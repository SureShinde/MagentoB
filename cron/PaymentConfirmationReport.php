    <?php
    /**
     * run every day at ?
     */

    require_once realpath(dirname(__FILE__)).'/../app/Mage.php'; // load magento API

    Mage::app();
    $i = 1;
    $oldId = 0;
    $stop = 0;
    $filename = './tes.csv';
    //print $filename;
    $handle = fopen($filename,'w+');
    //print $handle;exit;
    fwrite($handle,"ORDER ID,e-mail,Nominal,Bank Penerima,Tanggal Transfer,Bank Pengirim,No Rek Pengirim,Nama Pengirim,Komentar\n");
    while(true){
        $sendMail = Mage::getModel('Paymentconfirmation/payment')
                ->getCollection()
                ->addFieldToFilter('created_at',array('gteq'=>date('Y-m-d H:i:s',mktime(0,0,0,date('m'),intval(date('d'))-1,date('Y')))))
                ->addFieldToFilter('created_at',array('lteq'=>date('Y-m-d H:i:s',mktime(23,59,59,date('m'),intval(date('d'))-1,date('Y')))))
                ->setCurPage($i)
                ->setPageSize(10);
        if(count($sendMail) < 1) break;
        if($stop > 0) break;
        foreach($sendMail as $collection) {
            if($oldId >= $collection->id){
                $stop = 1;
                break;
            }
            //print $collection->id."\n";
            fwrite($handle,$collection->order_id.",".$$collection->email.",".$collection->nominal.",".$collection->dest_bank.",".$collection->transfer_date.",".$collection->source_bank.",".$collection->source_acc_number.",".$collection->source_acc_name.",".str_replace("\n"," ".$collection->comment)."\n");
            $oldId = $collection->id;
        }
        $i++;
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
    //$sendMail->cronEmailPaymentconfirmation();
    //print_r($sendMail);
    //$sendEmail = Mage::getModel('whitelistemail/processing');
    //$sendEmail->cronEmailWhitelist();

