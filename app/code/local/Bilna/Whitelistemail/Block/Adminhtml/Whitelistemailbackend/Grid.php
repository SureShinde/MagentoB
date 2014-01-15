<?php
/**
 * Description of Bilna_Whitelistemail_Block_Adminhtml_Whitelistemailbackend_Grid
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Whitelistemail_Block_Adminhtml_Whitelistemailbackend_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    protected $_tableCustomerEntity = 'customer_entity';
    protected $_tableWhitelistEmail = 'whitelist_email';
    
    protected $_aliasCustomerEntity = 'main_table';
    protected $_aliasWhitelistEmail = 'b';
    
    public function __construct() {
        parent::__construct();

        $this->setId('whitelistemail_grid');
        $this->setDefaultSort('email');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection() {
        $collection = Mage::getModel('whitelistemail/customer')->getCollection()->addFieldToFilter('entity_type_id', 1);
        $collection->getSelect()->joinLeft(
            array ($this->_aliasWhitelistEmail => $this->_tableWhitelistEmail),
            "`{$this->_aliasCustomerEntity}`.`entity_id` = `{$this->_aliasWhitelistEmail}`.`customer_id`",
            array (
                "code" => "{$this->_aliasWhitelistEmail}.code",
                "sent" => "IF ({$this->_aliasWhitelistEmail}.sent IS NULL, 0, {$this->_aliasWhitelistEmail}.sent)",
                "type" => "IF ({$this->_aliasWhitelistEmail}.type IS NULL, 0, {$this->_aliasWhitelistEmail}.type)"
            )
        );
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('email', array (
            'header' => Mage::helper('whitelistemail')->__('Email'),
            'align' => 'left',
            'index' => 'email'
        ));
	  
        $this->addColumn('code', array (
            'header' => Mage::helper('whitelistemail')->__('Code'),
            'index' => 'code',
            'filter_index' => $this->_aliasWhitelistEmail . '.code',
            'align' => 'left',
            'width' => '120px',
            'sortable' => false
        ));
        
        $this->addColumn('sent', array (
            'header' => Mage::helper('whitelistemail')->__('Sent'),
            'index' => 'sent',
            'filter_index' => $this->_aliasWhitelistEmail . '.sent',
            'align' => 'right',
            'width' => '100px'
        ));
        
        $this->addColumn('type', array (
            'header' => Mage::helper('whitelistemail')->__('Type'),
            'index' => 'type',
            'filter_index' => $this->_aliasWhitelistEmail . '.type',
            'align' => 'center',
            'type'  => 'options',
            'width' => '90px',
            'options' => $this->_prepareTypeOption(),
            'sortable' => false
        ));
	  
        return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('customer_id');
        $this->getMassactionBlock()->addItem('send_email', array (
            'label' => Mage::helper('whitelistemail')->__('Send Email'),
            'url' => $this->getUrl('*/*/massSendemail', array ('' => '')), // public function massDeleteAction() in Bilna_Whitelistemail_Adminhtml_WhitelistemailbackendController
            'confirm' => Mage::helper('whitelistemail')->__('Are you sure?')
        ));

        return $this;
    }
    
    protected function _prepareTypeOption() {
        return array (
            1 => 'Whitelist',
            2 => 'Graylist',
            3 => 'Blacklist'
        );
    }
    
    /**
     * Grid with Ajax Request
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array ('_current' => true));
    }
}
