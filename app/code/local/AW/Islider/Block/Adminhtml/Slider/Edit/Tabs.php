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
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Islider_Block_Adminhtml_Slider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    public function __construct() {
        parent::__construct();
        $this->setId('awislider_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Slider Information'));
    }
    
    protected function _beforeToHtml() {
        $this->addTab('general', array(
            'label' => $this->__('General'),
            'title' => $this->__('General'),
            'content' => $this->getLayout()->createBlock('awislider/adminhtml_slider_edit_tabs_general')->toHtml()
        ));
        
        if($this->getRequest()->getParam('id')) {
            $_imagesTabContent = $this->getLayout()->createBlock('awislider/adminhtml_slider_edit_tabs_images')
                ->setData('awis_pid', $this->getRequest()->getParam('id'))
                ->toHtml();
        } else {
            $_imagesTabContent = $this->__('You can manage images right after saving this slider');
        }

        $this->addTab('images', array(
            'label' => $this->__('Images'),
            'title' => $this->__('Images'),
            'content' =>  $_imagesTabContent
        ));

        if($this->getRequest()->getParam('continue_tab'))
            $this->setActiveTab($this->getRequest()->getParam('continue_tab'));

        return parent::_beforeToHtml();
    }
}
