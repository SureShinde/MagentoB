<?php
class Bilna_Wrappinggiftevent_Model_Sales_Order extends Mage_Sales_Model_Order
{
    public function hasCustomFields()
    {
        $var = $this->getSsn();
        if($var && !empty($var)){
                return true;
        }else{
                return false;
        }
    }
    public function getFieldHtml()
    {
        $var = $this->getSsn();
        $html = '<b>SSN:</b>'.$var.'<br/>';
        return $html;
    }

    public function getEncryptedIncrementId()
    {
        return urlencode(openssl_encrypt($this->getIncrementId(), 'AES-128-CBC', 'bilna-obfuscate-17'));
    }
}
