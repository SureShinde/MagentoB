<?php

abstract class Bilna_Pricevalidation_Model_Source_Abstract extends Varien_Object
{
    protected $_translateModule = 'Bilna_Pricevalidation';

    abstract public function toOptionHash($selector=false);

    public function toOptionArray($selector=false)
    {
        $arr = array();
        foreach ($this->toOptionHash($selector) as $v=>$l) {
            if (!is_array($l)) {
                $arr[] = array('label'=>$l, 'value'=>$v);
            } else {
                $options = array();
                foreach ($l as $v1=>$l1) {
                    $options[] = array('value'=>$v1, 'label'=>$l1);
                }
                $arr[] = array('label'=>$v, 'value'=>$options);
            }
        }
        return $arr;
    }

    public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->_translateModule);
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }
}
