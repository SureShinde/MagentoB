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
 * @package    AW_Clickcheckout
 * @version    1.1.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Clickcheckout_Block_Block extends Mage_Core_Block_Template
{
    const ADDRESSES_LENGTH = 31;

    protected $_product = null;
    /**
     * @return Mage_Core_Model_Abstract
     */
    private function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }


    public function getProductByUrl()
    {
        $this->_product = Mage::getModel('catalog/product');
        $request = Mage::app()->getFrontController()->getRequest();
        if(($request->getRequestedRouteName() == 'catalog') && ($request->getRequestedControllerName() == 'product') && ($request->getRequestedActionName() == 'view')){
            $productId = $request->getParam('id');
           $this->_product = $this->_product->load($productId);
            Mage::register('current_product', $this->_product);
        }
    }

    public function getProduct()
    {
        if (null === $this->_product) {
            $this->_product = Mage::registry('current_product');
            if (null === $this->_product) {
               $this->getProductByUrl();
            }
        }
        return $this->_product;
    }

    public function getLastMessage(){
        $sess = Mage::getSingleton('checkout/session');
        $mess = $sess->getMessages();
        if($mess->getLastAddedMessage()->getType()=='error'){
        $note= $mess->getLastAddedMessage()->getCode();
            $mess->clear();
          return $note;
        }
    }
    /**
     * @return mixed
     */
    public function currentProductType()
    {
        $product = $this->getProduct();
        return $product->getTypeId();
    }

    /**
     * @return mixed
     */
    public function currentProductId()
    {
        $product = $this->getProduct();
        return $product->getId();
    }

    /**
     * @return mixed
     */
    public function currentProductQty()
    {
        $product = $this->getProduct();
        if ($product->getStockItem()->getManageStock()) {
            return $product->getStockItem()->getStockQty();
        } else {
            return $this->maxQty();
        }
    }

    public function getProductUrl()
    {
        $product = $this->getProduct();
        return $product->getProductUrl();
    }

    /**
     * @return float
     */
    public function maxQty()
    {
        return (float) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MAX_SALE_QTY);
    }

    /**
     * Return true if product and quote doesn't requires shipping
     * @return bool
     */
    public function checkVirtual()
    {
        return Mage::helper('awclickcheckout')->checkVirtual();
    }

    /**
     * Redirector for native checkout method
     * @return string
     */
    public function getRedirectorLink()
    {
        return Mage::getUrl('awclickcheckout/native');
    }

    /**
     * Return billing/shipping addresses if exists
     * Sets default addresses as selected
     * @param null $type
     * @return string
     */
    public function getAddressesHtmlSelect($type = null)
    {
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $options = array();
            foreach ($session->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }
            if (count($options) > 1) {
                if ($type == 'billing') {
                    $address = $session->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $session->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
                $select = $this->getLayout()->createBlock('core/html_select')
                    ->setName($type . '_address_id')
                    ->setId($type . '_address_id')
                    ->setClass('aw-clickcheckout-address-select')
                    ->setValue($addressId)
                    ->setOptions($options);
                $select->addOption('0', Mage::helper('awclickcheckout')->__('Change'));
                return $select->getHtml();
            } elseif (count($options) == 1) {
                $href = Mage::getUrl('customer/address');
                $onclick = '';
                if (Mage::helper('awclickcheckout')->canOnePage()) {
                    $href = "#";
                    $onclick = 'onclick="callNgo(\'' . $type . '_address_id\'); return false;"';
                }
                return '<div class="aw-clickcheckout-address-text" title="' . $options[0]['label'] . '"><input id="' . $type . '_address_id" type="hidden" name="' . $type . '_address_id" value="' . $options[0]['value'] . '"/>' . str_replace(' ', '&nbsp;', mb_substr($options[0]['label'], 0, self::ADDRESSES_LENGTH,'utf-8')) . '...&nbsp;<a href="' . $href . '" ' . $onclick . ' >'.Mage::helper('awclickcheckout')->__('Edit').'</a>&nbsp;</div>';
            } else return false;
        }
        return 'You don\'t have any addresses';
    }

    /**
     * @return string
     */
    public function getBillingAddresses()
    {
        return $this->getAddressesHtmlSelect('billing');
    }

    /**
     * @return string
     */
    public function getShippingAddresses()
    {
        return $this->getAddressesHtmlSelect('shipping');
    }

    /**
     * @return string
     */
    public function customerGroupId()
    {
        return (string)$this->_getSession()->getCustomerGroupId();
    }

    /**
     * Check to display block on product page for current customer group
     * @return bool
     */
    public function canShow()
    {
        $helper = Mage::helper('awclickcheckout');
        if ($helper->isEnabled() && $this->getProduct()->getId()) {
            $groupId = $this->customerGroupId();
            $enabledGroups = $helper->getAWOCCGeneralParam('enabled');
            if (strpos($enabledGroups, $groupId) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set Template depends customer loggined or not
     */
    protected function _beforeToHtml()
    {
        if ($this->canShow()) {
            if ($this->customerGroupId() == '0' || !$this->helper('customer')->isLoggedIn()) {
                $this->setTemplate('aw_clickcheckout/anonimnous.phtml');
            } else {
                $this->setTemplate('aw_clickcheckout/block.phtml');
            }
        }
    }
}
