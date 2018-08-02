<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\Url;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Symfony\Component\DomCrawler\Crawler;

use App\Service\WebobjectClassifier;
use App\Service\WebobjectConverter;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

class UpdateRepositoryCommand extends Command
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
        $this->setName('orchestrator:update:repo')
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
            //$content = file($input->getArgument('object'), FILE_SKIP_EMPTY_LINES);
            $content = array_values(array_filter(file($input->getArgument('object'), FILE_SKIP_EMPTY_LINES), "trim"));

            if($content)
            {
                foreach($content as $object)
                {
                    $object = trim($object);

                    $class = $this->getClass($object);

                    array_push($this->objects[$class], array($object));
                }

                $this->drawTable($output);
            }

        }else{

            $object = trim($input->getArgument('object'));

            $class = $this->getClass($object);

            array_push($this->objects[$class], array($object));

            $converter = new WebobjectConverter($object);
// Test >>

            $output->writeln('Subdomain: '. $converter->getSubdomain());
            $output->writeln('Hostname: '. $converter->getHostname());
            $output->writeln('Suffix: '. $converter->getSuffix());
            $output->writeln('Registrable Domain: '. $converter->getRegistrableDomain());
            $output->writeln('Full Host: '. $converter->getFullHost());
            $output->writeln('IP address: '. var_dump($converter->getIp()));

// Test <<

            $this->drawTable($output);
        }

        $output->writeln('');
    }

    private function drawTable(OutputInterface $output)
    {
        $section = $output->section();

        $table = new Table($section);
        $table->setHeaders(array(strtoupper('automated object evaluation - results')));

        $i = 0;

        foreach ($this->types as $type)
        {
            if (count($this->objects[$type]) > 0)
            {
                if ($i > 0) $table->addRows(array(new TableSeparator()));

                $table->addRows(array(
                    array('TYPE: ' . strtoupper($type) . ' (' . count($this->objects[$type]) . ' OBJECT' . (count($this->objects[$type]) > 1 ? 'S' : '') . ')')
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