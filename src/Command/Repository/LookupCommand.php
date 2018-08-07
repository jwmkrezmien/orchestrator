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

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

class LookupCommand extends Command
{
    protected function configure()
    {
        $this->setName('orchestrator:repository:lookup')
            ->setDescription('Add a new individual object to the repository or ingest objects from a list provided in a plain-text file')
            ->setHelp('Only objects with a new and valid full host reference are added to the repository. The validity of the objects can be assessed with the orchestrator:evaluate command.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $bundles = array('AcmeDemoBundle', 'AcmeBlogBundle', 'AcmeStoreBundle');
        $question = new Question('Please enter the name of a bundle: ', 'FooBundle');
        $question->setAutocompleterValues($bundles);

        $bundleName = $helper->ask($input, $output, $question);

        $output->writeln("Bundle $bundleName");

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select your favorite color (defaults to red)',
            array('red', 'blue', 'yellow'),
            0
        );
        $question->setErrorMessage('Color %s is invalid.');

        $color = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: '.$color);
    }

    private function evalObject(OutputInterface $output, string $object)
    {
        // obtain a Webobject Converter class to obtain additional information (e.g. subdomain, hostname, suffix etc.)
        $converter = new Converter($object);

        // get a webobject from the converter
        $webobject = $converter->getWebobject();

        // get the converted Webobject from the Converter object and push it in to the Webobjects array
        $converter->getClass() !== 'undefined' ? $type = $this->updater->addWebobject($webobject) : $type = 'invalid';

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

                    $converter->getInitialValue(),
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