<?php
/**
 * Currency model
 *
 * @category   Alw
 * @package    Alw_Currencymodule
 */
class Alw_Currencymodule_Model_Currency extends Mage_Directory_Model_Currency
{
    /**
     * Format price to currency format
     *
     * @param   double $price
     * @param   bool $includeContainer
     * @return  string
     */
    public function format($price, $options=array(), $includeContainer = true, $addBrackets = false)
    {
        return $this->formatPrecision($price, 0, $options, $includeContainer, $addBrackets);
    }
}
