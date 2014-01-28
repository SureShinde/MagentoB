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

class AW_Islider_Model_Slider extends Mage_Core_Model_Abstract {
    private $_representation = null;

    public function _construct() {
        $this->_init('awislider/slider');
    }

    public function loadByBlockId($blockId) {
        $this->load($blockId, 'block_id');
        return $this;
    }

    public function _afterLoad() {
        if(is_string($this->getData('store')))
            $this->setData('store', @explode(',', $this->getData('store')));
    }

    public function _beforeSave() {
        if(is_array($this->getData('store')))
            $this->setData('store', @implode (',', $this->getData('store')));
    }

    public function getImagesCollection() {
        $_collection = Mage::getModel('awislider/image')->getCollection();
        $_collection->addSliderFilter($this->getData('id') ? $this->getData('id') : -1);
        return $_collection;
    }

    public function getRepresentation() {
        if($this->_representation === null) {
            $this->_representation = new Varien_Object(array(
                'block' => 'awislider/representations_default_block',
                'css' => 'aw_islider/representations/default/style.css',
                'js' => array(
                    array(
                        'name' => 'aw_islider/representations/default/default.js',
                        'location' => 'skin'
                    ),
                    array(
                        'name' => 'aw_islider/scriptaculous/scriptaculous.js',
                        'location' => 'js',
                        'ifconfig' => !Mage::helper('awislider')->checkVersion('1.4')
                    )
                )
            ));
        }
        return $this->_representation;
    }
}
