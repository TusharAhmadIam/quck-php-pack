<?php
class Format{

    public static function short_text($text, $length = 200){
        $text = substr($text, 0 , $length);
        $text = substr($text, 0 , strrpos($text, ' '));
        $text = $text.' ...';
        return $text;        
    }
    
    public static function format_slug($string){
        return urlencode($string);
    }

    public static function format_date($date,$fromat){        
        return date($fromat, strtotime($date));   
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
