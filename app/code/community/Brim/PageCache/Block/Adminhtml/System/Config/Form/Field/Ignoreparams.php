<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file Brim-LLC-Magento-License.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_PageCache
 * @copyright  Copyright (c) 2011-2014 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */


class Brim_Pagecache_Block_Adminhtml_System_Config_Form_Field_Ignoreparams extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {


    public function __construct()
    {
        $this->addColumn('parameter', array(
            'label' => Mage::helper('adminhtml')->__('Parameter'),
            'style' => 'width:160px',
        ));

        $this->_addAfter        = false;
        $this->_addButtonLabel  = Mage::helper('adminhtml')->__('Add New Parameter');

        parent::__construct();
    }


}