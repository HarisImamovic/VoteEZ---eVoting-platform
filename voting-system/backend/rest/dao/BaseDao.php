<?php
require_once __DIR__ . "/../config.php";

class BaseDao{
    protected $connection;
    protected $table_name;

    public function __construct($table_name){
        $this->table_name=$table_name;
        try{
            //print_r("Successfully connected to the database!");
            $this->connection = new PDO("mysql:host=" . Config::DB_HOST() . ";dbname=" . Config::DB_NAME() . 
            ";charset=utf8;port=" . Config::DB_PORT(), Config::DB_USER(), Config::DB_PASSWORD(), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch(PDOException $e){
            throw $e;
        }
    }
    protected function query($query, $params){
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    protected function query_unique($query, $params){
        $results = $this->query($query, $params);
        return reset($results);
    }

    //Method for adding an entry to a database table
    public function add($entity){
        $query = "INSERT INTO " . $this->table_name . " (";
        foreach($entity as $column => $value){
            $query .= $column . ', ';
        }
        $query = substr($query, 0, -2);
        $query .= ") VALUES (";
        foreach($entity as $column => $value){
            $query .= ":" . $column . ', ';
        }
        $query = substr($query, 0, -2); //Remove the , and whitespace after the above loop finishes
        $query .= ")";
        $stmt = $this->connection->prepare($query);
        $stmt->execute($entity);
        $entity['id'] = $this->connection->lastInsertId();
        return $entity;
    }
    
    //Method for updating an entry from a database table
    public function update($entity, $id, $id_column = "id"){
        $query = "UPDATE " . $this->table_name . " SET ";
        foreach($entity as $column => $value){
            $query .= $column . "=:" . $column . ", ";
        }
        $query = substr($query, 0, -2);
        $query .= " WHERE " . $id_column . " = :id";
        $stmt = $this->connection->prepare($query);
        $entity['id'] = $id;
        $stmt->execute($entity);
        return $entity;
    }

    //Method for deleting an entry from a database table
    public function delete($id){
        $stmt = $this->connection->prepare("DELETE FROM " . $this->table_name . " WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    //Method for getting all entries (no filter criteria) from a database table
    public function get_all(){
        return $this->query("SELECT * FROM " . $this->table_name, []);
    }

    //Method for getting entity by id from a database table
    public function get_by_id($id){
        return $this->query_unique("SELECT * FROM " . $this->table_name . " WHERE id=:id", ['id' => $id]);
    }
}