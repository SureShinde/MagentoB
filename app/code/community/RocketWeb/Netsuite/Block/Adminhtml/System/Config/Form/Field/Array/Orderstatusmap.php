<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Block_Adminhtml_System_Config_Form_Field_Array_Orderstatusmap extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct()
    {
        $this->addColumn('netsuite_status', array(
            'label' => Mage::helper('adminhtml')->__('Net Suite Status'),
            'size'  => 28,
        ));
        $this->addColumn('magento_status', array(
            'label' => Mage::helper('adminhtml')->__('Magento Status'),
            'size'  => 28,
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add new mapping');

        parent::__construct();
        $this->setTemplate('rocketweb_netsuite/system/config/form/field/array_dropdown.phtml');
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }

        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        $rendered = '<select name="'.$inputName.'">';

        if($columnName == 'netsuite_status') $options = $this->getPossibleNetsuiteOrderStatuses();
        if($columnName == 'magento_status') $options = $this->getPossibleMagentoOrderStatuses();

        foreach($options as $option) {
            $rendered .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
        }

        $rendered .= '</select>';
        return $rendered;

    }

   protected function getPossibleMagentoOrderStatuses() {
       $states = Mage::getConfig()->getNode(Mage_Sales_Model_Config::XML_PATH_ORDER_STATES);
       $options = array();
       foreach($states->asArray() as $stateCode=>$stateItem) {
            $options[]=array('value'=>$stateCode,'label'=>$stateItem['label']);
       }

       return $options;
   }

    protected function getPossibleNetsuiteOrderStatuses() {
        return array(
            array('label'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PENDING_APPROVAL,'value'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PENDING_APPROVAL),
            array('label'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PENDING_FULFILLMENT,'value'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PENDING_FULFILLMENT),
            array('label'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_CANCELED,'value'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_CANCELED),
            array('label'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PARTIAL_FULFILLED,'value'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PARTIAL_FULFILLED),
            array('label'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PARTIAL_FULFILLED_PENDING_BILLING,'value'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PARTIAL_FULFILLED_PENDING_BILLING),
            array('label'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PENDING_BILLING,'value'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_PENDING_BILLING),
            array('label'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_BILLED,'value'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_BILLED),
            array('label'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_CLOSED,'value'=>RocketWeb_Netsuite_Model_Config::NETSUITE_ORDER_STATUS_CLOSED),
        );
    }
}