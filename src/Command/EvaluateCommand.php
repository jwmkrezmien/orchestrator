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

use App\Service\WebobjectClassifier;
use App\Service\WebobjectConverter;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

class EvaluateCommand extends Command
{
    private $objects = array();

    private $types = array(
        'url',
        'domain',
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');

        // set empty arrays for url, ip and domain objects
        $this->objects['url']  = array();
        $this->objects['ip'] = array();
        $this->objects['domain'] = array();
        $this->objects['undefined'] = array();

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
                    // trim any spacing from the input and prepare a new row that depicts the results
                    $this->prepareRow($output, trim($object));

                }

                // draw the actual table to stdout
                $this->drawTable($output);

            }

        // if the file flag was not set then evaluate the provided input directly
        }else{

            // trim any spacing from the input
            $this->prepareRow($output, trim($input->getArgument('object')));

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

        // $output->writeln('File exists: '. (file_exists($input->getArgument('object')) ? 'Yes' : 'No'));

        // $output->writeln('Evaluating: '. $input->getArgument('object'));
    }

    private function prepareRow(OutputInterface $output, string $object)
    {
        // evaluate the object and see to what type of object it is
        $class = $this->getClass($object);

        // obtain a Webobject Converter class to obtain additional information (e.g. subdomain, hostname, suffix etc.)
        $converter = new WebobjectConverter($object);

        // add the object to the array of that specific object class
        // the actual output depends on whether the verbosity parameter has been set: verbose
        array_push($this->objects[$class], $output->isVerbose() ? array(

            $object,
            $converter->getSubdomain() ? $converter->getSubdomain() : '-',
            $converter->getHostname() ? $converter->getHostname() : '-',
            $converter->getSuffix() ? $converter->getSuffix() : '-',
            $converter->getRegistrableDomain() ? $converter->getRegistrableDomain() : '-',
            $converter->getIp() ? $converter->getIp() : '-'

            // the actual output depends on whether the verbosity parameter has been set: non-verbose
        ) : array(

            $object

        ));
    }

    private function drawTable(OutputInterface $output)
    {
        $section = $output->section();

        $table = new Table($section);
        $table->setHeaders(array(

            $output->isVerbose() ?

            array(new TableCell(
                strtoupper('automated object evaluation - results'),
                array('colspan' => 6)
            )) :

            array(
                strtoupper('automated object evaluation - results')
            )
        ));

        $i = 0;

        foreach ($this->types as $type)
        {
            if (count($this->objects[$type]) > 0)
            {
                if ($i > 0) $table->addRows(array(new TableSeparator()));

                $table->addRows($output->isVerbose() ? array(
                    array(
                        'TYPE: ' . strtoupper($type) . ' (' . count($this->objects[$type]) . ' OBJECT' . (count($this->objects[$type]) > 1 ? 'S' : '') . ')',
                        'SUBDOMAIN',
                        'HOST',
                        'SUFFIX',
                        'REGISTRABLE DOMAIN',
                        'IP'
                    )
                ) : array(
                    array(
                        'TYPE: ' . strtoupper($type) . ' (' . count($this->objects[$type]) . ' OBJECT' . (count($this->objects[$type]) > 1 ? 'S' : '') . ')',
                    )
                ));
                $table->addRows(array(new TableSeparator()));
                $table->addRows($this->objects[$type]);

                $i++;
            }
        }

        $table->render();
    }


    private function getClass(string $object)
    {
        $objectClassifier = new WebobjectClassifier();
        return $objectClassifier->getClass($object);
    }
}