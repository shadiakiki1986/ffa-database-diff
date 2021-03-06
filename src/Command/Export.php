<?php

namespace PdoGit\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Export extends MyCommand {

    protected function configure()
    {
      $this
          // the name of the command (the part after "bin/console")
          ->setName('export')

          // the short description shown while running "php bin/console list"
          ->setDescription('Export a sql server table to git')

          // the full command description shown when running the command with
          // the "--help" option
          ->setHelp("Export a sql server table to git")
        ;

      parent::configure();

      $this->addArgument(
        'ID',
        InputArgument::REQUIRED,
        'ID column in table'
      );
      $this->addOption(
        'init',
        '',
        InputOption::VALUE_NONE,
        'flag to use ONLY for initial export (to skip check diff on initial import)'
      );

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $repo = $this->factory->repo();

      foreach($this->factory->pdo([$input->getArgument('dsn')]) as $dsn=>$pdo) {
        $pg = new \PdoGit\PdoGit($pdo,$repo);
        $pg->export(
          $dsn,
          $input->getArgument('table'),
          $input->getArgument('ID'),
          $input->getOption('init')
        );
      }
    }

}
