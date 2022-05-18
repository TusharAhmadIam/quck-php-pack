<?php

    require_once './config/config.php';
    
    class DB{
        public $host    = DB_HOST;
        public $user    = DB_USER;
        public $pass    = DB_PASS;
        public $name    = DB_NAME;
        public $con;

        public function __construct(){
            $this->connection();
        }

        protected function connection(){
           
                // $this->con = new PDO("mysql:host=".$this->host."; dbname=".$this->name, $this->user, $this->pass);

                $this->con = new mysqli($this->host,$this->user,$this->pass,$this->name);
                if(!$this->con){
                    die('Connection failled');
                }            
        }        
    }
 
?>