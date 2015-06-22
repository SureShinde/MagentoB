<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Advancedreports_Block_Adminhtml_Setup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('advancedreports_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('advancedreports')->__('Report Customization'));
    }

    /**
     * Retrives setup
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function getSetup()
    {
        return Mage::helper('advancedreports/setup');
    }

    protected function  _beforeToHtml()
    {       
        $tabTitle = Mage::helper('advancedreports')->__('General');
        $this->addTab('general', array(
            'label'     => $tabTitle,
            'title'     => $tabTitle,

            'content'   => $this->getLayout()->createBlock('advancedreports/adminhtml_setup_edit_tabs_general')->toHtml()
        ));

        if ($this->getSetup()->getGrid()->getCustomColumnConfigEnabled()){
            $tabTitle = Mage::helper('advancedreports')->__('Columns');
            $this->addTab('columns', array(
                'label'     =>  $tabTitle,
                'title'     => $tabTitle,

                'content'   => $this->getLayout()->createBlock('advancedreports/adminhtml_setup_edit_tabs_columns')->toHtml()
            ));
        }

        parent::_beforeToHtml();        
    }

}
