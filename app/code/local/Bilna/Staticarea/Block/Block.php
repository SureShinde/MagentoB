<?php
/**
 * Description of Bilna_Staticarea_Block_Block
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Staticarea_Block_Block extends Mage_Core_Block_Template {
    private $_block = null;
    
    protected function _beforeToHtml() {
        if (!$this->getTemplate()) {
            $this->setTemplate('bilna_staticarea/blocks.phtml');
        }
        
        return parent::_beforeToHtml();
    }
    
    public function getStoreId() {
        return Mage::app()->getStore()->getStoreId();
    }

    public function getBlock() {
    	$store_id = $this->getStoreId();

        if ($this->_block === null) {
            $this->_block = $this->getCollectionData();
        }
      
        return $this->_block;
    }
    
    public function getCollectionData() {
        if ($this->getData('id')) {
            $storeId = Mage::app()->getStore()->getId();
            $cache = Mage::app()->getCache();
            $tags = array ('BRIM_FPC');
            $key = sprintf("STATICAREA_%s_%d", $this->getData('id'), $storeId);

            if ($cacheData = $cache->load($key)) {
                $collectionData = unserialize($cacheData);
            }
            else {
                $collection = Mage::getModel('staticarea/contents')->getCollection();
                $collection->addFieldToSelect('content', 'content');
                $collection->addFieldToSelect('url', 'url');
                $collection->addFieldToFilter('staticarea.block_id', array ('eq' => $this->getData('id')));
                $collection->addFieldToFilter('status', array ('eq' => 1));
                $collection->addFieldToFilter("'{$this->getMagentoDateNow()}'", array ('gteq' => new Zend_Db_Expr('active_from')));
                $collection->addFieldToFilter("'{$this->getMagentoDateNow()}'", array ('lteq' => new Zend_Db_Expr('active_to')));
                $collection->getSelect()->joinLeft(
                    array ('staticarea' => Mage::getSingleton('core/resource')->getTableName('staticarea/manage')),
                    "main_table.staticarea_id = staticarea.id AND staticarea.status_area = 1",
                    array (
                        'area_name' => 'staticarea.area_name',
                        'type' => 'staticarea.type'
                    )
                );
                $collection->setOrder('`order`', 'ASC');
                $collection->getData();
                
                if ($cache->save(serialize($collection), $key, $tags)) {
                    $collectionData = $collection;
                }
                else {
                    $collectionData = null;
                }
            }
        }
        else {
            $collectionData = null;
        }
        
        return $collectionData;
    }

    protected function getMagentoDateNow() {
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        $currentDate = date('Y-m-d', $currentTimestamp);
        
        return $currentDate;
    }

    public function getHtmlCode($block = null) {
        if (is_null($block)) {
            $block = $this->getBlock();
        }
        
        $block->afterLoad();
        
        if ($block) {
            $helper = Mage::helper('cms');
            $processor = $helper->getBlockTemplateProcessor();
            $html = $processor->filter($block->getContent());
            
            return $html;
        }
        else {
            return null;
        }
        
        return $block->toHtml();
    }
}
