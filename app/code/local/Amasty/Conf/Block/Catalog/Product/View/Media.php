<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Conf
*/
class Amasty_Conf_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
    public function __construct()
    {
        parent::__construct();
    }
    
    protected function _toHtml($status=false)
    {
         if(!$status) {
            $_product = $this->getProduct();
            if(($_product->isConfigurable() || Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($_product->getId())) && (Mage::getStoreConfig('amconf/zoom/enable') || Mage::getStoreConfig('amconf/lightbox/enable'))) {
                $this->setTemplate('amasty/amconf/media.phtml');    
            }
            return $this->_toHtml(true);
        }
        if (!$this->getTemplate()) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }
}