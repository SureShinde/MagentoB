<?php
class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Edit_Tab_Upload
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('bilna_pricevalidation/upload.phtml');
    }
    protected function _prepareLayout()
    {
        $this->setChild('uploader',
            $this->getLayout()->createBlock('adminhtml/media_uploader')
        );
        $this->getUploader()->getConfig()
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/upload'))
            ->setFileField('file')
            ->setFilters(array(
                'csv' => array(
                    'label' => Mage::helper('adminhtml')->__('CSV and Tab Separated files (.csv, .txt)'),
                    'files' => array('*.csv', '*.txt')
                ),
                'all'    => array(
                    'label' => Mage::helper('adminhtml')->__('All Files'),
                    'files' => array('*.*')
                )
            ));
        return Mage_Adminhtml_Block_Widget::_prepareLayout();
    }
}
