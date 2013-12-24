<?php
class Bilna_Newpage_Block_Adminhtml_Pagenew_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("pagenew_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("newpage")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("newpage")->__("Item Information"),
				"title" => Mage::helper("newpage")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("newpage/adminhtml_pagenew_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
