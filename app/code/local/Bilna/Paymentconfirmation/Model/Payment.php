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
        $sql = sprintf( "SELECT entity_id,grand_total,total_paid,status,customer_email FROM %s ".
                        "WHERE increment_id = '%s'", $this->orderTable,$orderNumber);
        return $db->fetchAll($sql);
    }
    public function isPaymentExists($orderNumber){
	$db = Mage::getSingleton('core/resource')->getConnection('core_read');
	$sql = sprintf(	"SELECT order_id,email,nominal,dest_bank,transfer_date,source_bank,source_acc_number,".
			"source_acc_name,comment,entity_id FROM %s where order_id = '%s'",$this->confirmationTable,$orderNumber);
	return $db->fetchAll($sql);
    }
    
    public function insertPayment($param){
        //print_r($param);//exit;
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = sprintf( "INSERT INTO %s set created_at = NOW(),".
                        "order_id = %s ,email = %s, nominal = %s, dest_bank = %s, transfer_date = %s, ".
                        "source_bank = %s, source_acc_number = %s, source_acc_name = %s, comment = %s, entity_id = %s",$this->confirmationTable,
                        !empty($param['order_number']) ? "'".$param['order_number']."'" : "NULL",
                        !empty($param['email']) ? "'".$param['email']."'" : "NULL",
                        !empty($param['nominal']) ? $param['nominal'] : "NULL",
                        !empty($param['bank_to']) ? "'".$param['bank_to']."'" : "NULL",
                        !empty($param['transfer_date']) ? "'".$param['transfer_date']."'" : "NULL",
                        !empty($param['bank_from']) ? "'".$param['bank_from']."'" : "NULL",
                        !empty($param['acc_from']) ? "'".$param['acc_from']."'" : "NULL",
                        !empty($param['name_from']) ? "'".$param['name_from']."'" : "NULL",
                        !empty($param['comment']) ? "'".$param['comment']."'" : "NULL",
			!empty($param['entity_id']) ? (int)$param['entity_id'] : "0");
        //print $sql;
        //exit;
        $query = $db->query($sql);
        return $query;
    }

    public function cronEmailPaymentconfirmation(){
	$db = Mage::getSingleton('core/resource')->getConnection('core_read');
	$i = 0;
	$limit = 1;
	$filename = './tes.csv';
	$handle = fopen($filename,'w+');
	fwrite($handle,"ORDER ID,e-mail,Nominal,Bank Penerima,Tanggal Transfer,Bank Pengirim,No Rek Pengirim,Nama Pengirim,Komentar\n");
	//$html = "<HTML><HEAD></HEAD><BODY><TABLE BORDER=\"1\"><TR><TD>ORDER ID</TD><TD>e-Mail</TD><TD>Nominal</TD><TD>BANK PENERIMA</TD><TD>TANGGAL TRANSFER</TD><TD>BANK PENGIRIM</TD><TD>NO REK PENGIRIM</TD><TD>NAMA PENGIRIM</TD><TD>KOMENTAR</TD></TR>";
	while(true){
		$sql = sprintf( "SELECT id,entity_id,order_id,email,nominal,dest_bank,transfer_date,source_bank,source_acc_number,".
				"source_acc_name,comment FROM %s WHERE id > %s AND ".
				"DATE_FORMAT(created_at,'%s') = DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 DAY),'%s') ".
				"LIMIT %d",$this->confirmationTable,$i,'%Y-%m-%d','%Y-%m-%d',$limit);
		$result = $db->query($sql);
		$data = $result->fetchAll();
		if(count($data) > 0){
			foreach($data as $idx => $rw){
				$i = $rw['id'];
				fwrite($handle,$rw['order_id'].",".$rw['email'].",".$rw['nominal'].",".$rw['dest_bank'].",".$rw['transfer_date'].",".$rw['source_bank'].",".$rw['source_acc_number'].",".$rw['source_acc_name'].",".str_replace("\n"," ".$rw['comment'])."\n");
				//$html .= sprintf("<TR><TD>%s</TD><TD>%s</TD><TD>%s</TD><TD>%s</TD><TD>%s</TD><TD>%s</TD><TD>%s</TD><TD>%s</TD><TD>%s</TD></TR>","<a href=\"".Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/",array("order_id" => $rw['entity_id']))."\">".$rw['order_id']."</a>",$rw['email'],number_format($rw['nominal'],0,",","."),$rw['dest_bank'],$rw['transfer_date'],$rw['source_bank'],$rw['source_acc_number'],$rw['source_acc_name'],$rw['comment']);
			}
		}
		else{
			break;
		}
	}
	fclose($handle);
	$html .= "</TABLE></BODY></HTML>";
	/*
	$mail = Mage::getModel('core/email');
	$mail->setToName('Deni Dhian');
	$mail->setToEmail('dendhi31@yahoo.com');
	$mail->setBody($html); 
	$mail->setSubject('Payment Confirmation '.date('d-m-Y'));
	$mail->setFromEmail('deni.dhian@bilna.com');
	$mail->setFromName("Bilna Admin");
	$mail->setType('html');// Html or text format in setBody
	$mail->send();
	*/
	$html = 'PFA';
	Mage :: app("default");
	$mail = new Zend_Mail();
	$mail->setType(Zend_Mime::MULTIPART_RELATED);
	$mail->setBodyHtml($html);
	$mail->setFrom(Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('cronPaymentConfirmSenderEmail')->getValue('text'), (Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('cronPaymentConfirmSenderName')->getValue('text')));
	$mail->addTo(Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('cronPaymentConfirmReceiverEmail')->getValue('text'), (Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('cronPaymentConfirmReceiverName')->getValue('text')));
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


