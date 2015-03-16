<?php
/**
 * Description of Bilna_Paymethod_Block_Adminhtml_Binmanage_Grid
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Adminhtml_Binmanage_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    protected $mainAliasName = 'main_table';
    
    public function __construct() {
        parent::__construct();

        $this->setId('binmanage_grid');
        $this->setDefaultSort('issuer');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection() {
        $collection = Mage::getModel('paymethod/binmanage')->getCollection();
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $_helper = Mage::helper('paymethod/binmanage');
        
        $this->addColumn('code', array (
            'header' => $_helper->__('Bin Number'),
            'width' => '100px',
            'index' => 'code',
            'filter_index' => $this->mainAliasName . '.code'
        ));
	  
        $this->addColumn('platform', array(
            'header' => $_helper->__('Platform'),
            'width' => '150px',
            'index' => 'platform',
            'filter_index' => $this->mainAliasName . '.platform',
            'type' => 'options',
            'options' => $this->_preparePlatformOption()
        ));
	  
        $this->addColumn('issuer', array (
            'header' => $_helper->__('Payment Method'),
            'width' => '350px',
            'index' => 'issuer',
            'filter_index' => $this->mainAliasName . '.issuer',
            'type' => 'options',
            'options' => $this->_preparePaymentOption()
        ));
        
        $this->addColumn('name', array (
            'header' => $_helper->__('Bank Information'),
            'index' => 'name',
            'filter_index' => $this->mainAliasName . '.name'
        ));
        
        $this->addExportType('*/*/exportCsv', $_helper->__('CSV'));
	  
        return parent::_prepareColumns();
    }
    
    protected function _preparePlatformOption() {
        return array ('Visa' => 'Visa', 'MasterCard' => 'MasterCard');
    }
    
    protected function _preparePaymentOption() {
        $paymentCollection = explode(',', Mage::getStoreConfig('bilna_customreports/installmentreport/payment_allow'));
        $paymentOption = array ();
       
        foreach ($paymentCollection as $key => $value) {
            $paymentOption[$value] = $this->helper('customreports/installmentreport')->getPaymentmentOptionLabel($value);
        }
        
        return $paymentOption;
    }
    
    public function getRowUrl($row) {
         return $this->getUrl('*/*/edit', array ('id' => $row->getId()));
    }
    
    /**
     * Grid with Ajax Request
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array ('_current' => true));
    }
}
