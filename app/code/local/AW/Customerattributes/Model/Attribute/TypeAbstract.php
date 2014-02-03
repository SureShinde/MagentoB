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


abstract class AW_Customerattributes_Model_Attribute_TypeAbstract
    implements AW_Customerattributes_Model_Attribute_TypeInterface
{

    const FRONTEND_ATTRIBUTE_CODE_PREFIX = 'aw_ca_';

    /**
     * @var AW_Customerattributes_Model_Attribute
     */
    protected $_attribute = null;

    /**
     * @var AW_Customerattributes_Block_Widget_Backend_GridInterface|null
     */
    protected $_backendGridRenderer = null;
    /**
     * @var AW_Customerattributes_Block_Widget_Backend_FormInterface|null
     */
    protected $_backendFormRenderer = null;
    /**
     * @var AW_Customerattributes_Block_Widget_Frontend_FormInterface|null
     */
    protected $_frontendFormRenderer = null;

    /**
     * @return AW_Customerattributes_Block_Widget_Backend_GridInterface
     * @throws Exception
     */
    public function getBackendGridRenderer()
    {
        if (is_null($this->_backendGridRenderer)) {
            $this->_backendGridRenderer = $this->_getBackendGridRenderer();
            if (!($this->_backendGridRenderer instanceof AW_Customerattributes_Block_Widget_Backend_GridInterface)) {
                throw new Exception('Wrong renderer');
            }
            $this->_backendGridRenderer->setTypeModel($this);
        }
        return $this->_backendGridRenderer;
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Backend_FormInterface
     * @throws Exception
     */
    public function getBackendFormRenderer()
    {
        if (is_null($this->_backendFormRenderer)) {
            $this->_backendFormRenderer = $this->_getBackendFormRenderer();
            if (!($this->_backendFormRenderer instanceof AW_Customerattributes_Block_Widget_Backend_FormInterface)) {
                throw new Exception('Wrong renderer');
            }
            $this->_backendFormRenderer->setTypeModel($this);
        }
        return $this->_backendFormRenderer;
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Frontend_FormInterface
     * @throws Exception
     */
    public function getFrontendFormRenderer()
    {
        if (is_null($this->_frontendFormRenderer)) {
            $this->_frontendFormRenderer = $this->_getFrontendFormRenderer();
            if (!($this->_frontendFormRenderer instanceof AW_Customerattributes_Block_Widget_Frontend_FormInterface)) {
                throw new Exception('Wrong renderer');
            }
            $this->_frontendFormRenderer->setTypeModel($this);
        }
        return $this->_frontendFormRenderer;
    }

    /**
     * @param AW_Customerattributes_Model_Value $valueModel
     *
     * @return AW_Customerattributes_Model_Value
     */
    public function beforeSave($valueModel)
    {
        return $valueModel;
    }

    /**
     * @return AW_Customerattributes_Model_Attribute_TypeAbstract
     */
    public function afterAttributeDelete()
    {
        return $this;
    }

    /**
     * @param mixed $value
     */
    public function validate($value)
    {
        return;
    }

    /**
     * @param AW_Customerattributes_Model_Attribute $attribute
     *
     * @return AW_Customerattributes_Model_Attribute_TypeAbstract
     */
    public function setAttribute(AW_Customerattributes_Model_Attribute $attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * @return AW_Customerattributes_Model_Attribute_TypeAbstract
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }
}