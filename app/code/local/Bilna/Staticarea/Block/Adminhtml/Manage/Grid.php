<?php

class Bilna_Staticarea_Block_Adminhtml_Manage_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId("manageGrid");
		$this->setDefaultSort("id");
		$this->setDefaultDir("ASC");
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
			$collection = Mage::getModel("staticarea/manage")->getCollection();
			$this->setCollection($collection);
			return parent::_prepareCollection();
	}
	protected function _prepareColumns()
	{
		$this->addColumn("id", array(
			"header" => Mage::helper("staticarea")->__("ID"),
			"align" =>"right",
			"width" => "50px",
		    "type" => "number",
			"index" => "id",
		));
        
        $this->addColumn("area_name", array(
			"header" => Mage::helper("staticarea")->__("Name"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "text",
			"index" => "area_name",
		));

		$this->addColumn("block_id", array(
			"header" => Mage::helper("staticarea")->__("Block ID"),
			"align" =>"right",
			"width" => "50px",
		    "type" => "text",
			"index" => "block_id",
		));

		$this->addColumn("status", array(
			"header" => Mage::helper("staticarea")->__("Status"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "text",
			"index" => "status",
		));

		$this->addColumn("type", array(
			"header" => Mage::helper("staticarea")->__("Type"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "text",
			"index" => "type",
		));

		$this->addColumn("storeview", array(
			"header" => Mage::helper("staticarea")->__("Store View"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "text",
			"index" => "storeview",
		));

		$this->addColumn("area_createddate", array(
			"header" => Mage::helper("staticarea")->__("Created Date"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "date",
			"index" => "area_createddate",
		));

		$this->addColumn("area_updatedate", array(
			"header" => Mage::helper("staticarea")->__("Update Date"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "date",
			"index" => "area_updatedate",
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}