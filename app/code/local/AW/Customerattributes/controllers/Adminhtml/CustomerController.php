<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

require_once 'Mage/Adminhtml/controllers/CustomerController.php';
class AW_Customerattributes_Adminhtml_CustomerController extends Mage_Adminhtml_CustomerController
{
    public function indexAction()
    {
        $this->_title($this->__('Customer Attributes'))->_title($this->__('Manage Customers'));
        $this->loadLayout();
        $this->_setActiveMenu('customer/aw_customerattributes');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function downloadAttachmentAction()
    {
        $this->_initCustomer('customer_id');
        $attributeCode = $this->getRequest()->getParam('attribute_code', '');
        $attribute = Mage::getModel('aw_customerattributes/attribute')->load($attributeCode, 'code');
        $file = Mage::helper('aw_customerattributes/image')->viewFile($attribute, Mage::registry('current_customer'));

        if ((!$attribute->getId() || !Mage::registry('current_customer')->getId()) || is_null($file)) {
            return $this->norouteAction();
        }

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Content-type', $file['header']['content_type'], true)
            ->setHeader('Content-Length', $file['header']['content_length'])
            ->setHeader('Last-Modified', $file['header']['content_modified']);
        if (isset($file['filename'])) {
            $this->getResponse()->setHeader(
                'Content-Disposition', 'attachment; filename="' . $file['filename'] . '"', true
            );
        }
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        while (false !== ($buffer = $file['content_stream']->streamRead())) {
            echo $buffer;
        }
        exit();
    }

    protected function _title($text = null, $resetIfExists = false)
    {
        if (Mage::helper('aw_customerattributes')->checkMageVersion()) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/aw_customerattributes/manage_customers');
    }
}