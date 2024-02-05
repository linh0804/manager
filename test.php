<?php
    function check_tag($text) {
        $data = [];
        $lines = explode("\n", $text);
        $number = 0;
        foreach($lines as $key=>$value) {
             $value = trim($value);
             if($value) {
                 $char = str_split($value);
                 foreach($char as $item=>$child) {
                     if($child == '\'' && (!isset($child[$item+1]) || isset($child[$item-1]) && $child[$item-1] !== '\\')) {
                         $data['\''][] = $number + $item;
                     } elseif($child == ';') {
                         $data[';'][] = $number + $item;
                     } elseif($child == '{') {
                         $data['{'][] = $number + $item;
                     } elseif($child == '}') {
                         $data['}'][] = $number + $item;
                     }                     
                 }               
                 $number += count($char);
             }
        }
       // array_reverse('}');
        return $data;
    }
    function no_line($text) {
        $lines = explode("\n", $text);
        $data = '';
        foreach($lines as $key=>$value) {
             $value = trim($value);
             if($value) {
               $data .= $value;
             }
        }
        return $data;
    }
    function format($text) {
        $data = '';
        $tag = check_tag($text);
        $char = str_split(no_line($text));
        $line = 0;
        foreach($char as $item=>$child) {
            $i = 0;
            if(in_array($item, $tag['{'])) {
                $line += 4;
                $data .= $child . "\n" . str_repeat(' ', $line);
                $i++;
            }            
            if(in_array($item, $tag['}'])) {
                $line -= 4;
                if((isset($char[$item+1]) && $char[$item+1] == ')') || (isset($char[$item+1]) && $char[$item+1] == ',')){
                  $data .= $child;
                  continue;
                }
                $data .= $child . "\n" . str_repeat(' ', $line);
                $i++;         
            }
            if(in_array($item, $tag[';'])) {
                $data .= $child . "\n" . str_repeat(' ', $line);        
                $i++;
            }
            $array = [';',' ','\'','"'];
            if($child == ')' && ((isset($char[$item+1]) && !in_array($char[$item+1],$array)) && (isset($char[$item-1]) && $char[$item-1] != '('))) {
                $data .= $child . "\n" . str_repeat(' ', $line);
                $i++;
            }
            if($i == 0) $data .= $child;
            if($child == ',') $data .= ' ';
        }
        return $data . "\n";
    }
    echo format(file_get_contents('./../themes/bootstrap/javascript/function.js'));

    echo format(file_get_contents('style.css'));