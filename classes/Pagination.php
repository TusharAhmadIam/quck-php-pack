<?php

/*
* Pagination class by Tushar Ahmed 
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
    public $prevPageIcon = '<';
    public $nextPageIcon = '>';
    public $jump = true;
    public $prevJumpIcon = '&laquo';
    public $nextJumpIcon = '&raquo';
    public bool $dots = true; 
    public $dotsIcon = '...'; 
    public $customQueryString;
    public $pdo = false;

    public function __construct(public $connection,public $query,public $value_bind_function = null){            
        // $this->count_results();        
    }

    public function count_results(){
        if($this->pdo == true){
            try{

                $statement = $this->connection->prepare("SELECT COUNT(*) FROM (". $this->query .") count");
        
                if(!empty($this->value_bind_function)){
                    call_user_func($this->value_bind_function,$statement);
                }
                $statement->execute();        
                $this->totalResults = htmlspecialchars($statement->fetch(PDO::FETCH_ASSOC)['COUNT(*)'], ENT_QUOTES, 'UTF-8');                        
                
            }catch(PDOException $e){
            
            }
              
        }else{  
            $statement = $this->connection->prepare("SELECT COUNT(*) FROM (". $this->query .") count");
        
            if(!empty($this->value_bind_function)){
                call_user_func($this->value_bind_function,$statement);
            }

            $statement->execute(); 
            $countResult = $statement->get_result();       
            $this->totalResults = htmlspecialchars($countResult->fetch_assoc()['COUNT(*)'], ENT_QUOTES, 'UTF-8');  
           
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
        
        if($this->pdo == true){
            try{
                $this->query .= ' LIMIT :start, :limit';
                $this->result = $this->connection->prepare($this->query);   
                $this->result->bindParam(':start', $this->start,PDO::PARAM_INT);
                $this->result->bindParam(':limit', $this->itemsPerPage,PDO::PARAM_INT);
            
        
                if(!empty($this->value_bind_function)){
                    call_user_func($this->value_bind_function,$this->result);
                }
                $this->result->execute();
                        
                return $this->result->fetchAll(PDO::FETCH_ASSOC);
                
                $this->result->close();
            }catch(PDOException $e){
            
            }           

        }else{
            $this->query .= ' LIMIT ?, ?'; 
            $this->result = $this->connection->prepare($this->query);   
            $this->result->bind_Param('ii', $this->start,$this->itemsPerPage);       
    
            if(!empty($this->value_bind_function)){
                call_user_func($this->value_bind_function, $this->result);
            }
            $this->result->execute();

            $fetched_result =$this->result->get_result();
            return $fetched_result->fetch_all(MYSQLI_ASSOC);                 
        }

    }

    public function linkButtons($increment, $disabled = false, $dots = true){
        $dots = $this->dots;        
       
            //prev
            if($increment == 'prev' && $disabled == false && $dots == true){
                $this->link .= '
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - 1).$this->customQueryString.'">'.$this->prevPageIcon.'</a></li> 
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.(1).$this->customQueryString.'">1</a></li>             
                <li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->dotsIcon.'</a></li> 
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
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - 1).$this->customQueryString.'">'.$this->prevPageIcon.'</a></li>             
                ';
            }
       
        //next
        elseif($increment == 'next' && $disabled == false && $dots == true){
                $this->link .= '
                <li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->dotsIcon.'</a></li> 
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->totalPages).$this->customQueryString.'">'.$this->totalPages.'</a></li>            
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + 1).$this->customQueryString.'">'.$this->nextPageIcon.'</a></li> 
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
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + 1).$this->customQueryString.'">'.$this->nextPageIcon.'</a></li>             
                ';
            } 
           
              
    }

    public function links(){
        $prev = 'prev';
        $next = 'next';

        $half = floor( $this->buttonNumbers/2);

        $this->link .= '<ul class="pagination">';

        if($this->page <= $this->buttonNumbers && $this->totalPages <= $this->buttonNumbers){
            if ($this->totalPages == 1 or $this->totalPages == 0) {
                $this->link = '';
            }else{
                $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->prevJumpIcon.'</a></li>';
                if($this->page == 1){
                    $this->linkButtons($prev, true);
                }else{

                    $this->linkButtons($prev, false);
                }
                    for ($i=1; $i <= $this->totalPages ; $i++) { 
                        if($i == $this->page){
                            $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                        }else{
                            $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                        }
                    }
                    if($this->page == $this->totalPages){
                        $this->linkButtons($next, true);
                    }else{
    
                        $this->linkButtons($next, false);
                    } 
                $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->nextJumpIcon.'</a></li>'; 
            }
        }
        
        elseif($this->page <= $this->buttonNumbers && $this->totalPages >= $this->buttonNumbers && $this->totalPages >= $this->buttonNumbers * 2 && $this->page <= $half){
            $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->prevJumpIcon.'</a></li>';
            if($this->page == 1){
                $this->linkButtons($prev, true);
            }else{

                $this->linkButtons($prev, false);
            }
                for ($i= 1; $i <= $this->buttonNumbers; $i++) { 

                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }   
                }
                $this->linkButtons($next);
                $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + $this->buttonNumbers).$this->customQueryString.'">'.$this->nextJumpIcon.'</a></li>';
        }
        elseif($this->page <= $this->buttonNumbers && $this->totalPages >= $this->buttonNumbers && $this->totalPages <= $this->buttonNumbers * 2){
            $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->prevJumpIcon.'</a></li>';
            if($this->page == 1){
                $this->linkButtons($prev, true);
            }else{

                $this->linkButtons($prev, false);
            }
                for ($i= 1; $i <= $this->buttonNumbers; $i++) { 

                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }   
                }
                $this->linkButtons($next);
                $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + $this->buttonNumbers).$this->customQueryString.'">'.$this->nextJumpIcon.'</a></li>';
        } 
        //       
        elseif($this->page <= $this->buttonNumbers && $this->totalPages >= $this->buttonNumbers && $this->totalPages > $this->buttonNumbers * 2 && $this->page > $half){
            $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->prevJumpIcon.'</a></li>';
            if($this->page == 1){
                $this->linkButtons($prev, true);
            }else{

                $this->linkButtons($prev, false);
            }
                for ($i= 1; $i <= $this->buttonNumbers; $i++) { 

                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }   
                }
                $this->linkButtons($next);
                $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + $this->buttonNumbers).$this->customQueryString.'">'.$this->nextJumpIcon.'</a></li>';
        }
        elseif($this->page > $this->buttonNumbers && $this->totalPages > $this->buttonNumbers && $this->page < ($this->totalPages - $half)){
            
            $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - $this->buttonNumbers).$this->customQueryString.'">'.$this->prevJumpIcon.'</a></li>';
            $this->linkButtons($prev);
                for ($i=($this->page - $half); $i <= ($this->page + $half); $i++) { 

                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }   
                }
                $this->linkButtons($next);
                $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + $this->buttonNumbers).$this->customQueryString.'">'.$this->nextJumpIcon.'</a></li>';

        }elseif($this->page >= ($this->totalPages - $this->buttonNumbers) ){
            $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - $this->buttonNumbers).$this->customQueryString.'">'.$this->prevJumpIcon.'</a></li>';
            $this->linkButtons($prev);

                for ($i=($this->totalPages - $this->buttonNumbers)+1; $i <= $this->totalPages ; $i++) { 
                    if($i == $this->page){
                        $this->link .= '<li class="page-item active"><a class="page-link " href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }else{
                        $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }
                }   
                if($this->page == $this->totalPages){
                    $this->linkButtons($next, true);
                }else{

                    $this->linkButtons($next, false);
                }  
            $this->link .= '<li class="page-item disabled"><a class="page-link" href="#" disabled>'.$this->nextJumpIcon.'</a></li>';     
        }
        
        $this->link .= '</ul>';
        return $this->link;        
    }  
}

