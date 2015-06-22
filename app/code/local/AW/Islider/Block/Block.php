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

class AW_Islider_Block_Block extends Mage_Core_Block_Template {
    const REGISTRYSTORAGE_FILES = 'awis_blocks_storage_files';
    const REGISTRYSTORAGE_FILES_ADDED = 'awis_blocks_storage_files_added';

    private $_block = null;
    private $_blocks = null;

    public function getBlockPosition() {
        switch($this->getNameInLayout()) {
            case 'awis.sidebar.left.bottom':
                return AW_Islider_Model_Source_Autoposition::LEFTCOLUMN;
                break;
            case 'awis.sidebar.right.bottom':
                return AW_Islider_Model_Source_Autoposition::RIGHTCOLUMN;
                break;
            case 'awis.content.top':
                return AW_Islider_Model_Source_Autoposition::BEFORECONTENT;
                break;
            default:
                return AW_Islider_Model_Source_Autoposition::NONE;
        }
    }

    protected function _beforeToHtml() {
        if(!$this->getTemplate()) {
            if($this->getData('id') || $this->getData('increment_id'))
                $this->setTemplate('aw_islider/block.phtml');
            else
                $this->setTemplate('aw_islider/blocks.phtml');
        }
        return parent::_beforeToHtml();
    }

    public function getBlocks() {
        if($this->_blocks === null) {
            if($this->getBlockPosition() != AW_Islider_Model_Source_Autoposition::NONE)
                $this->_blocks = Mage::getModel('awislider/slider')->getCollection()
                    ->addPositionFilter($this->getBlockPosition())
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addEnabledFilter();
        }
        return $this->_blocks;
    }

    public function getBlock() {
        if($this->_block === null) {
            if($this->getData('id'))
                $this->_block = Mage::getModel('awislider/slider')->loadByBlockId($this->getData('id'));
            if($this->getData('increment_id'))
                $this->_block = Mage::getModel('awislider/slider')->load($this->getData('increment_id'));
            if(!$this->_block->getData())
                $this->_block = null;
        }
        return $this->_block;
    }

    public function getHtmlCode($block = null) {
        if(is_null($block))
            $block = $this->getBlock();
        $block->afterLoad();
        if($block && $block->getRepresentation()
            && $block->getRepresentation()->getBlock()
            && $blockObj = $this->getLayout()->createBlock($block->getRepresentation()->getBlock())) {
            $blockObj->setAWISBlock($block);
            return $blockObj->toHtml();
        } else {
            return null;
        }
        return $block->toHtml();
    }

    protected function _getRegistryStorage() {
        $_storage = Mage::registry(self::REGISTRYSTORAGE_FILES);
        if(!$_storage) {
            $_storage = new Varien_Object();
            Mage::register(self::REGISTRYSTORAGE_FILES, $_storage);
        }
        return $_storage;
    }

    protected function _canAddFile($file) {
        $_storage = $this->_getRegistryStorage();
        return is_null($_storage->getData(base64_encode($file)));
    }

    protected function _addCss($file) {
        $_storage = $this->_getRegistryStorage();
        $_storage->setData(base64_encode($file), self::REGISTRYSTORAGE_FILES_ADDED);
        return;
        return '<link rel="stylesheet" type="text/css" href="'.$this->getSkinUrl($file).'" />';
    }

    protected function _addJs($file, $location) {
        $_storage = $this->_getRegistryStorage();
        $_storage->setData(base64_encode($file), self::REGISTRYSTORAGE_FILES_ADDED);
        $_fileSrc = $location == 'skin' ? $this->getSkinUrl($file) : Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).$file;
        return '<script type="text/javascript" src="'.$_fileSrc.'"></script>';
    }

    public function getCssJsIncludes($block = null) {
        if($block === null)
            $block = $this->getBlock();
        $includes = array();
        if($block && $block->getRepresentation()) {
            if($block->getRepresentation()->getCss()) {
                $_cssFiles = @explode(',', $block->getRepresentation()->getCss());
                foreach($_cssFiles as $_cssFile)
                    if($this->_canAddFile($_cssFile))
                        $includes[] = $this->_addCss($_cssFile);
            }
            if($block->getRepresentation()->getJs()) {
                $_jsFiles = $block->getRepresentation()->getJs();
                foreach($_jsFiles as $_jsFile) {
                    if((!isset($_jsFile['ifconfig']) || $_jsFile['ifconfig']) && $this->_canAddFile($_jsFile['name'])) {
                        $includes[] = $this->_addJs($_jsFile['name'], $_jsFile['location']);
                    }
                }
            }
        }
        $includes = @implode("\n", $includes);
        return $includes;
    }
}
