<?php
class RocketWeb_Netsuite_Block_Adminhtml_System_Config_Form_Field_Array_Paymentmap extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct()
    {
        $this->addColumn('payment_method', array(
            'label' => Mage::helper('adminhtml')->__('Payment Method'),
            'size'  => 28,
        ));
        $this->addColumn('payment_cc', array(
            'label' => Mage::helper('adminhtml')->__('Credit Card'),
            'size'  => 28,
        ));
        $this->addColumn('internal_netsuite_id', array(
            'label' => Mage::helper('adminhtml')->__('Netsuite internal ID'),
            'size'  => 10,
            'style' => 'width:30px;'
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

        if($columnName == 'payment_method' || $columnName == 'payment_cc') {
            $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
            $rendered = '<select name="'.$inputName.'">';
            if($columnName == 'payment_method') $options = $this->getAllPaymentMethods();
            if($columnName == 'payment_cc') $options = $this->getPaymentCCTypes();
            foreach($options as $option) {
                $rendered .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
            }
            $rendered .= '</select>';
            return $rendered;
        }
        else {
            return parent::_renderCellTemplate($columnName);
        }
    }

    public function getAllPaymentMethods() {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        $options = array();
        foreach ($payments as $paymentCode=>$paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            $options[] = array('value' => $paymentCode, 'label' => $paymentTitle);
        }

        return $options;
    }

    public function getPaymentCCTypes() {
        $ccTypes = Mage::getModel('payment/config')->getCcTypes();
        $options = array();
        $options[]=array('value'=>'','label'=>$this->__('All'));
        foreach($ccTypes as $code=>$title) {
            $options[]=array('value'=>$code,'label'=>$title);
        }
        return $options;
    }

}