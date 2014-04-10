<?php
class Bilna_AjaxRequest_DataController extends Mage_Core_Controller_Front_Action {
    public function RetrievecartAction() {
    	$response	= array();
        $data		= $this->getRequest()->getPost('data');
	        
	    $response['status'] = true;

	    $cart = Mage::getSingleton('checkout/session')->getQuote();
        foreach ($cart->getAllItems() as $item) {
			$product = array();
			$product["identifier"] = $item->getSku();
			$product["amount"] = (int) $item->getPrice();
			$product["currency"] = "IDR";
			$product["quantity"] = $item->getQty();
		    $response['data']['products'][] = $product;
        }
        
        echo json_encode($response);
        exit;
    }
    
	public function RetrieveconfirmAction() {
    	$response	= array();
        $data		= $this->getRequest()->getPost('data');
        $orderId	= $data["orderId"];
        
        if (!isset ($orderId) || empty ($orderId)) {
            $response['status'] = false;
            $response['message'] = 'OrderId is not valid';
        }else{
	        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
	        $response['status'] = true;

	        foreach ($order->getAllItems() as $item) {
				$product = array();
				$product["identifier"] = $item->getSku();
				$product["amount"] = (int) $item->getPrice();
				$product["currency"] = "IDR";
				$product["quantity"] = $item->getQtyOrdered();
			    $response['data']['products'][] = $product;
	        }
	
		    $response['data']['transaction'] = (int)$order->getIncrementId();
		    $response['data']['amount'] = (int)$order->getGrandTotal();
		    $response['data']['currency'] = "IDR";
        }
		
        echo json_encode($response);
        exit;
    }
	
	public function RetrieveproductAction() {
    	$response	= array();
        $data		= $this->getRequest()->getPost('data');
        $productId	= $data["productId"];
        
        if (!isset ($productId) || empty ($productId)) {
	   		$response['status'] = false;
        }else{
        	$product = Mage::getModel('catalog/product');
        	$product->unsetData()->load($data["productId"]);
        	$response['status'] = true;
        	
        	$response['data']['identifier'] = $product->getSku();
        	$response['data']['category'] = '';
        	$response['data']['fn'] = $product->getName();
        	$response['data']['description'] = '';
        	$response['data']['brand'] = $product->getAttributeText('brand');
        	$response['data']['price'] = (int)$product->getFinalPrice();
        	$response['data']['amount'] = (int)$product->getPrice();
        	$response['data']['currency'] = "IDR";
        	$response['data']['url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."baby/".$product->getUrlPath();
//         	$response['data']['photo'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$product->getSmallImage();
        	$response['data']['photo'] = $product->getMediaConfig()->getMediaUrl($product->getData('image'));

        	$response['data']['category'] = array();
        	$categories = $product->getCategoryIds();
        	foreach ($categories as $category_id) {
        		$category = Mage::getModel('catalog/category')->load($category_id) ;
        		if(strtolower(trim($category->getName())) !== "default category"){
        			$response['data']['category'][] = $category->getName();
        			
        			if($response['data']['description'] == ''){
        				$response['data']['description'] = $category->getName();
        			}
        		}
        	}

        	if	(($product->getStatus()==1) ||
        			($product->getIsInStock()==1) ||
        			($product->getStockItem()->getManageStock()==1 && $product->getStockItem()->getQty()>1  && $product->getStockItem()->getBackorders()==0) ||
        			($product->getStockItem()->getBackorders()==1)){
//         	if	(($product->getStatus()==2) || 
//         		($product->getIsInStock()==0) || 
//         		($product->getStockItem()->getManageStock()==1 && $product->getStockItem()->getQty()<2  && $product->getStockItem()->getBackorders()==0)){
        		$response['data']['valid'] = 0;
        	}else{
        		$response['data']['valid'] = 1;
        	}
        }
		
        echo json_encode($response);
        exit;
    }
	
	public function RetrievecategoryAction() {
    	$response	= array();
        $data		= $this->getRequest()->getPost('data');
        $categoryId	= $data["categoryId"];
        
        if (!isset ($categoryId) || empty ($categoryId)) {
	   		$response['status'] = false;
        }else{
        	$category = Mage::getModel('catalog/category');
        	$category->unsetData()->load($data["categoryId"]);
        	$response['status'] = true;
        	
        	$response['data']['category'][] = $category->getName();
        	
        	if(!is_null($category->getParentId())){
	        	$categoryParent = Mage::getModel('catalog/category');
	        	$categoryParent->unsetData()->load($category->getParentId());
        		if(strtolower(trim($categoryParent->getName())) !== "default category"){
	        		$response['data']['category'][] = $categoryParent->getName();
	        	}
        	}
        }
		
        echo json_encode($response);
        exit;
    }
}