<?php

class Bilna_Wrappinggiftevent_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getPrice($value) {
        preg_match_all('/\((.*?)\)/', $value, $match);
        foreach ($match as $key => $value) {
            foreach ($value as $key => $subvalue) {
                if(strpos(strtolower($subvalue), 'rp') !== false) {
                    return str_replace(array( '(', ')' ), '', $subvalue);
                }
            }
        }
        return ''; 
    }

    public function getDeliveryTime($value) {
        preg_match_all('/\((.*?)\)/', $value, $match);
        foreach ($match as $key => $value) {
            foreach ($value as $key => $subvalue) {
                if(strpos(strtolower($subvalue), 'kerja)') !== false) {
                    return $subvalue;
                }
            }
        }
        return '';      
    }
}