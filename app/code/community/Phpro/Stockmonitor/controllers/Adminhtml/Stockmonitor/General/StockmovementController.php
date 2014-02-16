<?php

class Phpro_Stockmonitor_Adminhtml_Stockmonitor_General_StockmovementController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('stockmonitor_movement_menu')
			->_addBreadcrumb(Mage::helper('Catalog')->__('Stockmonitor'), Mage::helper('Catalog')->__('Stockmovement'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
 
	public function gridAction() {
		$this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.stockmonitor.overview');//->setProductsStockmonitor($this->getRequest()->getPost('products_stockmonitor', null));
		$this->renderLayout();
	}
    
    public function gridOnlyAction()
    {
		$this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.stockmonitor.stockmovement')
			->setProductsStockmonitor($this->getRequest()->getPost('products_stockmonitor', null));
        $this->renderLayout();
    }
  
    public function exportCsvAction()
    {
		$this->_initProduct();
		$product = Mage::registry('current_product');
        $fileName   = $product->getSku() . '_stock_movement.csv';
        $content    = $this->getLayout()->createBlock('stockmonitor/adminhtml_catalog_product_edit_tab_stockmovement')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
		$this->_initProduct();
		$product = Mage::registry('current_product');
        $fileName   = $product->getSku() . '_stock_movement.xml';
        $content    = $this->getLayout()->createBlock('mercator/adminhtml_catalog_product_edit_tab_stockmovement')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}