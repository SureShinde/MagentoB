<?php

class Moxy_SocialCommerce_Block_Adminhtml_Customercollection_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId("customercollectionGrid");
        $this->setDefaultSort("wishlist_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("socialcommerce/customercollection")->getCollection()->addFieldToFilter("name", array("notnull" => true));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("wishlist_id", array(
            "header" => Mage::helper("socialcommerce")->__("ID"),
            "align" =>"right",
            "width" => "50px",
            "type" => "number",
            "index" => "wishlist_id",
        ));

        $this->addColumn("name", array(
            "header" => Mage::helper("socialcommerce")->__("name"),
            "index" => "name",
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }
}

