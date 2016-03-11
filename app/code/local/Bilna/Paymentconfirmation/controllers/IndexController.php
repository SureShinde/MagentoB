<?php
DEFINE('SUCCESS','Terima kasih telah melakukan pemesanan di Bilna. Proses konfirmasi memakan waktu paling lama 2x24 jam (hari kerja).');
DEFINE('INV_EMAIL','Maaf, e-Mail yang anda masukkan salah');
DEFINE('INV_ORDER','Maaf, Order ID yang anda masukkan salah');
DEFINE('DUPLICATE_ORDER','Order ID sudah pernah Melakukan Konfirmasi Sebelumnya');


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
    
    public function ProcessAction(){
        try{
            $post = $this->getRequest()->getPost('paymentconfirmation');
            $this->loadLayout();  
            if(isset($post)){
                $paymentModel = Mage::getModel('Paymentconfirmation/payment'); 
                $isValidOrderID = $paymentModel->isValidOrder($post['order_number']);
                list($post['month'],$post['day'],$post['year']) = explode("/",$post['transfer_date']);
                $post['transfer_date'] = sprintf('%s-%s-%s',$post['year'],$post['month'],$post['day']);
                unset($post['year'],$post['month'],$post['day']);
                if(trim($isValidOrderID->entity_id) != ''){
                    if($isValidOrderID->customer_email == $post['email']){
                        $x = $paymentModel
                            ->getCollection()
                            ->addFieldToFilter('order_id',array('equal' => $post['order_number']))
                            ->setCurPage(1)
                            ->setPageSize(1);
                        if(count($x) < 1){
                            $post['entity_id'] = $isValidOrderID->entity_id;

                            $param = $post;
                            $param['bank_from'] = isset($param['other_from']) ? $param['other_from'] : $param['bank_from'];
                            $fields = array("order_id" => !empty($param['order_number']) ? $param['order_number'] : "NULL",
                                            "email" => !empty($param['email']) ? $param['email'] : "NULL",
                                            "nominal" => !empty($param['nominal']) ? $param['nominal'] : "NULL",
                                            "dest_bank" => !empty($param['bank_to']) ? $param['bank_to'] : "NULL",
                                            "transfer_date" => !empty($param['transfer_date']) ? $param['transfer_date'] : "NULL",
                                            "source_bank" => !empty($param['bank_from']) ? $param['bank_from'] : "NULL",
                                            "source_acc_number" => !empty($param['acc_from']) ? $param['acc_from'] : "NULL",
                                            "source_acc_name" => !empty($param['name_from']) ? $param['name_from'] : "NULL",
                                            "comment" => !empty($param['comment']) ? $param['comment'] : "NULL",
                                            "entity_id" => !empty($param['entity_id']) ? (int)$param['entity_id'] : "0"
                                        );
                            //print "<PRE>";print_r($fields);exit;
                            //$paymentModel->insertPayment($fields);
                            $paymentModel->setData($fields);
                            $paymentModel->save();

                            $this->getLayout()->getBlock('head')->setTitle($this->__('Payment Confirmation Thank You'));
                            $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',SUCCESS);
                        }
                        else{
                            $this->getLayout()->getBlock('head')->setTitle($this->__('Failed Payment Confirmation'));
                            $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',DUPLICATE_ORDER);
                        }
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

