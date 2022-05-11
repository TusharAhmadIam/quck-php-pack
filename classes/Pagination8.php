<?php

/*Pagination class by Tushar Ahmed 

* How to use example

    <?php

		$connection = $db->con;

		$query = 'select * from posts where cat_id = :cat_id';
	
        $bind_function = function param_bind_function($result){
            global $cat_id;
            $result->bindParam(':id', $cat_id ,PDO::PARAM_INT);
        }

        $pg = new Pagination($connection,$query,$bind_function);
		
        if($pg->totalResults > 0){

            $rows = $pg->fetch_results(); 
            
            foreach($rows as $key => $row){
            ----- $row['title']-----
            ----- $row['body']------
            }
        }	
		echo $pg->links();															
																				
	?>

*/

class Pagination{

    public int $totalResults;
    public int $buttonNumbers = 5;
    public int $itemsPerPage = 10;
    public int $start;
    public int $totalPages;
    public int $page;
    public $result;
    public $cusQueryString = null;
    public $prevLinkText = '&laquo';
    public $nextLinkText = '&raquo';

    public function __construct(public $connection,public $query,public $value_bind_function = null){    
           
        $this->count_results();
    }

    public function count_results(){
        try{

            $statement = $this->connection->prepare("SELECT COUNT(*) FROM (". $this->query .") count");
     
            if(!empty($this->value_bind_function)){
                call_user_func($this->value_bind_function,$statement);
            }
            $statement->execute();        
            $this->totalResults = htmlspecialchars($statement->fetch(PDO::FETCH_ASSOC)['COUNT(*)'], ENT_QUOTES, 'UTF-8');                        
            
        }catch(PDOException $e){
           
        }

        return $this->totalResults;
    }

    public function fetch_results(){ 
        
       $this->count_results();

        $this->totalPages = ceil($this->totalResults/$this->itemsPerPage);
        if(isset($_GET['page']) && $_GET['page'] >= 1 && $_GET['page'] <= $this->totalPages){
           $page = htmlspecialchars($_GET['page'], ENT_QUOTES, 'UTF-8');
           $this->page = $page;        
        }elseif(isset($_GET['page']) && $_GET['page'] < 1) {
            $this->page = 1;
        }elseif(isset($_GET['page']) && $_GET['page'] > $this->totalPages){
            $this->page = $this->totalPages;
        }else{
            $this->page =1;
        }
        $this->start = ($this->page-1) * $this->itemsPerPage;

        //fetching results 

        try{
            $this->query .= ' LIMIT :start, :limit'; 
        
            $this->result = $this->connection->prepare($this->query);   
            $this->result->bindParam(':start', $this->start,PDO::PARAM_INT);
            $this->result->bindParam(':limit', $this->itemsPerPage,PDO::PARAM_INT);
    
            if(!empty($this->value_bind_function)){
                call_user_func($this->value_bind_function,$this->result);
            }
            $this->result->execute();
            
        }catch(PDOException $e){
           
        }       

        return $this->result->fetchAll(PDO::FETCH_ASSOC);

        $this->result->close();

    }

    public function links(){

        $half = floor( $this->buttonNumbers/2);

        $links = '<ul class="pagination">';

        if($this->page <= $this->buttonNumbers && $this->totalPages <= $this->buttonNumbers){
            if ($this->totalPages == 1) {
                $links = '';
            }else{

                for ($i=1; $i <= $this->totalPages ; $i++) { 
                    if($i == $this->page){
                        $links .= '<li class="active page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }else{
                        $links .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }
                }
            }
        }elseif($this->page <= $this->buttonNumbers && $this->totalPages >= $this->buttonNumbers){
            for ($i= 1; $i <= $this->buttonNumbers; $i++) { 

                if($i == $this->page){
                    $links .= '<li class="active page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                }else{
                    $links .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                }   
            }
            $links .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->buttonNumbers + 1).$this->cusQueryString.'">'.$this->nextLinkText.'</a></li>';
        }
        elseif($this->page > $this->buttonNumbers && $this->totalPages > $this->buttonNumbers && $this->page < ($this->totalPages - $half)){

            $links .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.(($this->page - $half) - 1).$this->cusQueryString.'">'.$this->prevLinkText.'</a></li>';
            for ($i=($this->page - $half); $i <= ($this->page + $half); $i++) { 

                if($i == $this->page){
                    $links .= '<li class="active page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                }else{
                    $links .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                }   
            }
            $links .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.(($this->page + $half) + 1).$this->cusQueryString.'">'.$this->nextLinkText.'</a></li>';

        }elseif($this->page >= ($this->totalPages - $this->buttonNumbers) ){
            $links .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->totalPages - $this->buttonNumbers).$this->cusQueryString.'">'.$this->prevLinkText.'</a></li>';

            for ($i=($this->totalPages - $this->buttonNumbers)+1; $i <= $this->totalPages ; $i++) { 
                if($i == $this->page){
                    $links .= '<li class="active page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                }else{
                    $links .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                }
            }            
        }
        
        $links .= '</ul>';
        return $links;
        
    }
}

