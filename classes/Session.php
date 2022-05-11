<?php

class Session{
    
    public static function init(){
        if(session_status() == PHP_SESSION_NONE){
            session_start();
        }
    }

    public static function set($key,$value){
        $_SESSION[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function get($key){
        if(isset($_SESSION[$key])){
            return htmlspecialchars($_SESSION[$key], ENT_QUOTES, 'UTF-8');
        }else{
            return false;
        }
    }

    public static function check_login(){
        if(Session::get('username') && Session::get('admin_id') && Session::get('role')){
            $login = true;
        }else{
            header('location:login.php');
            exit;
        }    
        if($login == false){
            exit;
        }
    }

    public static function logout(){
        session_destroy();
            header('location:../login.php');
            exit;
    }

    public static function alert($key){
        if(isset($_SESSION[$key])){
            return htmlspecialchars($_SESSION[$key], ENT_QUOTES, 'UTF-8');
        }else {
            return false;
        }
    }      
    
}