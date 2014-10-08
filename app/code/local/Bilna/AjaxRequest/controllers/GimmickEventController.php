<?php
class Bilna_AjaxRequest_GimmickEventController extends Mage_Core_Controller_Front_Action {
	public function CartCheckoutAction() {
		$data = $this->getRequest()->getPost('data');
		$nowDate = date("Y-m-d H:i:s");
		
		$response = array();
		$response['status'] = false;
		$response['data'] = array();

		/* START GET ONE ACTIVE GIMMICK EVENT */
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "select * from bilna_gimmick_event
        			WHERE	start_date <= '".$nowDate."' and
        					end_date >= '".$nowDate."' and
        					status = '1'
        			ORDER BY priority DESC limit 1"; 
		$result = $read->fetchAll($sql);

        if(empty($result)){
			echo json_encode($response);
			exit;
        }
        
        $response["promo"]["id"]				= $result[0]['id'];
        $response["promo"]["name"]				= $result[0]['name'];
        $response["promo"]["banner"]			= $result[0]['banner'];
        $response["promo"]["callback_url"]		= $result[0]['callback_url'];
        $response["promo"]["tos"]				= $result[0]['tos'];
        $response["promo"]["allow_repeatable"]	= $result[0]['allow_repeatable'];
        $response["promo"]["start_date"]		= $result[0]['start_date'];
        $response["promo"]["end_date"]			= $result[0]['end_date'];
		/* END GET ONE ACTIVE GIMMICK EVENT */

		$orderId = $data["orderId"];

		/* START GET VALID SKU FROM ACTIVE GIMMICK EVENT */
        $validSkuPromo = array();
        
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "select sku from bilna_gimmick_event_sku
        			WHERE	event_id = '".$response["promo"]["id"]."'";
		$result = $read->fetchAll($sql);

		if(empty($result)){
			echo json_encode($response);
			exit;
		}
		
		foreach($result as $product){
			$validSkuPromo[] = strtoupper($product["sku"]);
		}
		/* END GET VALID SKU FROM ACTIVE GIMMICK EVENT */

		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

		if(strtotime($response["promo"]["start_date"]) > strtotime($order->getCreatedAt())){
			echo json_encode($response);
			exit;
		}
		
        /* START CHECK USER HISTORY */
		if($response["promo"]["allow_repeatable"] == "0"){
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$sql = "select id from bilna_gimmick_event_applicant
	        			WHERE	event_id = '".$response["promo"]["id"]."' and 
	        					user_email = '".$order->getCustomerEmail()."'";
			$result = $read->fetchAll($sql);
		}else{
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$sql = "select id from bilna_gimmick_event_applicant
	        			WHERE	event_id = '".$response["promo"]["id"]."' and 
	        					order_increment_id = '".$order->getIncrementId()."'";
			$result = $read->fetchAll($sql);
		}

		if(!empty($result)){
			echo json_encode($response);
			exit;
		}
        /* END CHECK USER HISTORY */

		/* START CHECK VALID SKU ON EXISTING ORDER */
		$items = $order->getAllItems();
		$products = array();
		foreach ($items as $item) {
        	if(in_array(strtoupper($item->getSku()), $validSkuPromo)){
        		$response['status'] = true;
        		$products[] = $item->getSku();
        	}
		}
        /* END CHECK VALID SKU ON EXISTING ORDER */

		/* START GENERATING RESPONSE DATA */
		if($response['status'] == true){
			$shipping = $order->getShippingAddress()->getData();

			$response['data']['transaction']['id'] = $order->getIncrementId();
			$response['data']['user']['id'] = $shipping["customer_id"];
			$response['data']['user']['firstname'] = $order->getCustomerFirstname();
			$response['data']['user']['lastname'] = $order->getCustomerLastname();
			$response['data']['user']['email'] = $order->getCustomerEmail();
			$response['data']['user']['telephone'] = $shipping["telephone"];
			$response['data']['user']['address']['firstname'] = $shipping["firstname"];
			$response['data']['user']['address']['lastname'] = $shipping["lastname"];
			$response['data']['user']['address']['type'] = $shipping["address_type"];
			$response['data']['user']['address']['street'] = $shipping["street"];
			$response['data']['user']['address']['city'] = $shipping["city"];
			$response['data']['user']['address']['region'] = $shipping["region"];
			$response['data']['user']['address']['country'] = $shipping["country_id"];
			$response['data']['user']['address']['postcode'] = $shipping["postcode"];
			$response['data']['products'] = $products;
		}
		/* END GENERATING RESPONSE DATA */

		echo json_encode($response);
		exit;
	}
	
	public function AcceptAction() {
		$data = $this->getRequest()->getPost('data');
		$nowDate = date("Y-m-d H:i:s");
		
		$response = array();
		$response['status'] = false;
		$response['data'] = array();

		/* START GET ONE ACTIVE GIMMICK EVENT */
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "select * from bilna_gimmick_event
        			WHERE	start_date <= '".$nowDate."' and
        					end_date >= '".$nowDate."' and
        					status = '1'
        			ORDER BY priority DESC limit 1";
		$result = $read->fetchAll($sql);

        if(empty($result)){
			echo json_encode($response);
			exit;
        }
        
        $response["promo"]["id"]			= $result[0]['id'];
        $response["promo"]["name"]			= $result[0]['name'];
        $response["promo"]["banner"]		= $result[0]['banner'];
        $response["promo"]["callback_url"]	= $result[0]['callback_url'];
        $response["promo"]["tos"]			= $result[0]['tos'];
		/* END GET ONE ACTIVE GIMMICK EVENT */

		$orderId = $data["orderId"];

		/* START GET VALID SKU FROM ACTIVE GIMMICK EVENT */
        $validSkuPromo = array();
        
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "select sku from bilna_gimmick_event_sku
        			WHERE	event_id = '".$response["promo"]["id"]."'";
		$result = $read->fetchAll($sql);

		if(empty($result)){
			echo json_encode($response);
			exit;
		}
		
		foreach($result as $product){
			$validSkuPromo[] = strtoupper($product["sku"]);
		}
		/* END GET VALID SKU FROM ACTIVE GIMMICK EVENT */

        /* START CHECK VALID SKU ON EXISTING ORDER */
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		$items = $order->getAllItems();
		$products = array();
		foreach ($items as $item) {
        	if(in_array(strtoupper($item->getSku()), $validSkuPromo)){
        		$response['status'] = true;
        		$products[] = $item->getSku();
        	}
		}
        /* END CHECK VALID SKU ON EXISTING ORDER */

		/* START GENERATING RESPONSE DATA */
		if($response['status'] == true){
			$shipping = $order->getShippingAddress()->getData();

			$response['data']['transaction']['id'] = $order->getIncrementId();
			$response['data']['user']['id'] = $shipping["customer_id"];
			$response['data']['user']['firstname'] = $order->getCustomerFirstname();
			$response['data']['user']['lastname'] = $order->getCustomerLastname();
			$response['data']['user']['email'] = $order->getCustomerEmail();
			$response['data']['user']['telephone'] = $shipping["telephone"];
			$response['data']['user']['address']['firstname'] = $shipping["firstname"];
			$response['data']['user']['address']['lastname'] = $shipping["lastname"];
			$response['data']['user']['address']['type'] = $shipping["address_type"];
			$response['data']['user']['address']['street'] = $shipping["street"];
			$response['data']['user']['address']['city'] = $shipping["city"];
			$response['data']['user']['address']['region'] = $shipping["region"];
			$response['data']['user']['address']['country'] = $shipping["country_id"];
			$response['data']['user']['address']['postcode'] = $shipping["postcode"];
			$response['data']['products'] = $products;

			$write = Mage::getSingleton("core/resource")->getConnection("core_write");
			$query = "insert into bilna_gimmick_event_applicant (id, event_id, order_increment_id, order_date, user_id, user_email, products, created_at, updated_at) 
							values (NULL, :event_id, :order_increment_id, :order_date, :user_id, :user_email, :products, NOW(), NOW())";
			$binds = array(
					'event_id'				=> $response["promo"]["id"],
					'order_increment_id'	=> $response['data']['transaction']['id'],
					'order_date'			=> $order->getCreatedAt(),
					'user_id'				=> $response['data']['user']['id'],
					'user_email'			=> $response['data']['user']['email'],
					'products'				=> json_encode($response['data']['products']),
			);
			
			$write->query($query, $binds);
		}
		/* END GENERATING RESPONSE DATA */

		echo json_encode($response);
		exit;
	}
}