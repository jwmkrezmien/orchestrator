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

use App\Entity\Webobject;

class AddCommand extends Command
{
    // set object variables
    private $tableSection = array();

    private $updater;

    private $types = array(
        'valid',
        'duplicate',
        'invalid'
    );

    public function __construct(Updater $updater)
    {
        $this->updater = $updater;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('orchestrator:repository:add')
            ->setDescription('Add a new individual object to the repository or ingest objects from a list provided in a plain-text file')
            ->setHelp('Only objects with a new and valid full host reference are added to the repository. The validity of the objects can be assessed with the orchestrator:evaluate command.')

            ->addArgument(
                'object',
                InputArgument::REQUIRED,
                'This can either be an IP address, an URL, a full host name (either entered directly or ingested through a plain-text file'
            )

            ->addOption(
                'file',
                'f',
                InputOption::VALUE_NONE,
                'Provide a valid file name as the command\'s input'
            )

            ->addOption(
                'no-flush',
                null,
                InputOption::VALUE_NONE,
                'Prevents that new, valid objects are added to the database'
            )
        ;

        // prepare the arrays within the table section array for both successfully objects and unsuccessful attempts
        $this->tableSection['valid']  = array(); // valid objects
        $this->tableSection['invalid'] = array(); // already in database or invalid object
        $this->tableSection['duplicate'] = array(); // already in database or invalid object
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // check whether the file flag was set
        if($input->getOption('file'))
        {
            // parse the provided object into an array and skip any empty lines
            $content = array_values(array_filter(file($input->getArgument('object'), FILE_SKIP_EMPTY_LINES), "trim"));

            // if there is any content in the file
            if($content)
            {
                // then run through each line and
                foreach($content as $object)
                {
                    // evaluate whether the object will be added to the repository
                    $this->evalObject($output, trim($object));

                }
            }

            // if the file flag was not set then evaluate the provided input directly
        }else{

            // evaluate whether the object will be added to the repository
            $this->evalObject($output, trim($input->getArgument('object')));
        }

        // draw the actual table to stdout
        if(($output->isVerbose() && count($this->tableSection['valid']) > 0) || $output->isVeryVerbose()) $this->drawTable($output);

        if (count($this->tableSection['valid']) > 0 && !$input->getOption('no-flush')) $this->updater->flush();

        (!$input->getOption('no-flush') && count($this->tableSection['valid']) > 0) ?
            $output->writeln('Operation successful: <fg=green>' . count($this->tableSection['valid']) . '</> object'. (count($this->tableSection['valid']) !== 1  ? 's' : '') .' are new to the repository <fg=green>(successfully added)</>.') :
            $output->writeln('Operation successful: ' . count($this->tableSection['valid']) . ' objects are new to the repository.');
    }

    private function evalObject(OutputInterface $output, string $object)
    {
        // obtain a Webobject Converter class to obtain additional information (e.g. subdomain, hostname, suffix etc.)
        $converter = new Converter($object);

        // get a webobject from the converter
        $webobject = $converter->getWebobject();

        // get the converted Webobject from the Converter object and push it in to the Webobjects array
        $converter->getClass() !== 'undefined' ?
            $type = ($this->updater->addWebobject($webobject) ? 'valid' : 'duplicate') :
            $type = 'invalid';

        $this->prepareRow($output, $converter, $webobject, $type);

        return $type;
    }

    private function prepareRow(OutputInterface $output, Converter $converter, Webobject $webobject, string $type)
    {
        $columns = array();

        switch(true)
        {
            case $output->isVeryVerbose():

                $columns = array(

                    sprintf('<fg=%s>%s</>' , 'red', $converter->getInitialValue()),
                    $webobject->getFullHost() ? $webobject->getFullHost() : '-',

                );

                break;

            case $output->isVerbose():

                $columns = array(

                    $converter->getInitialValue(),

                );

                break;
        }

        // add the object to the array assessed objects
        array_push($this->tableSection[$type], $columns);
    }

    private function drawTable(OutputInterface $output)
    {
        $output->writeln('');

        // obtain a section to draw the table in
        $section = $output->section();

        // get a Table instance
        $table = new Table($section);

        switch (true)
        {
            case $output->isVeryVerbose():

                $columns = array(new TableCell(
                    strtoupper('REPOSITORY UPDATE'),
                    array('colspan' => 2)
                ));

                break;

            case $output->isVerbose():

                $columns = array(new TableCell(
                    strtoupper('REPOSITORY UPDATE')
                ));

                break;
        }

        // set the header of the table (and span the cell over 6 cells, in case verbosity is set)
        $table->setHeaders(array($columns));

        $i = 0;

        // cycle through the types
        foreach ($this->types as $type)
        {
            if ($output->isVeryVerbose() || ($output->isVerbose() && $type === 'valid'))
            {
                // for each type, count the number of objects classified as such
                if (count($this->tableSection[$type]) > 0)
                {
                    // if part of the table has already been drawn, and this is a subsequent section, draw a table seperator
                    if ($i > 0) $table->addRows(array(new TableSeparator()));

                    switch(true)
                    {
                        case $output->isVeryVerbose():

                            $columns = array(
                                array(
                                    'TYPE: ' . strtoupper($type) . ' (' . count($this->tableSection[$type]) . ' OBJECT' . (count($this->tableSection[$type]) > 1 ? 'S' : '') . ')',
                                    'FULL HOST',
                                )
                            );

                            break;

                        case $output->isVerbose():

                            $columns = array(
                                array(
                                    'TYPE: ' . strtoupper($type) . ' (' . count($this->tableSection[$type]) . ' OBJECT' . (count($this->tableSection[$type]) > 1 ? 'S' : '') . ')'
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
        }

        // render the table to stdout
        $table->render();

        $output->writeln('');
    }
}