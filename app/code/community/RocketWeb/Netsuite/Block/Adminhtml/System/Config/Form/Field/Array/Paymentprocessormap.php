<?php
class RocketWeb_Netsuite_Block_Adminhtml_System_Config_Form_Field_Array_Paymentprocessormap extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct()
    {
        $this->addColumn('payment_method', array(
            'label' => Mage::helper('adminhtml')->__('Payment Method'),
            'size'  => 28,
        ));
        $this->addColumn('internal_netsuite_id', array(
            'label' => Mage::helper('adminhtml')->__('Netsuite internal ID'),
            'size'  => 10,
            'style' => 'width:30px;'
        ));
        $this->addColumn('payment_processor_helper_class',array(
            'label' => Mage::helper('adminhtml')->__('Payment Processor helper class'),
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

        if($columnName == 'payment_method') {
            $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
            $rendered = '<select name="'.$inputName.'">';
            if($columnName == 'payment_method') $options = $this->getAllPaymentMethods();
            foreach($options as $option) {
                $rendered .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
            }
            $rendered .= '</select>';
            return $rendered;
        }
        else if($columnName == 'payment_processor_helper_class') {
            $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
            $rendered = '<select name="'.$inputName.'">';
            $options = $this->getAllPaymentProcessorHelperClasses();
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

    public function getAllPaymentProcessorHelperClasses() {
        $options = array();

        $path = Mage::getModuleDir('Helper', 'RocketWeb_Netsuite').DS.'Helper'.DS.'Paymentprocessors';
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $fileInfo = pathinfo($entry);
                    if($fileInfo['extension'] == 'php' && strtolower($fileInfo['filename'])!='abstract') {
                        $options[] = array('value' => strtolower($fileInfo['filename']), 'label'=>$fileInfo['filename']);
                    }
                }
            }
        }
        return $options;
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


}