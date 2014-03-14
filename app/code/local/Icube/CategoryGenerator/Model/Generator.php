<?php

class Icube_CategoryGenerator_Model_Generator extends Mage_Rule_Model_Rule
{
	protected $_eventPrefix = 'categorygenerator_rule';
	protected $_productIds;
	protected $_productsFilter = null;
	
	protected function _construct()
    {
        parent::_construct();

        $this->_init('categorygenerator/generator');
        $this->setIdFieldName('id');
    }

    public function _afterLoad()
    {
        if (is_string($this->getCategoryData())) {
            $this->setCategoryData(@unserialize($this->getCategoryData()));
        }
    }
    
    public function _beforeSave()
    {
        if (is_array($this->getCategoryData())) {
            $this->setCategoryData(@serialize($this->getCategoryData()));
        }
        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsConditions();
        }
    }
    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    public function getProductsFilter()
    {
        return $this->_productsFilter;
    }

    
    public function afterLoad()
    {
        $this->getResource()->afterLoad($this);
        $this->_afterLoad();
        return $this;
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('categorygenerator/generator_condition_combine');
    }

    public function applyAll()
    {
        $this->getResourceCollection()->walk(array($this->_getResource(), 'updateGeneratorProductData'));
    }
    
    public function getMatchingProductIds()
    {
        if (is_null($this->_productIds)) {
            $this->_productIds = array();
            $this->setCollectedAttributes(array());

                $productCollection = Mage::getResourceModel('catalog/product_collection');
               
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);

                Mage::getSingleton('core/resource_iterator')->walk(
                        $productCollection->getSelect(), array(array($this, 'callbackValidateProduct')), array(
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => Mage::getModel('catalog/product'),
                        )
                );
        }
        return $this->_productIds;
    }
    
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
    }

}