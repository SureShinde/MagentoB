<?php
DEFINE('SUCCESS_TITLE','Terima Kasih!');
DEFINE('SUCCESS','Konfirmasi pembayaran anda telah kami terima. kami akan melakukan pengecekan. Mohon simpan bukti pembayaran anda, jika diperlukan tim kami akan menghubungi anda untuk pengecekan lebih lanjut, jika anda melakukan kesalahan saat memasukkan data konfirmasi, silahkan hubungi <a href="mailto:cs@bilna.com">cs@bilna.com</a><br><br>Mohon tunggu maksimal 24 jam, Anda akan menerima e-mail status pesanan dari kami.');
DEFINE('INV_TITLE','Ulangi Proses Konfirmasi');
DEFINE('INV_EMAIL','Alamat email atau nomor pesanan yang dimasukkan salah. Mohon ulangi proses konfirmasi dan pastikan informasi sesuai dengan data pesanan.');
DEFINE('INV_ORDER','Alamat email atau nomor pesanan yang dimasukkan salah. Mohon ulangi proses konfirmasi dan pastikan informasi sesuai dengan data pesanan.');
DEFINE('DUPLICATE_ORDER_TITLE','Konfirmasi Telah Berhasil');
DEFINE('DUPLICATE_ORDER','Pesanan telah berhasil dikonfirmasi sebelumnya. Apabila Anda ingin mengubah informasi yang telah diterima, silakan menghubungi <a href="mailto:cs@bilna.com">cs@bilna.com</a>');


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
                    $post['email'] = strtolower($post['email']);
                    if(strtolower($isValidOrderID->customer_email) == $post['email']){
                        $paymentData = $paymentModel
                            ->getCollection()
                            ->addFieldToFilter('order_id',array('equal' => $post['order_number']))
                            ->setCurPage(1)
                            ->setPageSize(1);
                        if(count($paymentData) < 1){
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
                                            "entity_id" => !empty($param['entity_id']) ? (int)$param['entity_id'] : "0",
                                            "created_at" => Mage::getModel('core/date')->date('Y-m-d H:i:s')
                                        );
                            $paymentModel->setData($fields);
                            $paymentModel->save();

                            $this->getLayout()->getBlock('head')->setTitle($this->__('Payment Confirmation Thank You'));
                            $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',SUCCESS);
                            $this->getLayout()->getBlock('paymentconfirm_process')->setData('title',SUCCESS_TITLE);
                            $this->getLayout()->getBlock('paymentconfirm_process')->setData('status',1);
                        }
                        else{
                            $this->getLayout()->getBlock('head')->setTitle($this->__('Failed Payment Confirmation'));
                            $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',DUPLICATE_ORDER);
                            $this->getLayout()->getBlock('paymentconfirm_process')->setData('title',DUPLICATE_ORDER_TITLE);
                            $this->getLayout()->getBlock('paymentconfirm_process')->setData('status',1);
                        }
                    }
                    else{
                        $this->getLayout()->getBlock('head')->setTitle($this->__('Failed Payment Confirmation'));
                        $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',INV_EMAIL);
                        $this->getLayout()->getBlock('paymentconfirm_process')->setData('title',INV_TITLE);
                        $this->getLayout()->getBlock('paymentconfirm_process')->setData('status',0);
                    }
                }
                else{
                    $this->getLayout()->getBlock('head')->setTitle($this->__('Failed Payment Confirmation'));
                    $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',INV_ORDER);
                    $this->getLayout()->getBlock('paymentconfirm_process')->setData('title',INV_TITLE);
                    $this->getLayout()->getBlock('paymentconfirm_process')->setData('status',0);
                }
            }
            else{
                $this->getLayout()->getBlock('head')->setTitle($this->__('Failed Payment Confirmation'));
                $this->getLayout()->getBlock('paymentconfirm_process')->setData('message',INV_ORDER);
                $this->getLayout()->getBlock('paymentconfirm_process')->setData('title',INV_TITLE);
                $this->getLayout()->getBlock('paymentconfirm_process')->setData('status',0);
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
    		Mage::logException($e);
    	}
         
    }
}

