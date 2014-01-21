<?php
/**
 * @category    Alw
 * @package     Alw_Customer
 */

class Alw_Customer_Block_Widget_Dob extends Mage_Customer_Block_Widget_Dob
{
    
    /**
     * Sort date inputs by dateformat order of current locale
     *
     * @return string
     */
	 /*Change date format from MM-DD-YYYY to DD-MM-YYYY*/
    public function getSortedDateInputs()
    {
        $strtr = array(
            '%b' => '%1$s',
            '%B' => '%1$s',
            '%m' => '%1$s',
            '%d' => '%2$s',
            '%e' => '%2$s',
            '%Y' => '%3$s',
            '%y' => '%3$s'
        );

        $dateFormat = preg_replace('/[^\%\w]/', '\\1', $this->getDateFormat());

        return sprintf(strtr($dateFormat, $strtr),
            $this->_dateInputs['d'], $this->_dateInputs['m'], $this->_dateInputs['y']);
    }
}
