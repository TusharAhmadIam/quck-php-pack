<?php
/*
* Pagination class by Tushar Ahmad 
*/
class Pagination{

    public int $totalResults;
    public int $buttonNumbers = 5;
    public int $itemsPerPage = 10;
    public int $start;
    public int $totalPages;
    public int $page;
    public $result;
    public string $link = '';    
    public string $prevPageIcon = '<';
    public string $nextPageIcon = '>';
    public bool $jump = true;
    public string $prevJumpIcon = '&laquo';
    public string $nextJumpIcon = '&raquo';
    public bool $dots = true; 
    public string $dotsIcon = '...'; 
    public string $customQueryString = '';
    public bool $pdo = false;

    //php 7 start
    public $connection;
    public $query;
    public $value_bind_function;

    public function __construct($connection,$query,$value_bind_function = null){
        $this->connection = $connection;
        $this->query = $query;
        $this->value_bind_function = $value_bind_function;
    }
    //php 7 end

    //php 8 start
    //public function __construct(Public $connection, public $query, public $value_bind_function = null){}
    //php 8 end

    //fetching results
    public function fetch_results(){
        //counting total results
        if($this->pdo == true){          

            $statement = $this->connection->prepare("SELECT COUNT(*) FROM (". $this->query .") count");
    
            if(!empty($this->value_bind_function)){
                call_user_func($this->value_bind_function,$statement);
            }

            $statement->execute();        
            $this->totalResults = htmlspecialchars($statement->fetch(PDO::FETCH_ASSOC)['COUNT(*)'], ENT_QUOTES, 'UTF-8');  

        }else{  
            $statement = $this->connection->prepare("SELECT COUNT(*) FROM (". $this->query .") count");
        
            if(!empty($this->value_bind_function)){
                call_user_func($this->value_bind_function,$statement);
            }

            $statement->execute(); 
            $countResult = $statement->get_result();       
            $this->totalResults = htmlspecialchars($countResult->fetch_assoc()['COUNT(*)'], ENT_QUOTES, 'UTF-8');  
        }

        if($this->totalResults > 0){
        
            //getting current page no
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
                
            }else{
                
                $this->query .= ' LIMIT '.$this->start.' , '. $this->itemsPerPage; 
                $this->result = $this->connection->prepare($this->query);         
        
                if(!empty($this->value_bind_function)){
                    call_user_func($this->value_bind_function, $this->result);
                }
                $this->result->execute();

                $fetched_result =$this->result->get_result();
                return $fetched_result->fetch_all(MYSQLI_ASSOC);                 
            }
        }

    }

    public function linkButtons($increment, $disabled = false, $dots = true){
        $dots = $this->dots;        
       
            //prev
            //$disabled means next page or previous page link disability
            if($increment == 'prev' && $disabled == false && $dots == true){
                $this->link .= '
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - 1).$this->customQueryString.'">'.$this->prevPageIcon.'</a></li> 
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.(1).$this->customQueryString.'">1</a></li>             
                <li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->dotsIcon.'</a></li> 
                ';
            }
            elseif($increment == 'prev' && $disabled == true && $dots == true){
                $this->link .= '
                <li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->prevPageIcon.'</a></li> 
                <li class="page-item disabled"><a class="page-link" href="" disabled>1</a></li>             
                <li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->dotsIcon.'</a></li>             
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
                <li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->dotsIcon.'</a></li> 
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->totalPages).$this->customQueryString.'">'.$this->totalPages.'</a></li>            
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + 1).$this->customQueryString.'">'.$this->nextPageIcon.'</a></li> 
                ';
            }
            elseif($increment == 'next' && $disabled == true && $dots == true){
                $this->link .= '
                <li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->dotsIcon.'</a></li> 
                <li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->totalPages.'</a></li>             
                <li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->nextPageIcon.'</a></li> 
                ';
            }
            elseif($increment == 'next' && $disabled == false && $dots == false){
                $this->link .= '
                <li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + 1).$this->customQueryString.'">'.$this->nextPageIcon.'</a></li>             
                ';
            }                
    }
    
    //displaying links
    public function links(){

        if($this->totalResults > 0){

            $prev = 'prev';
            $next = 'next';
            $half = floor( $this->buttonNumbers/2);

            $this->link .= '<ul class="pagination">';

            //if selected page no is smaller than button no && total page no is equal or smaller than button no
            if($this->page <= $this->buttonNumbers && $this->totalPages <= $this->buttonNumbers){
                if ($this->totalPages <= 1 ) {
                    $this->link = '';
                }else{
                    $this->link .= '<li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->prevJumpIcon.'</a></li>';
                    if($this->page == 1){
                        $this->linkButtons($prev, true);
                    }else{
                        $this->linkButtons($prev, false);
                    }
                        for ($i=1; $i <= $this->totalPages ; $i++) { 
                            $this->link .= '<li class="page-item '.($i==$this->page ? "active": " ").'"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                        }
                    if($this->page == $this->totalPages){
                        $this->linkButtons($next, true);
                    }else{    
                        $this->linkButtons($next, false);
                    } 
                    $this->link .= '<li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->nextJumpIcon.'</a></li>'; 
                }
            }   
            
            //if total page no is greater than button no && total page no is smaller than or equal to button no * 2
            elseif($this->totalPages > $this->buttonNumbers && $this->totalPages <= ($this->buttonNumbers * 2) ){
                
                if ($this->page <= $this->buttonNumbers) {
                // jump only right
                    $this->link .= '<li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->prevJumpIcon.'</a></li>';
                    if($this->page == 1){
                        $this->linkButtons($prev, true);
                    }else{
                        $this->linkButtons($prev);
                    }
                    for ($i=1; $i <= $this->buttonNumbers; $i++) { 
                        $this->link .= '<li class="page-item '.($i==$this->page ? "active": " ").'"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }
                    $this->linkButtons($next);
                                        
                    $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.( $this->totalPages >= ($this->buttonNumbers + $this->page) ? ($this->page + $this->buttonNumbers) : $this->totalPages).$this->customQueryString.'">'.$this->nextJumpIcon.'</a></li>'; 
                }
                elseif ($this->page > $this->buttonNumbers) {
                // jump only left
                    $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - $this->buttonNumbers).$this->customQueryString.'">'.$this->prevJumpIcon.'</a></li>'; 
                    $this->linkButtons($prev);
                    for ($i=$this->buttonNumbers + 1; $i <= $this->totalPages; $i++) { 
                        $this->link .= '<li class="page-item '.($i==$this->page ? "active": " ").'"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                    }
                    if($this->page == $this->totalPages ){
                        $this->linkButtons($next, true);
                    }else{
                        $this->linkButtons($next);
                    }
                    $this->link .= '<li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->nextJumpIcon.'</a></li>'; 
                }
            } 
            
            //if total page no is greater than button no * 2
            elseif ($this->totalPages > ($this->buttonNumbers * 2) ) {

                if($this->page <= $this->buttonNumbers){
                    //only jump right
                    $this->link .= '<li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->prevJumpIcon.'</a></li>';
                    if($this->page == 1){
                        $this->linkButtons($prev, true);
                    }else{
                        $this->linkButtons($prev, false);
                    }
                        for ($i=1; $i <= $this->buttonNumbers ; $i++) { 
                            $this->link .= '<li class="page-item '.($i==$this->page ? "active": " ").'"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                        }
                    $this->linkButtons($next);
                    $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page + $this->buttonNumbers).$this->customQueryString.'">'.$this->nextJumpIcon.'</a></li>';                    
                }
                elseif ($this->page <= ($this->totalPages - ($this->totalPages % $this->buttonNumbers == 0 ? $this->buttonNumbers : $this->totalPages % $this->buttonNumbers)) ) {
                     // jump in both
                     $this->link .= '<li class="page-item"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - $this->buttonNumbers).$this->customQueryString.'">'.$this->prevJumpIcon.'</a></li>';                    
                     $this->linkButtons($prev);
                     for ($i=$this->page - $half; $i <= $this->page + $half; $i++) { 
                         $this->link .= '<li class="page-item '.($i==$this->page ? "active": " ").'"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                     }
                     $this->linkButtons($next);
                     $this->link .= '<li class="page-item "><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.(($this->page + $this->buttonNumbers) > $this->totalPages ? $this->totalPages : $this->page + $this->buttonNumbers).$this->customQueryString.'">'.$this->nextJumpIcon.'</a></li>';
                   
                }else {
                     // jump only left
                     $this->link .= '<li class="page-item "><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.($this->page - $this->buttonNumbers).$this->customQueryString.'">'.$this->prevJumpIcon.'</a></li>';
                     $this->linkButtons($prev);
                     for ($i=$this->totalPages % $this->buttonNumbers == 0 ? ($this->totalPages - $this->buttonNumbers)+ 1 : ($this->totalPages - ($this->totalPages % $this->buttonNumbers))+ 1 ; $i <= $this->totalPages; $i++) { 
                         $this->link .= '<li class="page-item '.($i==$this->page ? "active": " ").'"><a class="page-link" href="'.$_SERVER["SCRIPT_NAME"].'?page='.$i.$this->customQueryString.'">'.$i.'</a></li>';
                     }
                     if($this->page == $this->totalPages ){
                         $this->linkButtons($next, true);
                     }else{
                         $this->linkButtons($next);
                     }
                     $this->link .= '<li class="page-item disabled"><a class="page-link" href="" disabled>'.$this->nextJumpIcon.'</a></li>';              
                }
            }

                     
            $this->link .= '</ul>';
            return $this->link;   
        }     
    }  
}

