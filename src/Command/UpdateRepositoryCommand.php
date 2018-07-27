<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\Url;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Symfony\Component\DomCrawler\Crawler;

use App\Service\ObjectClassifier;

class UpdateRepositoryCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:repo:update')
             ->setDescription('Evaluate(s) an individual object or a list of objects provided in a plain-text file.')
             ->setHelp('This command evaluates an individual object or a list of objects.')

             ->addArgument('object', InputArgument::REQUIRED, 'This can either be an IP address, a domain name or a plain-text file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            '===========================',
            'Automated object evaluation',
            '===========================',
            ''
        ]);

        $objectClassifier = new ObjectClassifier();
        $class = $objectClassifier->getClass($input->getArgument('object'), '4_public');

        $output->writeln('Type: ' . ($class ? $class : 'N/A'));

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
}