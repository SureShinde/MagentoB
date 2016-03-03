<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Bilna_Paymentconfirmation_Model_Payment extends Varien_Object{
    private $orderTable = 'sales_flat_order';
    private $confirmationTable = 'bilna_payment_confirmation';
    public function isValidOrder($orderNumber){
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $field = array("entity_id"=>"entity_id","grand_total"=>"grand_total",
                       "total_paid"=>"total_paid","status"=>"status",
                       "customer_email"=>"customer_email");
        $query = $db->select()->from($this->orderTable,$field)->where(sprintf("increment_id = %s",$orderNumber));
        return $db->fetchAll($query);
    }
    public function isPaymentExists($orderNumber){
	$db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $field = array("order_id"=>"order_id","email"=>"email","nominal"=>"nominal",
                       "dest_bank"=>"dest_bank","transfer_date"=>"transfer_date",
                       "source_bank"=>"source_bank","source_acc_number"=>"source_acc_number",
                       "source_acc_name"=>"source_acc_name","comment"=>"comment","entity_id"=>"entity_id");
        $query = $db->select()->from($this->confirmationTable,$field)->where(sprintf("order_id = %s",$orderNumber))->order('transfer_date','DESC')->limit(1);
	return $db->fetchAll($sql);
    }
    
    public function insertPayment($param){
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $fields = array("order_id" => !empty($param['order_number']) ? $param['order_number'] : "NULL",
                        "email" => !empty($param['email']) ? $param['email'] : "NULL",
                        "nominal" => !empty($param['nominal']) ? $param['nominal'] : "NULL",
                        "dest_bank" => !empty($param['bank_to']) ? $param['bank_to'] : "NULL",
                        "transfer_date" => !empty($param['transfer_date']) ? $param['transfer_date'] : "NULL",
                        "source_bank" => !empty($param['bank_from']) ? $param['bank_from'] : "NULL",
                        "source_acc_number" => !empty($param['acc_from']) ? $param['acc_from'] : "NULL",
                        "source_acc_name" => !empty($param['name_from']) ? $param['name_from'] : "NULL",
                        "comment" => !empty($param['comment']) ? $param['comment'] : "NULL",
			"entity_id" => !empty($param['entity_id']) ? (int)$param['entity_id'] : "0");
        $db->insert($this->confirmationTable, $fields);
        return true;
    }

    public function cronEmailPaymentconfirmation(){
	$db = Mage::getSingleton('core/resource')->getConnection('core_read');
	$i = 0;
	$limit = 1;
	$filename = './tes.csv';
	$handle = fopen($filename,'w+');
	fwrite($handle,"ORDER ID,e-mail,Nominal,Bank Penerima,Tanggal Transfer,Bank Pengirim,No Rek Pengirim,Nama Pengirim,Komentar\n");
	while(true){
                $field = array("id"=>"id","entity_id"=>"entity_id","order_id"=>"order_id","email"=>"email",
                               "nominal"=>"nominal","dest_bank"=>"dest_bank","transfer_date"=>"transfer_date",
                               "source_bank"=>"source_bank","source_acc_number"=>"source_acc_number",
                               "source_acc_name"=>"source_acc_name","comment"=>"comment");
                $query = $db->select()->from($this->confirmationTable,$field)
                            ->where(sprintf("id > %s",$i))
                            ->where(sprintf("DATE_FORMAT(created_at,'%s') = DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 DAY),'%s')",'%Y-%m-%d','%Y-%m-%d'))
                            ->order('id','ASC')->limit($limit);
                $data = $db->fetchAll($query);
		if(count($data) > 0){
			foreach($data as $idx => $rw){
				$i = $rw['id'];
				fwrite($handle,$rw['order_id'].",".$rw['email'].",".$rw['nominal'].",".$rw['dest_bank'].",".$rw['transfer_date'].",".$rw['source_bank'].",".$rw['source_acc_number'].",".$rw['source_acc_name'].",".str_replace("\n"," ".$rw['comment'])."\n");
			}
		}
		else{
			break;
		}
	}
	fclose($handle);
	$html .= "</TABLE></BODY></HTML>";
	$html = 'PFA';
	Mage :: app("default");
	$mail = new Zend_Mail();
	$mail->setType(Zend_Mime::MULTIPART_RELATED);
	$mail->setBodyHtml($html);
	$mail->setFrom(Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/sender_email'),Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/sender_name'));
        $mail->addTo(Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/receiver_email'),Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/receiver_name'));
	$mail->setSubject('[BILNA] Payment Confirmation List '.date('Y-m-d',mktime(0,0,0,date('m'),intval(date('d')-1),date('Y'))));
	$dir = Mage::getBaseDir();
	$file = $mail->createAttachment(file_get_contents($filename));
	$file ->type        = 'text/csv';
	$file ->disposition = Zend_Mime::DISPOSITION_INLINE;
	$file ->encoding    = Zend_Mime::ENCODING_BASE64;
	$file ->filename    = sprintf('payment_confirmation_list_%s.csv',date('Y-m-d',mktime(0,0,0,date('m'),intval(date('d')-1),date('Y'))));
	$mail->send();
	@unlink($filename);
    }
    
}


