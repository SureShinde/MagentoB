    <?php
    /**
     * run every day at ?
     */

    require_once realpath(dirname(__FILE__)).'/../app/Mage.php'; // load magento API

    Mage::app();
    $i = 1;
    $oldId = 0;
    $stop = 0;
    $filename = Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/file_email').rand(1000,9999).'.csv';
    $csv = new Varien_File_Csv();
    $csvdata = array();
    $date = Mage::getModel('core/date')->date('Y-m-d H:00:00');
    $data['number'] = "Nomor Pesanan";
    $data['email'] = "Alamat Email";
    $data['payTotal'] = "Jumlah Yang diBayar";
    $data['payAcc'] = "Bayar ke Rekening";
    $data['payDate'] = "Tanggal Bayar";
    $data['sourceBank'] = "Nama Bank Asal";
    $data['payName'] = "Nama Pengirim Sesuai Rekening";
    $data['comment'] = "Komentar";
    $csvdata[] = $data;
    while(true){
        $sendMail = Mage::getModel('Paymentconfirmation/payment')
                ->getCollection()
                ->addFieldToFilter('created_at',array('gteq'=>Mage::getModel('core/date')->date('Y-m-d H:00:00', strtotime(" -1 hours"))))
                ->addFieldToFilter('created_at',array('lteq'=>Mage::getModel('core/date')->date('Y-m-d H:59:59', strtotime(" -1 hours"))))
                ->setCurPage($i)
                ->setPageSize(100);
        
        if(count($sendMail) < 1) break;
        if($stop > 0) break;
        foreach($sendMail as $collection) {
            if($oldId >= $collection->id){
                $stop = 1;
                break;
            }
            $data = array();
            $data['number'] = $collection->order_id;
            $data['email'] = $collection->email;
            $data['payTotal'] = $collection->nominal;
            $data['payAcc'] = $collection->dest_bank;
            $data['payDate'] = $collection->transfer_date;
            $data['sourceBank'] = $collection->source_bank;
            $data['payName'] = $collection->source_acc_name;
            $data['comment'] = str_replace("\n"," ",$collection->comment);
            $csvdata[] = $data;
            $oldId = $collection->id;
        }
        $i++;
    }
    $csv->saveData($filename, $csvdata);
    unset($csvdata);

    $html .= "</TABLE></BODY></HTML>";
    $html = 'PFA';
    $mail = new Zend_Mail();
    $mail->setType(Zend_Mime::MULTIPART_RELATED);
    $mail->setBodyHtml($html);
    $mail->setFrom(Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/sender_email'),Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/sender_name'));
    $mailTo = explode(",",Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/receiver_email'));
    $mail->addTo($mailTo);
    $mail->setSubject('[BILNA] Payment Confirmation List '.Mage::getModel('core/date')->date('Y-m-d H', strtotime("-1 hours")));
    $dir = Mage::getBaseDir();
    $file = $mail->createAttachment(file_get_contents($filename));
    $file ->type        = 'text/csv';
    $file ->disposition = Zend_Mime::DISPOSITION_INLINE;
    $file ->encoding    = Zend_Mime::ENCODING_BASE64;
    $file ->filename    = sprintf('payment_confirmation_list_%s.csv',Mage::getModel('core/date')->date('YmdH', strtotime("-1 hours")));
    try{
        $mail->send();
    }
    catch (Exception $e) {
        Mage::logException($e);
    }
    @unlink($filename);
    