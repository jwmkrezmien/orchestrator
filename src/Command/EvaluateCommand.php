<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Symfony\Component\DomCrawler\Crawler;

use App\Service\Webobject\Converter;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

use App\Entity\Webobject;

class EvaluateCommand extends Command
{
    private $tableSection = array();

    private $types = array(
        'url',
        'host',
        'ip',
        'undefined'
    );

    protected function configure()
    {
        $this->setName('orchestrator:evaluate')
             ->setDescription('Evaluate(s) an individual object or a list of objects provided in a plain-text file.')
             ->setHelp('This command evaluates an individual object or a list of objects.')

             ->addArgument(
                 'object',
                 InputArgument::REQUIRED,
                 'This can either be an IP address, a domain name or a plain-text file'
             )

             ->addOption(
                 'file',
                 'f',
                 InputOption::VALUE_NONE,
                 'Provide a valid file name as the command\'s input'
             )
        ;

        // set empty arrays for url, ip and domain objects
        $this->tableSection['url']  = array();
        $this->tableSection['ip'] = array();
        $this->tableSection['host'] = array();
        $this->tableSection['undefined'] = array();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');

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
                    // obtain a Webobject Converter class to obtain additional information (e.g. subdomain, hostname, suffix etc.)
                    $converter = new Converter(trim($object));

                    // get the converted Webobject from the Converter object
                    $webobject = $converter->getWebobject();

                    // trim any spacing from the input and prepare a new row that depicts the results
                    $this->prepareRow($output, $converter, $webobject);
                }

                // draw the actual table to stdout
                $this->drawTable($output);

            }

        // if the file flag was not set then evaluate the provided input directly
        }else{

            // obtain a Webobject Converter class to obtain additional information (e.g. subdomain, hostname, suffix etc.)
            $converter = new Converter(trim($input->getArgument('object')));

            // get the converted Webobject from the Converter object
            $webobject = $converter->getWebobject();

            // trim any spacing from the input
            $this->prepareRow($output, $converter, $webobject);

            // draw the actual table to stdout
            $this->drawTable($output);
        }

        $output->writeln('');

/*

        // check whether a particular package is installed
        $process = new Process('dpkg-query -W nmap');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();


*/
    }

    private function prepareRow(OutputInterface $output, Converter $converter, Webobject $webobject)
    {
        switch(true)
        {
            case $output->isVeryVerbose():

                $columns = array(

                    $converter->getInitialValue(),
                    $webobject->getSubdomain() ? $webobject->getSubdomain() : '-',
                    $webobject->getHostname() ? $webobject->getHostname() : '-',
                    $webobject->getSuffix() ? $webobject->getSuffix() : '-',
                    $webobject->getRegistrableDomain() ? $webobject->getRegistrableDomain() : '-',
                    $webobject->getIp() ? $webobject->getIp() : '-'

                );

                break;

            case $output->isVerbose():

                $columns = array(

                    $converter->getInitialValue(),
                    $webobject->getFullHost() ? $webobject->getFullHost() : '-'

                );

                break;

            default:

                $columns = array(

                    $converter->getInitialValue()

                );

        }

        // add the object to the array of that specific object class
        // the actual output depends on whether the verbosity parameter has been set
        array_push($this->tableSection[$webobject->getClass()], $columns);
    }

    private function drawTable(OutputInterface $output)
    {
        // obtain a section to draw the table in
        $section = $output->section();

        // get a Table instance
        $table = new Table($section);

        switch (true)
        {
            case $output->isVeryVerbose():

                $columns = array(new TableCell(
                    strtoupper('automated object evaluation - results'),
                    array('colspan' => 6)
                ));

                break;

            case $output->isVerbose():

                $columns = array(new TableCell(
                    strtoupper('automated object evaluation - results'),
                    array('colspan' => 2)
                ));

                break;

            default:

                $columns = array(
                    strtoupper('automated object evaluation - results')
                );
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
                    case $output->isVeryVerbose():

                        $columns = array(
                            array(
                                'TYPE: ' . strtoupper($type) . ' (' . count($this->tableSection[$type]) . ' OBJECT' . (count($this->tableSection[$type]) > 1 ? 'S' : '') . ')',
                                'SUBDOMAIN',
                                'HOST',
                                'SUFFIX',
                                'REGISTRABLE DOMAIN',
                                'IP'
                            )
                        );

                        break;

                    case $output->isVerbose():

                        $columns = array(
                            array(
                                'TYPE: ' . strtoupper($type) . ' (' . count($this->tableSection[$type]) . ' OBJECT' . (count($this->tableSection[$type]) > 1 ? 'S' : '') . ')',
                                'FULL HOST'
                            )
                        );

                        break;

                    default:

                        $columns = array(
                            array(
                                'TYPE: ' . strtoupper($type) . ' (' . count($this->tableSection[$type]) . ' OBJECT' . (count($this->tableSection[$type]) > 1 ? 'S' : '') . ')',
                            )
                        );
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
    }
}