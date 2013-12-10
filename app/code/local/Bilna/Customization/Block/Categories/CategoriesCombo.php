<?php

class Bilna_Customization_Block_Categories_CategoriesCombo extends Mage_Core_Block_Template
{
	
    function categoriesCombo() {
        $category = Mage::getModel('catalog/category');
        $category->load(Mage::app()->getStore()->getRootCategoryId());
        $children_string = $category->getChildrenCategories();
        echo "children_string:".$children_string;
        return $children_string;
    }
    
}

?>