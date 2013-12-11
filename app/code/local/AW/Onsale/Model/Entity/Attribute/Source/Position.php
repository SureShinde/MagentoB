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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

    
class AW_Onsale_Model_Entity_Attribute_Source_Position extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrive all attribute options
     * @return array
     */
    public function getAllOptions()
    {
        $options = Mage::getSingleton('onsale/system_config_source_position')->toOptionArray();
        $oO = array();
        foreach ($options as $option){
            $oO[] = $option;
        }
        return $oO;    
    }

    public function getFlatColums() {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        $column = array(
            'unsigned'  => false,
            'default'   => null,
            'extra'     => null
        );

        if (!method_exists(Mage::helper('core'), 'useDbCompatibleMode') || Mage::helper('core')->useDbCompatibleMode()) {
            $column['type']     = 'varchar(255)';
            $column['is_null']  = true;
        } else {
            $column['type']     = Varien_Db_Ddl_Table::TYPE_TEXT;
            $column['length']   = 255;
            $column['nullable'] = true;
            $column['comment']  = $attributeCode . ' column';
        }
        return array($attributeCode => $column);
    }

    public function getFlatUpdateSelect($store) {

        return Mage::getResourceModel('eav/entity_attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }

}
