<?php
class Moxy_SocialCommerce_Block_Adminhtml_Collectioncover_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("collectioncover_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("socialcommerce")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("socialcommerce")->__("Item Information"),
				"title" => Mage::helper("socialcommerce")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("socialcommerce/adminhtml_collectioncover_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
