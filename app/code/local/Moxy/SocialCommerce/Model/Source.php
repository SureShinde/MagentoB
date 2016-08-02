<?php
class Moxy_SocialCommerce_Model_Source extends Moxy_SocialCommerce_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $categories = array('' => $this->__('Empty'));
        $categories_data = Mage::getSingleton("socialcommerce/collectioncategory")->getCollection()->getData();
        foreach ($categories_data as $category_data) {
            $categories[$category_data["category_id"]] = $this->__($category_data["name"]);
        }
        return $categories;
    }
}
