<?php

namespace PdoGit\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class Export extends Command {

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

      $this->addOption(
          'dsn',
          'd',
          InputOption::VALUE_REQUIRED,
          'Name of dsn in /etc/odbc.ini to export'
      );

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $repo = $this->factory->repo();

      $subset = $input->getOption('dsn');
      $subset = explode(',',$subset);

      foreach($this->factory->pdo($subset) as $dsn=>$obj) {
        if(!array_key_exists('dbname',$obj['odbc'])) {
          throw new \Exception("Missing field dbname from ".$dsn);
        }

        $obj['pdo']->query("use ".$obj['odbc']['dbname'].";");
        $pg = new \PdoGit\PdoGit($obj['pdo'],$repo);
        $pg->export('TITRE',$dsn);
      }
    }

}
