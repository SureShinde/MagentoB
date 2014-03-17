<?php

/**
 * Cms block content block
 */
class Bilna_Formbuilder_Block_Block extends Mage_Core_Block_Template
{
    public function __construct()
    {
echo 5;die;
        parent::__construct();
        $this->setTemplate('formbuilder/form/default.phtml');
    }

    /**
     * Prepare Content HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
	$block = NULL;
        $blockId = $this->getBlockId();
        $html = '';
        if ($blockId) {
		$block = Mage::getModel('bilna_formbuilder/formbuilder')->getCollection();
		$block->getSelect()->join('bilna_formbuilder_input', 'main_table.id = bilna_formbuilder_input.form_id');
                //->setStoreId(Mage::app()->getStore()->getId())
                //->load($blockId);
		$this->setCollection($block);
		//$block->printLogQuery(true);
		//Zend_Debug::Dump($block); die;
                //if ($block->getStatus()) {
                /* @var $helper Mage_Cms_Helper_Data */
                $helper = Mage::helper('bilna_formbuilder');
		//Zend_Debug::Dump($block); die;
		//$processor = $helper->getBlockTemplateProcessor();
                //$html = 'input';
	//foreach($block as $row) {
	 	//echo $row->getTitle().":";
		//echo "<br/>";
	//}
                //$this->addModelTags($block);
		//}
        }
	$this->renderLayout();
        return $block;
    }
}
