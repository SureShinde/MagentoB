<?php
/**
 * Description of Bilna_Ccp_Model_Selectbox_Salesorder
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Ccp_Model_Resource_Ccpmodel extends Mage_Core_Model_Resource_Db_Abstract{

    protected function _construct()
    {
        $this->_init('ccp/ccpmodel', 'product_id');
    }

    public function toOptionArray() {
        return array(
            array('value' => 'complete', 'label' =>'complete')
            , array('value' => 'processing', 'label' =>'processing')
            , array('value' => 'processing_cod', 'label' =>'processing_cod')
            , array('value' => 'shipping_cod', 'label' =>'shipping_cod')
            , array('value' => 'holded', 'label' =>'holded')
        );
    }
}
