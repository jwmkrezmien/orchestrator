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
        true,
        false
    );

    public function __construct(Updater $updater)
    {
        $this->updater = $updater;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('orchestrator:repository:add')
            ->setDescription('Add a new individual object directly (or from a list of objects provided in a plain-text) file to the repository.')
            ->setHelp('Only objects with a new and valid full host reference are added to the repository. The validity of the objects can be assessed with the orchestrator:evaluate command.')

            ->addArgument(
                'object',
                InputArgument::REQUIRED,
                'This can either be an IP address, an URL or a full host name provided directly or in a plain-text file.'
            )

            ->addOption(
                'file',
                'f',
                InputOption::VALUE_NONE,
                'Provide a valid file name as the command\'s input.'
            )
        ;

        // prepare the arrays within the table section array for both successfully objects and unsuccessful attempts
        $this->tableSection[true]  = array(); // valid objects
        $this->tableSection[false] = array(); // already in database or invalid object
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
                    // obtain a Webobject Converter class to obtain additional information (e.g. subdomain, hostname, suffix etc.)
                    $converter = new Converter(trim($object));

                    // get a webobject from the converter
                    $webobject = $converter->getWebobject();

                    // get the converted Webobject from the Converter object and push it in to the Webobjects array
                    $converter->getClass() !== 'undefined' ? $success = $this->updater->addWebobject($webobject) : $success = false;

                    if ($output->isVerbose()) $this->prepareRow($output, $converter, $webobject, $success);
                }

                // draw the actual table to stdout
                $this->drawTable($output);

            }

            // if the file flag was not set then evaluate the provided input directly
        }else{

            // obtain a Webobject Converter class to obtain additional information (e.g. subdomain, hostname, suffix etc.)
            $converter = new Converter(trim($input->getArgument('object')));

            // get a webobject from the converter
            $webobject = $converter->getWebobject();

            // get the converted Webobject from the Converter object and push it in to the Webobjects array
            $converter->getClass() !== 'undefined' ? $success = $this->updater->addWebobject($webobject) : $success = false;

            if ($output->isVerbose()) $this->prepareRow($output, $converter, $webobject, $success);

            // draw the actual table to stdout
            $this->drawTable($output);
        }

        $this->updater->flush();
    }

    private function prepareRow(OutputInterface $output, Converter $converter, Webobject $webobject, bool $success)
    {
        switch(true)
        {
            case $output->isVerbose():

                $columns = array(

                    $converter->getInitialValue(),
                    $webobject->getFullHost() ? $webobject->getFullHost() : '-',
                    $success === true ? 'Yes' : ($converter->getClass() !== 'undefined' ?  'No, already in repository' : 'No, invalid input')

                );

                break;
        }

        // add the object to the array assessed objects
        array_push($this->tableSection[$success], $columns);
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
            case $output->isVerbose():

                $columns = array(new TableCell(
                    strtoupper('REPOSITORY UPDATE'),
                    array('colspan' => 3)
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
                    case $output->isVerbose():

                        $columns = array(
                            array(
                                'OBJECTS',
                                'FULL HOST',
                                'ADDED?'
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

        $output->writeln('');
    }
}