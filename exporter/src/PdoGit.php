<?php

namespace PdoGit;

class PdoGit {

  function __construct(\PDO $pdo, \GitRestApi\Repository $repo) {
    $this->pdo = $pdo;
    $this->repo = $repo;
  }

  public function export(string $dsn, string $table) {
    $stmt = $this->pdo->query("select top 7 TIT_COD,TIT_NOM from ".$table,\PDO::FETCH_ASSOC);
    if(!$stmt) {
      throw new \Exception("Exception from PDO query");
    }

    $result = $stmt->fetchAll();
    if(!$result) {
      throw new \Exception("No data returned from PDO query");
    }

    $this->repo->putConfig('user.email','shadiakiki1986@gmail.com');
    $this->repo->putConfig('user.name','Shadi Akiki');
    $fn = $dsn.'/'.$table.'.yml';
    $this->repo->putTree($fn,\yaml_emit($result));

    // before committing, check if data changed
    if($this->repo->diff($fn,null,null,true)=='') {
      throw new \Exception("Data not changed");
    }

    // commit
    $this->repo->postCommit('a new commit message');
  }

}
