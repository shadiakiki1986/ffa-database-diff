<?php

namespace PdoGit;

class Factory {

  public function repo(\GitRestApi\Client $git=null) {

    if(is_null($git)) {
      $grapiUri='http://localhost:8081';
      $git = new \GitRestApi\Client($grapiUri);
    }

    // prepare git access
    $GIT_NAME='MfDbVersioned';
    $repo = $git->get($GIT_NAME);
    if(!$repo) {
      // if first usage
      $repo = $git->init($GIT_NAME);
    }
    return $repo;
  }

  // dsn: array of strings of DSN names in /etc/odbc.ini
  public function pdo(array $dsn = null, string $iniFile = '/etc/odbc.ini', PdoWrap $pdoWrap=null) {

    if(is_null($pdoWrap)) $pdoWrap=new PdoWrap();

    // iterate over databases in odbc
    $iniContents = parse_ini_file($iniFile,true);

    if(!is_null($dsn)) {
      $iniContents = array_intersect_key($iniContents,array_flip($dsn));
    }

    # $dsn = 'MarketflowAcc';
    foreach($iniContents as $dsn=>$details) {
      if(!array_key_exists('UID',$details)) {
        throw new \Exception("Missing UID from $dsn in ".$iniFile);
      }

      $pdo = $pdoWrap->get($dsn,$details['UID'],$details['PWD']);

      #$dsn = "mysql:host=$hostname;dbname=$dbname";
      #return new \PDO($dsn, $username, $password);

      yield $dsn=>['pdo'=>$pdo,'odbc'=>$details];
    }
  }

  public function deepDiff() {
    $repo = $this->repo();
    $ge = new DeepDiffFactory($repo);

    // get history of commits
    $commits = $ge->commits();

    // get sha1 of commit to diff by
    $today = \DateTime::createFromFormat('!Y-m-d',date('Y-m-d'));
    $sha1 = $ge->parentOfFirstCommitToday($commits,$today);

    // get diff
    $differences = $ge->diff($sha1);

    return new DeepDiffObject($differences);
  }

}
