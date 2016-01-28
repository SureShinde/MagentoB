<?php
class Bilna_Highlightproduct_AddProductController extends Mage_Core_Controller_Front_Action {
	public function IndexAction() {
		$data = $this->getRequest()->getPost('data');
		$nowDate = date("Y-m-d H:i:s");
		
		try{
			$response = array();
			$response['status'] = true;
			
			if(!isset($data['sku']) || is_null($data['sku'])){
				$response['status'] 		= false;
				$response['errormessage']	= "Invalid JSON response";
			}
			
	        if(!isset($data["allow_multiple"]) || $data["allow_multiple"]==0){
	       		$productOnCart = false;
	
				//Check if product already exist on the cart
		        $quote = Mage::getSingleton('checkout/session')->getQuote();
		        foreach ($quote->getAllItems() as $item) {
		        	if($item->getSku() == $data["sku"]){
		        		$productOnCart	= true;
		        	}
		        }
		        
	        	if($productOnCart===true){
					$response['status'] 		= false;
		        	$response['errormessage']	= "Product already exist in cart";
	        	}
	        }
	        
	        if(!isset($data["quantity"])) $data["quantity"] = 1;
	
	        if($response['status']===true){
		        $productModel = Mage::getModel('catalog/product');
		        $productId = $productModel->getIdBySku($data['sku']);
		        
		        if(!is_null($productId)){
		        	$params = array(
		        			'product' => $productId,
		        			'qty' => $data["quantity"],
		        	);
		        	$cart = Mage::getSingleton('checkout/cart');
		        	$product = new Mage_Catalog_Model_Product();
		        	$product->load($productId);
		        	$cart->addProduct($product, $params);
		        	$cart->save();
		        	Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
		        	$message = $this->__('Gift product: %s was successfully added to your shopping cart.', $product->getName());
		        	Mage::getSingleton('checkout/session')->addSuccess($message);
		        }else{
		        	$response['status'] 		= false;
		        	$response['errormessage']	= "Product doesn't exist";
		        }
	        }
		}catch(Exception $e){
			$response['status'] = false;
			$response['errormessage']	= "Invalid JSON response";
		}

		echo json_encode($response);
		exit;
	}
}