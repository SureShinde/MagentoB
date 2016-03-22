    <?php
    /**
     * run every day at ?
     */

    require_once realpath(dirname(__FILE__)).'/../app/Mage.php'; // load magento API
    Mage::app();
    
    $flag = Mage::getModel('Paymentconfirmation/flag');
    $getFlag = $flag->loadSelf();
    if(trim($getFlag->flag_data) == ''){
        $flag->setFlagData(Mage::getModel('core/date')->date('Y-m-d H', strtotime("-1 hours")))->save();
        $getFlag = $flag->loadSelf();
    }
    $nextExecute = unserialize($getFlag->flag_data);
    
    if(trim(Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/run_time')) == ""){
        Mage::getConfig()->saveConfig('bilna_paymentconfirmation/paymentconfirmation/run_time','0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23');
        Mage::app()->getConfig()->reinit();
    }
    $configScheduled = Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/run_time');
    $arrScheduledTime = explode(",",$configScheduled);
    $currentHour = (int)Mage::getModel('core/date')->date('H');
    if(!in_array($currentHour,$arrScheduledTime)){
        print "gk jalan";
        exit(0);
    }
    $i = 1;
    $oldId = 0;
    $stop = 0;
    $filename = Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/file_email').rand(1000,9999).'.csv';
    $csv = new Varien_File_Csv();
    $csvdata = array();
    $date = Mage::getModel('core/date')->date('Y-m-d H:00:00');
    $data['created_at'] = "Waktu Konfirmasi";
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
                ->addFieldToFilter('created_at',array('gteq'=>$nextExecute.":00:00"))
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
            $data['created_at'] = $collection->created_at;
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
    $mail->setSubject('[BILNA] Payment Confirmation List '.$nextExecute.' To '.Mage::getModel('core/date')->date('Y-m-d H', strtotime("-1 hours")));
    $dir = Mage::getBaseDir();
    $file = $mail->createAttachment(file_get_contents($filename));
    $file ->type        = 'text/csv';
    $file ->disposition = Zend_Mime::DISPOSITION_INLINE;
    $file ->encoding    = Zend_Mime::ENCODING_BASE64;
    $file ->filename    = sprintf('payment_confirmation_list_%s_%s.csv',  str_replace(array(" ","-"), "",Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/next_execute')),Mage::getModel('core/date')->date('YmdH', strtotime("-1 hours")));
    $exitStatus = 0;
    try{
        $mail->send();
    }
    catch (Exception $e) {
        Mage::logException($e);
        $exitStatus = 1;
    }
    @unlink($filename);
    $flag->setFlagData(Mage::getModel('core/date')->date('Y-m-d H'))->save();
    Mage::log("Cron Payment Confirmation Success Running");
    exit($exitStatus);
    