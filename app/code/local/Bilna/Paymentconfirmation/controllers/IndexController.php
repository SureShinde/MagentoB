<?php
DEFINE('SUCCESS','Terima kasih telah melakukan pemesanan di Bilna. Proses konfirmasi memakan waktu paling lama 2x24 jam (hari kerja).');
DEFINE('INV_EMAIL','Maaf, e-Mail yang anda masukkan salah');
DEFINE('INV_ORDER','Maaf, Order ID yang anda masukkan salah');


class Bilna_Paymentconfirmation_IndexController extends Mage_Core_Controller_Front_Action {
    public function IndexAction() {
        $this->loadLayout();  
//        print_r($this->getLayout()->getUpdate()->getHandles());exit;
        $this->getLayout()->getBlock('head')->setTitle($this->__('Payment Confirmation'));
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array (
            'label' => $this->__('Home Page'),
            'title' => $this->__('Home Page'),
            'link' => Mage::getBaseUrl()
        ));
        $breadcrumbs->addCrumb('paymentconfirmation', array (
            'label' => $this->__('Konfirmasi Pembayaran'),
            'title' => $this->__('Konfirmasi Pembayaran')
        ));
        $this->renderLayout();
    }
    
    public function validateOrderAction(){
        try{
            $post = $_POST;
            if(isset($post['order_number'])){
                $paymentModel = Mage::getModel('Paymentconfirmation/payment'); 
                $isValidOrderID = $paymentModel->isValidOrder($post['order_number']);
                if(isset($isValidOrderID[0]['entity_id'])){
                    if($isValidOrderID[0]['customer_email'] == $post['email']){
                        echo json_encode(array('status' => true, 'message' => 'Success'));
                    }
                    else{
                        echo json_encode(array('status' => false, 'message' => 'Invalid Customer e-Mail'));
                    }
                }
                else{
                    echo json_encode(array('status' => false, 'message' => 'Order ID Not Found'));
                }
            }
            else{
                echo json_encode(array('status' => false, 'message' => 'Order ID Not Found'));
            }
        } catch (Exception $ex) {
            Zend_Debug::dump($e);die;
        }
    }
    
    public function ProcessAction(){
        try{
            $post = $this->getRequest()->getPost('paymentconfirmation');
            $this->loadLayout();  
            if(isset($post)){
                $paymentModel = Mage::getModel('Paymentconfirmation/payment'); 
                $isValidOrderID = $paymentModel->isValidOrder($post['order_number']);
                $post['transfer_date'] = sprintf('%s-%s-%s',$post['year'],$post['month'],$post['day']);
                unset($post['year'],$post['month'],$post['day']);
                if(trim($isValidOrderID->entity_id) != ''){
                    if($isValidOrderID->customer_email == $post['email']){
                        $post['entity_id'] = $isValidOrderID->entity_id;
                        $collections = $paymentModel->insertPayment($post);//models var on config.xml
                        $this->getLayout()->getBlock('head')->setTitle($this->__('Payment Confirmation Thank You'));
                        $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',SUCCESS);
                    }
                    else{
                        $this->getLayout()->getBlock('head')->setTitle($this->__('Failed Payment Confirmation'));
                        $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',INV_EMAIL);
                    }
                }
                else{
                    $this->getLayout()->getBlock('head')->setTitle($this->__('Failed Payment Confirmation'));
                    $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',INV_ORDER);
                }
            }
            else{
                $this->getLayout()->getBlock('head')->setTitle($this->__('Failed Payment Confirmation'));
                $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',INV_ORDER);
            }
            
            
            $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb('home', array (
                'label' => $this->__('Home Page'),
                'title' => $this->__('Home Page'),
                'link' => Mage::getBaseUrl()
            ));
            $breadcrumbs->addCrumb('paymentconfirmation', array (
                'label' => $this->__('Konfirmasi Pembayaran'),
                'title' => $this->__('Konfirmasi Pembayaran')
            ));
            $this->renderLayout();
            
        }catch(Exception $e){
    		Zend_Debug::dump($e);die;
    	}
         
    }
}

