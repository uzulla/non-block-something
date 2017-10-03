<?php
class Db {
    public $pdo;

    public function __construct($config)
    {
        $pdo = new PDO("sqlite:{$config->sqlite_path}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $pdo->exec("
CREATE TABLE IF NOT EXISTS
items(
  id INTEGER PRIMARY KEY,
  ch_id INTEGER,
  title TEXT,
  start_at INTEGER
)
        ");
        $this->pdo = $pdo;
    }

    public function isExistsId(int $id):bool{
        $sth = $this->pdo->prepare("SELECT count(*) FROM items WHERE id = :id");
        $sth->bindValue('id',$id,PDO::PARAM_INT);
        $sth->execute();
        $count = (integer)$sth->fetchColumn();
        return $count===1;
    }

    public function getLastItems():array{
        $sth = $this->pdo->prepare("SELECT * FROM items ORDER BY start_at DESC LIMIT 10");
        $sth->execute();
        return $sth->fetchAll();
    }

    public function insert(int $id, int $ch_id, string $title, int $start_at):bool{
        $sth = $this->pdo->prepare("INSERT INTO items(id, ch_id, title, start_at) VALUES (:id, :ch_id, :title, :start_at)");
        $sth->bindValue('id',$id,PDO::PARAM_INT);
        $sth->bindValue('ch_id',$ch_id,PDO::PARAM_INT);
        $sth->bindValue('title',$title,PDO::PARAM_STR);
        $sth->bindValue('start_at',$start_at,PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount()===1;
    }

}