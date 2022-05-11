<?php
class Security{
    
    public static function e($data){
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        $data = stripslashes($data);
        $data = trim($data);

        return $data;
    }

    public static function e_decode($data){
        $data = htmlspecialchars_decode($data);
        return $data;
    }

    public static function slug($data){
        return self::e(strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($data, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-')));
    }

    public static function get_token(){  
        $_SESSION['user_token'] = md5(uniqid());
        return $_SESSION['user_token']; 
    }

    public static function check_token(){
        
        if(isset($_SESSION['user_token']) && isset($_POST['token']) && $_SESSION['user_token'] == self::e($_POST['token'])){ 
            return true;
        }else{
            unset($_SESSION['user_token']);
            header('location:404.php');
            exit;
        }
    }  
    
    public static function unset_user_token(){
        unset($_SESSION['user_token']);
    }
    
}
