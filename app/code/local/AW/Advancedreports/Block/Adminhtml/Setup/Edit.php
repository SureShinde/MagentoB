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

class AW_Advancedreports_Block_Adminhtml_Setup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Retrives setup
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function getSetup()
    {
        return Mage::helper('advancedreports/setup');
    }

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'report_id';
        $this->_blockGroup = 'advancedreports';
        $this->_mode = 'edit';
        $this->_controller = 'adminhtml_setup';

        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_updateButton('save', 'label', Mage::helper('advancedreports')->__('Save'));

//        $this->_addButton('saveandcontinue', array(
//            'label'     => Mage::helper('adminhtml')->__('Apply'),
//            'onclick'   => 'saveAndContinueEdit()',
//            'class'     => 'save',
//        ), -100);

//        $this->_formScripts[] = "
//            function saveAndContinueEdit(){
//                editForm.submit($('edit_form').action+'back/edit/');
//            }
//        ";

    }

    public function getHeaderText()
    {
        return $this->getSetup()->getReportTitle() ? $this->getSetup()->getReportTitle() : Mage::helper('advancedreports')->__('');
    }

    public function getBackUrl()
    {
        return $this->getSetup()->getBackUrl();
    }
}