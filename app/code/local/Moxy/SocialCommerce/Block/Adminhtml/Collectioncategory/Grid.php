<?php

class Moxy_SocialCommerce_Block_Adminhtml_Collectioncategory_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("collectioncategoryGrid");
        $this->setDefaultSort("category_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("socialcommerce/collectioncategory")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("category_id", array(
            "header" => Mage::helper("socialcommerce")->__("ID"),
            "align" =>"right",
            "width" => "50px",
            "type" => "number",
            "index" => "category_id",
        ));

        $this->addColumn("name", array(
            "header" => Mage::helper("socialcommerce")->__("Collection name"),
            "index" => "name",
        ));

        $this->addColumn("show_in_coll_page", array(
            "header" => Mage::helper("socialcommerce")->__("Show in collection page"),
            "index" => "show_in_coll_page",
        ));

        $this->addColumn("url", array(
            "header" => Mage::helper("socialcommerce")->__("Categories URL"),
            "index" => "url",
        ));

        $this->addColumn("sort_no", array(
            "header" => Mage::helper("socialcommerce")->__("Sort order"),
            "index" => "sort_no",
        ));

        $this->addColumn("is_active", array(
            "header" => Mage::helper("socialcommerce")->__("Is active"),
            "index" => "is_active",
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('category_id');
        $this->getMassactionBlock()->setFormFieldName('category_ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('remove_collectioncategory', array(
            'label'=> Mage::helper('socialcommerce')->__('Remove Collectioncategory'),
            'url'  => $this->getUrl('*/adminhtml_collectioncategory/massRemove'),
            'confirm' => Mage::helper('socialcommerce')->__('Are you sure?')
        ));
        return $this;
    }
}
