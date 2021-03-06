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
 * @package    AW_Collpur
 * @version    1.0.5
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Collpur_Block_Deals extends AW_Collpur_Block_BaseDeal
{

    protected $_baseDeal;
    protected $_collection;
    private $_limitParam = NULL;

    protected $_awcpStoreModel;
    protected $_currencyHelper;
    protected $_dealModel;
//     protected $_cmsdeal;
//     protected $_bridge;

    protected function _construct()
    {
        parent::_construct();
        if ($this->getCmsmode()) {
            AW_Collpur_Helper_Deals::setActiveMenus($this->getCmsmode());
        }
        $this->setTemplate('aw_collpur/deals/list.phtml');
        $this->setAvailableDealsScope($this->getAvailableDeals());

        $this->_awcpStoreModel = Mage::app()->getStore();
        $this->_currencyHelper = Mage::helper('core');
        $this->_dealModel = Mage::getModel('collpur/deal');
//         $this->_bridge = Mage::getBlockSingleton('collpur/deals');
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    public function getAvailableDeals()
    {

        if (isset($this->_data['available_deals_scope'])) {
            return $this->getAvailableDealsScope();
        }

        $dealsCollection = Mage::getModel('collpur/deal')
            ->getCollection()
            ->addIsActiveFilter();

        $section = Mage::app()->getRequest()->getParam('section');
        if(is_null($section)) $section = AW_Collpur_Helper_Deals::RUNNING;

        if ($section == AW_Collpur_Helper_Deals::CLOSED || $this->getCmsmode() == AW_Collpur_Helper_Deals::CLOSED) {
            $dealsCollection->getClosedDeals();
            $this->_limitParam = AW_Collpur_Helper_Deals::CLOSED;
        } elseif ($section == AW_Collpur_Helper_Deals::NOT_RUNNING || $this->getCmsmode() == AW_Collpur_Helper_Deals::NOT_RUNNING) {
            $dealsCollection->getFutureDeals();
            $this->_limitParam = AW_Collpur_Helper_Deals::NOT_RUNNING;
        } elseif ($section == AW_Collpur_Helper_Deals::RUNNING || $this->getCmsmode() == AW_Collpur_Helper_Deals::RUNNING) {
            $dealsCollection->getActiveDeals();
            $this->_limitParam = AW_Collpur_Helper_Deals::RUNNING;
        } elseif ($section == AW_Collpur_Helper_Deals::FEATURED || $this->getCmsmode() == AW_Collpur_Helper_Deals::FEATURED) {
            $dealsCollection->getActiveDeals()->addFeaturedFilter();
            $this->_limitParam = AW_Collpur_Helper_Deals::FEATURED;
        } else {
            return new Varien_Data_Collection;
        }

        return $dealsCollection;
    }

    public function getPurchasesCount($dealId) {
        return $this->_dealModel->load($dealId)->getPurchasesCount();
    }

    public function getFeaturedSliders()
    {
        $dealsCollection = Mage::getModel('collpur/deal')
            ->getCollection()
            ->addIsActiveFilter()
        	->getActiveDeals()
        	->addFeaturedFilter()
        	->setPageSize(4);

        return $dealsCollection;
    }

    protected function _prepareLayout()
    {
        $pager = $this->getLayout()->createBlock('page/html_pager', 'available_deals_pager');
        $pager->setAvailableLimit(array("1"=> "1", "15" => "15", "30" => "30", "60" => "60", "all" => "all"));
        $pager->setLimitVarName('dealslimit' . $this->_limitParam);
        $pager->setPageVarName('deals');
        $pager->setPrevNext(false);
        $pager->setCollection($this->getAvailableDeals());
        $this->setChild('available_deals_pager', $pager);
        

        $pager = $this->getLayout()->createBlock('page/html_pager', 'available_deals_pager_extra');
        $pager->setAvailableLimit(array("1"=> "1", "15" => "15", "30" => "30", "60" => "60", "all" => "all"));
        $pager->setLimitVarName('dealslimit' . $this->_limitParam);
        $pager->setPageVarName('deals');
        $pager->setPrevNext(true);
        $pager->setCollection($this->getAvailableDeals());
        $this->setChild('available_deals_pager_extra', $pager);
        $this->_modifyCrumbs($this->getLayout(), false, false, 'category');
    }

    public function isNative()
    {
        return 'deals' == Mage::app()->getRequest()->getModuleName();
    }

    public function getProduct($deal) {
        $this->_originalProductId = $deal->getProduct()->getId();
        $product = Mage::getModel('catalog/product')->load($deal->getProduct()->getId())->setDeal($deal);
        $product->setData('price', $product->getDeal()->getPrice());
        $product->setData('final_price', $product->getDeal()->getPrice());

//         $this->_modifyMeta($product, $deal);

        return $product;
    }

    public function getOriginalProduct($deal) {
        $product = Mage::getModel('catalog/product')->load($deal->getProduct()->getId());

        return $product;
    }

    public function getDealPricesSpare($orig, $deal) {

        $priceInfo = new Varien_Object();
        $price = $this->_currencyHelper->currency($deal->getPrice());
        $save = $this->_currencyHelper->currency($orig->getPrice() - $deal->getPrice(), true, false);

        /* Avoide devision by zero */
        $discount = 0;
        if ($orig->getPrice()) {
            $discount = ($orig->getPrice() - $deal->getPrice()) / $orig->getPrice() * 100;
        }

        $priceInfo->setPrice($price)
                ->setSaveAmount($save)
                ->setPercentDiscount(round($discount, 1));

        return $priceInfo;
    }

    public function hasOptions($deal) {
        if ($this->getProduct($deal)->getTypeInstance(true)->hasOptions($this->getProduct($deal))) {
            return true;
        }
        return false;
    }
}
