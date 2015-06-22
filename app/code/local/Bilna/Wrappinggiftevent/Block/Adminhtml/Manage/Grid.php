<?php

class Bilna_Wrappinggiftevent_Block_Adminhtml_Manage_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
			$collection = Mage::getModel("wrappinggiftevent/manage")->getCollection();
			$this->setCollection($collection);
			return parent::_prepareCollection();
	}
	protected function _prepareColumns()
	{
		$this->addColumn("id", array(
			"header" => Mage::helper("wrappinggiftevent")->__("ID"),
			"align" =>"right",
			"width" => "50px",
		    "type" => "number",
			"index" => "id",
		));
        
        $this->addColumn("wrapping_name", array(
			"header" => Mage::helper("wrappinggiftevent")->__("Name"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "text",
			"index" => "wrapping_name",
		));

		$this->addColumn("wrapping_price", array(
			"header" => Mage::helper("wrappinggiftevent")->__("Price"),
			"align" =>"right",
			"width" => "50px",
		    "type" => "number",
			"index" => "wrapping_price",
		));

		$this->addColumn("wrapping_desc", array(
			"header" => Mage::helper("wrappinggiftevent")->__("Description"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "text",
			"index" => "wrapping_desc",
		));

		$this->addColumn("wrapping_startdate", array(
			"header" => Mage::helper("wrappinggiftevent")->__("Start Date"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "date",
			"index" => "wrapping_startdate",
		));

		$this->addColumn("wrapping_enddate", array(
			"header" => Mage::helper("wrappinggiftevent")->__("End Date"),
			"align" =>"left",
			"width" => "50px",
		    "type" => "date",
			"index" => "wrapping_enddate",
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