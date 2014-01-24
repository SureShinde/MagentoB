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

class AW_Customerattributes_Helper_Type extends Mage_Core_Helper_Data
{
    const CONFIGURATION_FOLDER = 'etc';
    const CONFIGURATION_FILE = 'type.xml';

    protected $_typeConfig = array();

    public function __construct()
    {
        $this->_initTypeConfig();
    }

    protected function _initTypeConfig()
    {
        $configFilePath
            = Mage::getModuleDir(self::CONFIGURATION_FOLDER, $this->_getModuleName()) . DS . self::CONFIGURATION_FILE;
        $config = new Varien_Simplexml_Config($configFilePath);
        $this->_typeConfig = $config->getNode()->asArray();
        uasort($this->_typeConfig, array($this, '_configSort'));
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();
        foreach ($this->_typeConfig as $key => $value) {
            $optionArray[] = array(
                'value' => $key,
                'label' => $this->__($value['label'])
            );
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this->_typeConfig as $key => $value) {
            $array[$key] = $this->__($value['label']);
        }
        return $array;
    }

    /**
     * @param string $code
     *
     * @return AW_Customerattributes_Model_Attribute_TypeInterface|null
     */
    public function getModelByTypeCode($code)
    {
        if (!array_key_exists($code, $this->_typeConfig)) {
            return null;
        }
        return Mage::getModel($this->_typeConfig[$code]['model']);
    }

    /**
     * @param $first
     * @param $second
     *
     * @return int
     */
    private function _configSort($first, $second)
    {
        if ($first['sort_order'] > $second['sort_order']) {
            return 1;
        } elseif ($first['sort_order'] < $second['sort_order']) {
            return -1;
        }
        return 0;
    }
}
