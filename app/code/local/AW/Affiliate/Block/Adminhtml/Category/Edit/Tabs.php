<?php



class AW_Affiliate_Block_Adminhtml_Category_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awaffiliate_category_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('awaffiliate')->__('Manage Category'));
    }

    protected function _beforeToHtml()
    {
        $this->_updateActiveTab();
        $this->addTab('categories', array(
          'label'     => Mage::helper('catalog')->__('Categories'),
          'url'       => $this->getUrl('*/*/categories', array('_current' => true)),
          'class'     => 'ajax',
      ));   
        return parent::_beforeToHtml();
    }

    protected function _updateActiveTab()
    {
        $tabId = $this->getRequest()->getParam('tab');
        if ($tabId) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if ($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }
}
