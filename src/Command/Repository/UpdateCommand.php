<?php

namespace App\Command\Repository;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use App\Service\Webobject\Converter;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

use App\Service\Webobject\Updater;

use Symfony\Component\Console\Helper\ProgressBar;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Webobject;

use Nmap\Nmap;
use Nmap\Host;

class UpdateCommand extends Command
{
    // set object variables
    private $tableSection = array();

    // define the different types [stable IP addresses, updated IP addresses and host that are down]
    private $types = array(
        'stable',
        'updated',
        'down'
    );

    private $colors = array(
        'stable'  => 'default',
        'updated' => 'blue',
        'down'    => 'red'
    );

    // get EntityManagerInterface and Updater objects through service injection capability
    public function __construct(Updater $updater)
    {
        $this->updater = $updater;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('orchestrator:repository:update')
             ->setDescription('Updates all the IP addresses of the full hosts registered in the repository through an ICMP probe')
             ->setHelp('In order to perform the update, an outbound connection is required')

             ->addOption(
                'no-flush',
                null,
                InputOption::VALUE_NONE,
                'Prevents that new, valid objects are added to the database'
             );

        // prepare the arrays within the table section variable
        $this->tableSection['stable']  = array(); // objects that are stable
        $this->tableSection['updated'] = array(); // objects that are updated
        $this->tableSection['down']    = array(); // objects that are down
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // obtain console sections to print both the Table as well as the Progressbar
        $secProgress = $output->section();
        $secTable    = $output->section();

        // retrieve all webobjects (whose IP address was either not retrieved yet or objects that do not have an IP address configured as a full host)
        $webobjects = $this->updater->getAllWebobjects();

        // creates a new progressbar and set it's format
        $progress = new ProgressBar($secProgress, count($webobjects));
        $progress->setFormat('<fg=blue>[%bar%]</> - Evaluating webobjects: %current%/%max% - Current: <fg=blue>%message%</>');

        // starts and displays the progress bar at $i = 0
        $progress->start(); $i = 0;

        // obtain a new Nmap object to renew the webobject's IP addresses
        $nmap = new Nmap();

        // while evaluating all obtained webobjects ..
        while ($i++ < count($webobjects))
        {
            // get the current webobject and let the progressbar depict the full host name of the object
            $progress->setMessage($webobjects[$progress->getProgress()]->getFullHost());

            // obtain the hosts IP address
            $hosts = $nmap->disablePortScan()
                          ->disableReverseDNS()
                          ->treatHostsAsOnline()
                          ->scan([ $webobjects[$progress->getProgress()]->getFullHost() ]);

            // if the host file is an empty array then this indicates that the host could not be reached:
            // either the pinged host is down or the machine that runs this script does not have an outbound connection
            if (!empty($hosts))
            {
                // obtain the host
                $host = $hosts[0];

                // and set the IP address
                $webobjects[$progress->getProgress()]->setIp($host->getAddress());
                $this->updater->updateWebobject($webobjects[$progress->getProgress()]);

            }else{

                // if the hosts array is empty, set the host variable as null
                $host = null;
            }

            // prepare a table output row
            $this->prepareRow($output, $webobjects[$progress->getProgress()], $host);

            // advance the progressbar
            $progress->advance();
        }

        // clear the progressbar console output section and draw the table
        $secProgress->clear();
        $this->drawTable($secTable);

        if(count($this->tableSection['updated']) > 0  && !$input->getOption('no-flush')) $this->updater->flush();

        $output->writeln(sprintf('Operation successful: <fg=%s>%s</> %s updated IP addresses %s',
            count($this->tableSection['updated']) > 0 ? 'green' : 'default',
                count($this->tableSection['updated']),
                count($this->tableSection['updated']) <> 1 ? 'objects have' : 'object has',
                !$input->getOption('no-flush') ? '<fg=green>(successfully added)</>' : ''
            ));
    }

    private function prepareRow(OutputInterface $output, Webobject $webobject, Host $host = null)
    {
        $columns = array();

        // if the host parameter was set, this means that the ICMP request was successful
        if ($host)
        {
            // if an old IP address is set and it's different from the newly retrieved IP address, classify it as an updated webobject
            ((is_null($webobject->getOldIp()) || $webobject->getIp() != $webobject->getOldIp())) ? $type = 'updated' : $type = 'stable';

        // if not, indicate that the state is down
        }else{

            $type = 'down';
        }

        switch(true)
        {
            case $output->isVerbose():

                $columns = array(

                    $webobject->getFullHost(),
                    $host ? $webobject->getIp() : '-'
                );

                break;
        }

        // add the object to the array assessed objects
        array_push($this->tableSection[$type], $columns);
    }

    private function drawTable(OutputInterface $section)
    {
        $section->writeln('');

        // get a Table instance
        $table = new Table($section);

        switch (true)
        {
            case $section->isVerbose():

                $columns = array(new TableCell(
                    sprintf('%s%s%s','<fg=green;options=bold>', strtoupper('IP ADDRESS RENEWAL'), '</>'),
                    array('colspan' => 2)
                ));

                break;
        }

        // set the header of the table (and span the cell over 6 cells, in case verbosity is set)
        $table->setHeaders(array($columns));

        $i = 0;

        // cycle through the types
        foreach ($this->types as $type)
        {
            // for each type, count the number of objects classified as such
            if (count($this->tableSection[$type]) > 0)
            {
                // if part of the table has already been drawn, and this is a subsequent section, draw a table seperator
                if ($i > 0) $table->addRows(array(new TableSeparator()));

                switch(true)
                {
                    case $section->isVeryVerbose():
                        
                        $columns = array(
                            array(
                                sprintf('HOST: <fg=%s>%s (%s %s)</>', $this->colors[$type], strtoupper($type), count($this->tableSection[$type]), count($this->tableSection[$type]) <> 1 ? 'OBJECTS' : 'OBJECT'),
                                'IP'
                            )
                        );

                        break;

                    case $section->isVerbose():

                        $columns = array(
                            array(
                                sprintf('HOST: <fg=%s>%s (%s %s)</>', $this->colors[$type], strtoupper($type), count($this->tableSection[$type]), count($this->tableSection[$type]) <> 1 ? 'OBJECTS' : 'OBJECT')
                            )
                        );

                        break;
                }

                // define the row of subheaders (depending on verbosity, this encompasses 6 columns)
                $table->addRows($columns);

                // add a table seperator and the objects
                $table->addRows(array(new TableSeparator()));
                $table->addRows($this->tableSection[$type]);

                $i++;
            }
        }

        // render the table to stdout
        $table->render();

        $section->writeln('');
    }
}