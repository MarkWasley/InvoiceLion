<?php
namespace MintyPHP;

class InvoiceTemplate
{
    private static function functions() {
        $formatNumber = function($number, $decimals,$decimalPoint,$thousandsSeperator) {
            return number_format($number, $decimals, chr($decimalPoint), $thousandsSeperator?chr($thousandsSeperator):'');
        };
        
        $translateMonth = function($date,$lang) {
            $months = array(
                'en' => array("January","February","March","April","May","June","July","August","September","October","November","December"),
                'nl' => array("januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december"),
            );
            return str_replace($months['en'], $months[$lang], $date);
        };
        
        $relativeDate = function($days) {
            return strtotime("+$days days");
        };
        
        $formatDate = function($date,$format) {
            return date($format, $date);
        };
        
        $eq = function($a,$b) {
            return $a==$b;
        };
        
        return array(
            'encodeBase64'=>'base64_encode',
            'formatDate'=>$formatDate,
            'formatNumber'=>$formatNumber,
            'relativeDate'=>$relativeDate,
            'translateMonth'=>$translateMonth,
            'convertLines'=>'nl2br',
            'capitalize'=>'ucfirst',
            'eq'=>$eq,
        );
    }

    private static function render($template, $data) {
        $result = Template::render($template, $data, static::functions());
        return preg_replace('/<script[^>]*>(.*?)<\/script>/is', "", $result);
    }
}