<?php

require_once realpath(dirname(__FILE__)).'/../app/Mage.php';

class PaymentConfirmationReport
{
    private $lastExecTime;
    private $coreFlagModel;
    const LIMIT = 100;

    public function __construct()
    {
        Mage::app();
        $this->coreFlagModel = Mage::getModel('Paymentconfirmation/flag');
    }

    private function setLastExecTime()
    {
        $flag = $this->coreFlagModel->loadSelf();
        if (trim($flag->flag_data) == '') {
            $this->coreFlagModel->setFlagData(Mage::getModel('core/date')->date('Y-m-d H', strtotime("-1 hours")))->save();
            $flag = $this->coreFlagModel->loadSelf();
        }
        $this->lastExecTime = unserialize($flag->flag_data);
    }

    private function updateLastExecTime()
    {
        $this->coreFlagModel->setFlagData(Mage::getModel('core/date')->date('Y-m-d H'))->save();
    }

    private function getScheduledTime()
    {
        $configScheduled = Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/run_time');
        if (trim($configScheduled) != "") { 
            $configScheduled = "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23"; 
        }
        return explode(",",$configScheduled);
    }

    private function isScheduledToRun($scheduledHours)
    {
        if (Mage::getModel('core/date')->date('Y-m-d H') <= $this->lastExecTime) {
            echo "Cron Payment Confirmation is Not Running because it's already running for current hour\n";
            return false;
        }
        
        if (!in_array((int)Mage::getModel('core/date')->date('H'),$scheduledHours)) {
            echo "Cron Payment Confirmation is Not Running because it's not the scheduled Time\n";
            return false;
        }

        return true;
    }

    private function getConfirmationData($page)
    {
        $confirmationData = Mage::getModel('Paymentconfirmation/payment')
                    ->getCollection()
                    ->addFieldToFilter('created_at',array('gteq'=>$this->lastExecTime.":00:00"))
                    ->addFieldToFilter('created_at',array('lteq'=>Mage::getModel('core/date')->date('Y-m-d H:59:59', strtotime(" -1 hours"))))
                    ->setCurPage($page)
                    ->setPageSize(self::LIMIT);
        return $confirmationData;
    }

    private function generateAttachmentFile()
    {
        $page = 1;
        $oldId = 0;
        $stop = 0;
        $filename = Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/file_email').rand(1000,9999).'.csv';
        
        $csv = new Varien_File_Csv();
        $csvdata = array();
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
            $confirmationData = $this->getConfirmationData($page);
            if (count($confirmationData) < 1) break;
            if ($stop > 0) break;
            foreach ($confirmationData as $collection) {
                if ($oldId >= $collection->id) {
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
            $page++;
        }
        $csv->saveData($filename, $csvdata);

        return $filename;
    }

    private function sendEmail()
    {
        $filename = $this->generateAttachmentFile();
        $html = 'Please find the attached file';
        $mail = new Zend_Mail();
        $mail->setType(Zend_Mime::MULTIPART_RELATED);
        $mail->setBodyHtml($html);
        $mail->setFrom(Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/sender_email'),Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/sender_name'));
        $mailTo = explode(",",Mage::getStoreConfig('bilna_paymentconfirmation/paymentconfirmation/receiver_email'));
        $mail->addTo($mailTo);
        $mail->setSubject('[BILNA] Payment Confirmation List '.$this->lastExecTime.' To '.Mage::getModel('core/date')->date('Y-m-d H', strtotime("-1 hours")));
        $dir = Mage::getBaseDir();
        $file = $mail->createAttachment(file_get_contents($filename));
        $file ->type        = 'text/csv';
        $file ->disposition = Zend_Mime::DISPOSITION_INLINE;
        $file ->encoding    = Zend_Mime::ENCODING_BASE64;
        $file ->filename    = sprintf('payment_confirmation_list_%s_%s.csv',  str_replace(array(" ","-"), "",$this->lastExecTime),Mage::getModel('core/date')->date('YmdH', strtotime("-1 hours")));
        $exitStatus = 0;
        try {
            $mail->send();
            $this->updateLastExecTime();
        }
        catch (Exception $e) {
            Mage::logException($e);
            $exitStatus = 1;
        }
        @unlink($filename);
        return $exitStatus;
    }

    public function main()
    {
        $this->setLastExecTime();
        $scheduledHours = $this->getScheduledTime();
        if (!$this->isScheduledToRun($scheduledHours)) {
            return 0;
        }

        return $this->sendEmail();
    }
}

$cronObj = new PaymentConfirmationReport();
$exitStatus = $cronObj->main();

if ($exitStatus == 0) {
    Mage::log("Cron Payment Confirmation Success Running");
    echo "Cron Payment Confirmation Success Running\n";
}

exit($exitStatus);