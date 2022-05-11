<?php
class Format{

    public static function short_text($text, $length = 200){
        $text = substr($text, 0 , $length);
        $text = substr($text, 0 , strrpos($text, ' '));
        $text = $text.' ...';
        return $text;        
    }
    
    public static function format_slug($string){
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }

    public static function format_date($date,$time=false){
        if($time == false){
            return date('F d Y', strtotime($date));
        }else{
            return date('F d Y - h:i a',strtotime($date)); // use Format::format_date($date,true)
        }
    }

    public static function page_name(){
        return basename($_SERVER['SCRIPT_NAME'],'.php');
    }

    public static function format_page_name(){
        return ucwords(str_replace('-',' ',self::page_name()));
    }

    public static function dd($data){
        echo '<pre>';
        return print_r($data);
        echo '</pre>';
        die;
    }

}
