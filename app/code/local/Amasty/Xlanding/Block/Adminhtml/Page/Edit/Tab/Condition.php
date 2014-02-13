<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Label
*/ 
class Amasty_Xlanding_Block_Adminhtml_Page_Edit_Tab_Condition extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Xlanding_Helper_Data */
        $hlp = Mage::helper('amlanding');
        
        $fldState = $form->addFieldset('state', array('legend'=> $hlp->__('State')));
        $fldState->addField('is_new', 'select', array(
            'label'     => $hlp->__('Is New'),
            'name'      => 'is_new',
            'values'    => array(
                0 => $hlp->__('Does not matter'), 
                1 => $hlp->__('No'), 
                2 => $hlp->__('Yes'), 
             ),
        ));
        $fldState->addField('is_sale', 'select', array(
            'label'     => $hlp->__('Is on Sale'),
            'name'      => 'is_sale',
            'values'    => array(
                0 => $hlp->__('Does not matter'), 
                1 => $hlp->__('No'), 
                2 => $hlp->__('Yes'), 
             ),
        ));   
        
        $fldAttr = $form->addFieldset('categories', array('legend'=> $hlp->__('Category')));
        $fldAttr->addField('category', 'select', array(
            'label'     => $hlp->__('Category is'),
            'name'      => 'category',
            'values'    => $this->getTree(),
        ));        
        
               
        $fldStock = $form->addFieldset('stock', array('legend'=> $hlp->__('Stock')));
        $fldStock->addField('stock_status', 'select', array(
            'label'     => $hlp->__('Status'),
            'name'      => 'stock_status',
            'values'    => array(
                0 => $hlp->__('Does not matter'), 
                2 => $hlp->__('In Stock'), 
             ),
        ));
        
        
        $data = Mage::registry('amlanding_page')->getData();
        $form->setValues($data); 
        
        $fldAttr = $form->addFieldset('attributes', array('legend'=> $hlp->__('Attributes')));
        $this->prepareAttributes($fldAttr, Mage::registry('amlanding_page'));
        
        return parent::_prepareForm();
    } 
    
    /**
     * Render required attributes
     * @param Varien_Data_Form_Element_Fieldset $fldAttr
     * @param Amasty_Xlanding_Model_Page $model
     */
    protected function prepareAttributes($fldAttr, $model)
    {
    	$hlp = Mage::helper('amlanding');
    	
		/*
		 * Add Empty Fields user for new conditions
		 */    	
    	$fieldSet = $this->getForm()->addFieldset('attributestmp', array('legend'=> $hlp->__('Attribute Tmp')));
        $fieldSet->addField('attr_code[][]', 'select', array(
            'label'     => $hlp->__('Has attribute'),
            'name'      => 'attr_code[]',
            'values'    => $this->getAttributes(),
            'onchange'  => 'showOptions(this)',
        	'note'      => $hlp->__('If attribute is related to configurable products, please make sure that attribute is used in layered navigation')
            )
        );
        
        $fieldSet->addField('attr_value[][]', 'text', array(
                'label'     => $hlp->__('Attribute value is'),
                'name'      => 'attr_value[]',
		));
        
    	$array = $model->getAttributesAsArray();
		foreach ($array as $attributeCode => $attributeValue) {
    		if (empty($attributeCode)) {
    			continue;
    		}
    		
    		if (is_array($attributeValue)) {
    			
    			foreach ($attributeValue as $i => $value) {
		    		/*
		    		 * Add Attribute Names
		    		 */
    				$elementCode = $attributeCode . '-' . $value . '-' . $i;
    				
		    		$fldAttr->addField('attr_code[' . $elementCode . ']', 'select', 
		    			array(
							'label'     => $hlp->__('Has attribute'),
					        'name'      => 'attr_code[' . $elementCode . ']',
					        'values'    => $this->getAttributes(),
					        'onchange'  => 'showOptions(this)',
					   		'value' 	=> $attributeCode,
		    				'note'      => $hlp->__('If attribute is related to configurable products, please make sure that attribute is used in layered navigation'),
			    		    'after_element_html' => '<a href="#" onclick="landingRemove(this);return false;" title="' . $hlp->__('Remove') . '">' . $hlp->__('X') . '</a>'
						)
				    );
				        
					/*
				     * Add Attribute Options
				     */
					$attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);
					
			        if ('select' === $attribute->getFrontendInput() || 'multiselect' === $attribute->getFrontendInput()) {
						$options = $attribute->getFrontend()->getSelectOptions();
			            $fldAttr->addField('attr_value[' . $elementCode . ']', 'select', array(
							'label'     => $hlp->__('Attribute value is'),
			                'name'      => 'attr_value[' . $elementCode . ']',
			                'values'    => $options,
			                'value'    => $value,
						));
		            } else {
		                $fldAttr->addField('attr_value[' . $elementCode . ']', 'text', array(
							'label'     => $hlp->__('Attribute value is'),
		                	'name'      => 'attr_value[' . $elementCode . ']',
		                	'value'    => $value,
		                ));
		            }
    			}
    		}
		}
    }
    
    
    protected function getAttributes()
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId())
        ;
            
        $options = array(''=>'');
		foreach ($collection as $attribute){
		    $label = $attribute->getFrontendLabel();
			if ($label){ // skip system attributes
			    $options[$attribute->getAttributeCode()] = $label;
			}
		}
		asort($options);
        
		return $options;
    }

    /**
     * Genarates tree of all categories
     *
     * @return array sorted list category_id=>title
     */
    protected function getTree()
    {
        $rootId = Mage::app()->getStore(0)->getRootCategoryId();         
        $tree = array();
        
        $collection = Mage::getModel('catalog/category')
            ->getCollection()->addNameToResult();
        
        $pos = array();
        foreach ($collection as $cat){
            $path = explode('/', $cat->getPath());
            if ((!$rootId || in_array($rootId, $path)) && $cat->getLevel()){
                $tree[$cat->getId()] = array(
                    'label' => str_repeat('--', $cat->getLevel()) . $cat->getName(), 
                    'value' => $cat->getId(),
                    'path'  => $path,
                );
            }
            $pos[$cat->getId()] = $cat->getPosition();
        }
        
        foreach ($tree as $catId => $cat){
            $order = array();
            foreach ($cat['path'] as $id){
            	if (isset($pos[$id])){
                	$order[] = $pos[$id];
            	}
            }
            $tree[$catId]['order'] = $order;
        }
        
        usort($tree, array($this, 'compare'));
        array_unshift($tree, array('value'=>'', 'label'=>''));
        
        return $tree;
    }
    
    /**
     * Compares category data. Must be public as used as a callback value
     *
     * @param array $a
     * @param array $b
     * @return int 0, 1 , or -1
     */
    public function compare($a, $b)
    {
        foreach ($a['path'] as $i => $id){
            if (!isset($b['path'][$i])){ 
                // B path is shorther then A, and values before were equal
                return 1;
            }
            if ($id != $b['path'][$i]){
                // compare category positions at the same level
                $p = isset($a['order'][$i]) ? $a['order'][$i] : 0;
                $p2 = isset($b['order'][$i]) ? $b['order'][$i] : 0;
                return ($p < $p2) ? -1 : 1;
            }
        }
        // B path is longer or equal then A, and values before were equal
        return ($a['value'] == $b['value']) ? 0 : -1;
    }           
       
    
}