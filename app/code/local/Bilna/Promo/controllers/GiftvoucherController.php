<?php
class Bilna_Promo_GiftvoucherController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();
		$this->renderLayout();
    }
    
    public function successAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }

    // Promo Tell Your Friend
    public function submitGiftvoucherAction() {
    	$data = array();
		$data["name"] = $this->getRequest()->getPost('name');
		$data["email"] = $this->getRequest()->getPost('email');
		$data["orderId"] = preg_replace("[^0-9]","", $this->getRequest()->getPost('orderId'));
		$data["address"] = $this->getRequest()->getPost('address');
		
		$data["submitDate"] = date("Y-m-d H:i:s");
		
		if(empty($data["name"])){
			Mage::getSingleton('core/session')->addError("Name can't be empty");
			$this->_redirectPage(Mage::getBaseUrl()."bilnapromo/giftvoucher");
		}

		$activePromo = $this->_checkActivePromo($data);

		if ($activePromo == false) {
			Mage::getSingleton('core/session')->addError("No active promo has been found");
			$this->_redirectPage(Mage::getBaseUrl()."bilnapromo/giftvoucher");
		}
		
		$data["promoId"] = $activePromo["id"];
		$checkEmailExist = $this->_checkEmailExist($data);
		if ($checkEmailExist == true) {
			Mage::getSingleton('core/session')->addError("Email is already exist with the orderId:".$data["orderId"]);
			$this->_redirectPage(Mage::getBaseUrl()."bilnapromo/giftvoucher");
		}
		
		$validOrder = $this->_checkOrderValidation($data, $activePromo);
		if ($validOrder == false) {
			$this->_redirectPage(Mage::getBaseUrl()."bilnapromo/giftvoucher");
		}
		
		$insertData = $this->_insertData($data);
		if ($insertData) {
			$this->_redirectPage(Mage::getBaseUrl()."bilnapromo/giftvoucher/success");
		}
		else {
			Mage::getSingleton('core/session')->addError("Failed to insert data to gift voucher");
			$this->_redirectPage(Mage::getBaseUrl()."bilnapromo/giftvoucher");
		}
    }

    // Check Active Promo
    private function _checkActivePromo($data) {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "select * from bilna_promo_giftvoucher 
        			WHERE	start_date <= '".$data["submitDate"]."' and 
        					end_date >= '".$data["submitDate"]."' and 
        					status = '1'
        			ORDER BY priority DESC limit 1";
        $result = $read->fetchAll($sql);

        if(empty($result)) return false;
        
        return $result[0];
    }
    // End Check Active Promo

    // Check Email Exist User
    private function _checkEmailExist($data) {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "select * from bilna_promo_giftvoucher_member 
        			WHERE 	email = '".$data["email"]."' and 
        					promo_id = '".$data["promoId"]."' and 
        					order_id = '".$data["orderId"]."'";
        $result = $read->fetchRow($sql);

        if(empty($result)) return false;

        return true;
    }
    // End Check Email Exist User

    // Check Valid Order
    private function _checkOrderValidation($data, $promo) {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "SELECT * FROM sales_flat_order 
        			WHERE 	increment_id = ".$data["orderId"];
        $result = $read->fetchRow($sql);

        if(empty($result)){
        	Mage::getSingleton('core/session')->addError("There is no Order with id:".$data["orderId"]);
        	return false;
        }

        if($result["customer_email"] !== $data["email"]){
        	Mage::getSingleton('core/session')->addError("Order with id:".$data["orderId"]." is not linked with email: ".$data["email"]);
        	return false;
        }
        if($result["base_grand_total"] < $promo["value"]){
        	Mage::getSingleton('core/session')->addError("Order with id:".$data["orderId"]." total must be bigger than: ".$promo["value"]);
        	return false;
        }

        return true;
    }
    // End Valid Order

    // Add Customer to Active Promo
    private function _insertData($data) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        //$signup_date = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
		$signup_date=date("Y-m-d H:i:s");
        $dataArr = array (
            $data["promoId"],
            $data["name"],
            $data["email"],
            $data["orderId"],
            $data["address"],
            $data["submitDate"],
        );

        $sql = "insert into bilna_promo_giftvoucher_member (promo_id, name, email, order_id, address, submit_date) values (?,?,?,?,?,?)";
        $query = $write->query($sql, $dataArr);

        if ($query)
            return true;
        else
            return false;
    }
    // End Add Customer to Active Promo

    // Redirect Page Function
    private function _redirectPage($url) {
        header("location:".$url);
        exit;
    }
    // End Redirect Page Function
}