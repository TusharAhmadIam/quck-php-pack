<?php

/*Pagination class by Tushar Ahmed 

* How to use example

    <?php

		$connection = $db->con;

		$query = 'select * from posts where cat_id = :cat_id';
	
        function param_bind_function($statement){
            global $cat_id;
            $statement->bindParam(':id', $cat_id ,PDO::PARAM_INT);
        }

        $pg = new Pagination($connection,$query,'param_bind_function');
		
        if($pg->totalResults > 0){

            $rows = $pg->fetch_results(); 
            
            foreach($rows as $row){
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
    public $link;
    public $cusQueryString = null;
    public $prevPageIcon = '<';
    public $nextPageIcon = '>';
    public $jump = true;
    public $prevJumpIcon = '&laquo';
    public $nextJumpIcon = '&raquo';
    public bool $dots = true; 
    public $dotsIcon = '...'; 

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
            $this->page = 1;
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

    public function linkButtons($increment, $disabled = false, $dots = true){
        $dots = $this->dots;        
       
            //prev
            if($increment == 'prev' && $disabled == false && $dots == true){
                $this->link .= '
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - 1).$this->cusQueryString.'">'.$this->prevPageIcon.'</a></li> 
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.(1).$this->cusQueryString.'">1</a></li>             
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.(1).$this->cusQueryString.'">'.$this->dotsIcon.'</a></li>
                ';
            }
            elseif($increment == 'prev' && $disabled == true && $dots == true){
                $this->link .= '
                <li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->prevPageIcon.'</a></li> 
                <li class="page-item disabled"><a class="page-link" href="#" disabled>1</a></li>             
                <li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->dotsIcon.'</a></li>             
                ';
            }
            elseif($increment == 'prev' && $disabled == false && $dots == false){
                $this->link .= '
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - 1).$this->cusQueryString.'">'.$this->prevPageIcon.'</a></li>             
                ';
            }
       
        //next
        elseif($increment == 'next' && $disabled == false && $dots == true){
                $this->link .= '
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->totalPages).$this->cusQueryString.'">'.$this->dotsIcon.'</a></li> 
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->totalPages).$this->cusQueryString.'">'.$this->totalPages.'</a></li>            
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + 1).$this->cusQueryString.'">'.$this->nextPageIcon.'</a></li> 
                ';
            }
            elseif($increment == 'next' && $disabled == true && $dots == true){
                $this->link .= '
                <li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->dotsIcon.'</a></li> 
                <li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->totalPages.'</a></li>             
                <li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->nextPageIcon.'</a></li> 
                ';
            }
            elseif($increment == 'next' && $disabled == false && $dots == false){
                $this->link .= '
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + 1).$this->cusQueryString.'">'.$this->nextPageIcon.'</a></li>             
                ';
            } 
           
              
    }

    public function links(){
        $prev = 'prev';
        $next = 'next';

        $half = floor( $this->buttonNumbers/2);

        $this->link .= '<ul class="pagination">';

        if($this->page <= $this->buttonNumbers && $this->totalPages <= $this->buttonNumbers){
            if ($this->totalPages == 1) {
                $this->link = '';
            }else{
                $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->prevJumpIcon.'</a></li>';
                $this->linkButtons($prev, true);
                    for ($i=1; $i <= $this->totalPages ; $i++) { 
                        if($i == $this->page){
                            $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                        }else{
                            $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                        }
                    }
                $this->linkButtons($next);
            }
        }
        
        elseif($this->page <= $this->buttonNumbers && $this->totalPages >= $this->buttonNumbers && $this->totalPages > $this->buttonNumbers * 2 && $this->page <= $half){
            $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->prevJumpIcon.'</a></li>';
            $this->linkButtons($prev, true);
                for ($i= 1; $i <= $this->buttonNumbers; $i++) { 

                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }   
                }
                $this->linkButtons($next);
                $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->buttonNumbers + 1).$this->cusQueryString.'">'.$this->nextJumpIcon.'</a></li>';
        }
        elseif($this->page <= $this->buttonNumbers && $this->totalPages >= $this->buttonNumbers && $this->totalPages < $this->buttonNumbers * 2){
            $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->prevJumpIcon.'</a></li>';
            $this->linkButtons($prev, true);
                for ($i= 1; $i <= $this->buttonNumbers; $i++) { 

                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }   
                }
                $this->linkButtons($next);
                $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->buttonNumbers + 1).$this->cusQueryString.'">'.$this->nextJumpIcon.'</a></li>';
        } 
        //       
        elseif($this->page <= $this->buttonNumbers && $this->totalPages >= $this->buttonNumbers && $this->totalPages > $this->buttonNumbers * 2 && $this->page > $half){
            $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->prevJumpIcon.'</a></li>';
            $this->linkButtons($prev, true);
                for ($i= 1; $i <= $this->buttonNumbers; $i++) { 

                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }   
                }
                $this->linkButtons($next);
                $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + $half+ 1).$this->cusQueryString.'">'.$this->nextJumpIcon.'</a></li>';
        }
        elseif($this->page > $this->buttonNumbers && $this->totalPages > $this->buttonNumbers && $this->page < ($this->totalPages - $half)){
            
            $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.(($this->page - $half) - 1).$this->cusQueryString.'">'.$this->prevJumpIcon.'</a></li>';
            $this->linkButtons($prev);
                for ($i=($this->page - $half); $i <= ($this->page + $half); $i++) { 

                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }   
                }
                $this->linkButtons($next);
                $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.(($this->page + $half) + 1).$this->cusQueryString.'">'.$this->nextJumpIcon.'</a></li>';

        }elseif($this->page >= ($this->totalPages - $this->buttonNumbers) ){
            $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->totalPages - $this->buttonNumbers).$this->cusQueryString.'">'.$this->prevJumpIcon.'</a></li>';
            $this->linkButtons($prev);

                for ($i=($this->totalPages - $this->buttonNumbers)+1; $i <= $this->totalPages ; $i++) { 
                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->cusQueryString.'">'.$i.'</a></li>';
                    }
                }   
            $this->linkButtons($next, true);   
            $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->nextJumpIcon.'</a></li>';     
        }
        
        $this->link .= '</ul>';
        return $this->link;
        
    }

  
}

