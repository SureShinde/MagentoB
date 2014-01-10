<?php
/**
 *
 *
 * @category   AW
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Block_Adminhtml_Campaign_Edit_Tab_Products
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);

        $_fieldset = $_form->addFieldset('automation_settings', array('legend' => $this->__('Please select product(s)')));

        $productGridHtml = Mage::getSingleton('core/layout')
            ->createBlock(
                'awaffiliate/adminhtml_campaign_edit_tab_productsgrid', null,array('automation_data_products' => array())
            )
            ->toHtml()
        ;
        $_fieldset->addField(
            'gridcontainer_products', 'note',
            array(
                'label' => $this->__('Select Products'),
                'text' => $productGridHtml
            )
        );

        $_fieldset->addField('automation_data_products', 'hidden', array('name' => 'automation_data[products]'));

    }

    public function getTabLabel()
    {
        return Mage::helper('awaffiliate')->__('Product (for product campaign type only)');
    }


    public function getTabTitle()
    {
        return Mage::helper('awaffiliate')->__('Rate Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _getTiersData($profit)
    {
        if ($profit->hasTierPrice())
            return $profit->getTierPrice();
        $profitId = $profit->getId();
        $_tiersCollection = Mage::getModel('awaffiliate/profit_tier_rate')->loadByProfitId($profitId);
        $_tiersData = array();
        foreach ($_tiersCollection->getItems() as $item) {
            $_data = array(
                'cust_group' => $item->getAffiliateGroupId(),
                'amount' => $item->getProfitAmount(),
                'rate' => $item->getProfitRate(),
            );
            $_tiersData[] = $_data;
        }
        return $_tiersData;
    }
}
