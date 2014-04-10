<?php
/**
 * Description of Bilna_Whitelistemail_Model_Processing
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Whitelistemail_Model_Processing extends Mage_Core_Model_Abstract {
    protected $_code = 'whitelistemail';
    protected $customerTable = 'customer_entity';
    protected $whitelistTable = 'whitelist_email';
    
    public function prepareSendEmailWhitelist($customerIds) {
        $customerExist = $this->getCustomerExist($customerIds);
        $customerData = $this->getCustomerData($customerIds, $this->parseCustomerExist($customerExist));
        
        if (is_array($customerExist) && count($customerExist) > 0) {
            $updateData = $this->prepareUpdateData($customerExist);
        }
        
        if (is_array($customerData) && count($customerData) > 0) {
            $insertData = $this->prepareInsertData($customerData);
        }
        
        $result = $updateData + $insertData;
        
        return (int) $result;
    }
    
    protected function getCustomerExist($customerIds) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = array (
            'customer_id' => 'customer_id',
            'customer_email' => 'customer_email',
            'sent' => 'sent',
            'status' => 'status'
        );

        $query = $connection->select()
            ->from($this->whitelistTable, $select)
            ->where(sprintf("customer_id IN (%s)", implode(',', $customerIds)));
        $rows = $connection->fetchAll($query);
        
        return $rows;
    }
    
    protected function parseCustomerExist($customerExist) {
        $result = array ();
        
        foreach ($customerExist as $customer) {
            $result[] = $customer['customer_id'];
        }
        
        return $result;
    }
    
    protected function getCustomerData($customerIds, $customerExist) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = array (
            'customer_id' => 'entity_id',
            'customer_email' => 'email'
        );

        $query = $connection->select()
            ->from($this->customerTable, $select)
            ->where(sprintf("entity_id IN (%s)", implode(',', $customerIds)));
        
        if (is_array($customerExist) && count($customerExist) > 0) {
            $query->where(sprintf("entity_id NOT IN (%s)", implode(',', $customerExist)));
        }
        
        $rows = $connection->fetchAll($query);
        
        return $rows;
    }
    
    protected function prepareUpdateData($rows) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = '';
        $result = 0;
        
        foreach ($rows as $row) {
            if ($row['status'] != 1) {
                $sql .= sprintf(
                    "UPDATE %s SET sent = %d, status = 1 WHERE customer_id = %d LIMIT 1;",
                    $this->whitelistTable, (int) ($row['sent'] + 1), $row['customer_id']
                );
                $result++;
            }
        }
        
        if (!empty ($sql)) {
            $query = $connection->query($sql);
        }
        
        return $result;
    }

    protected function prepareInsertData($rows) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = sprintf("INSERT INTO %s (customer_id, customer_email, code, sent, status) VALUES ", $this->whitelistTable);
        $separator = false;
        $result = 0;
        
        foreach ($rows as $row) {
            if ($separator) {
                $sql .= ", ";
            }
            
            $sql .= sprintf("(%d, '%s', '%s', 1, 1)", $row['customer_id'], $row['customer_email'], $this->getCustomerCode());
            $separator = true;
        }
        
        $query = $connection->query($sql);
        
        if ($query) {
            $result = count($rows);
        }
        
        return $result;
    }

    protected function getCustomerCode() {
        return Mage::Helper('whitelistemail')->getCustomerCode();
    }
    
    protected function getCustomerEncodeKey($customerId, $customerCode) {
        return Mage::Helper('whitelistemail')->getCustomerEncodeKey($customerId, $customerCode);
    }
    
    public function cronEmailWhitelist() {
        $sendEmail = $this->sendEmailWhitelist();
        $updateStatus = $this->updateStatusEmailWhitelist($sendEmail['success']);
    }
    
    protected function sendEmailWhitelist() {
        $rows = $this->getSendEmailWhitelist();
        $success = array ();
        $failed = array ();
        
        if (is_array($rows) && count($rows) > 0) {
            foreach ($rows as $row) {
                $to = array (
                    'email_to' => $row['customer_email'],
                    'name_to' => $row['customer_email']
                );
                $data = array (
                    'customer_email' => $row['customer_email'],
                    'code' => $this->getCustomerEncodeKey($row['customer_id'], $row['code'])
                );
                
                $sendEmail = $this->sendEmail($to, $data);
                
                if ($sendEmail) {
                    $success[] = $row['id'];
                }
                else {
                    $failed[] = $row['id'];
                }
            }
        }
        
        return array ('success' => $success, 'failed' => $failed);
    }
    
    protected function getSendEmailWhitelist() {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = array (
            'id' => 'id',
            'customer_id' => 'customer_id',
            'customer_email' => 'customer_email',
            'code' => 'code'
        );
        $where = "status = 1";

        $query = $connection->select()
            ->from($this->whitelistTable, $select)
            ->where($where);
        
        return $connection->fetchAll($query);
    }
    
    protected function sendEmail($to, $data) {
        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');
        $processEmail = Mage::getModel('core/email_template');
        
        // setting parameter
        $emailTemplateId = Mage::getStoreConfig('bilna_whitelistemail/whitelistemail/template_email_id');
        $emailSender = array (
            'name' => 'Bilna.com', //configurable
            'email' => 'cs@bilna.com' //configurable
        );
        $emailTo = $to['email_to'];
        $nameTo = $to['name_to'];
        
        $processEmail->sendTransactional($emailTemplateId, $emailSender, $emailTo, $nameTo, $data, $storeId);
        $translate->setTranslateInline(true);

        if ($processEmail) {
            return true;
        }

        return false;
    }

    protected function updateStatusEmailWhitelist($data) {
        if (is_array($data) && count($data) > 0) {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = sprintf("UPDATE %s SET status = 2 WHERE id IN (%s) LIMIT %d", $this->whitelistTable, implode(',', $data), count($data));
            $query = $connection->query($sql);
        }
            
        return true;
    }

    public function updateCustomerReadEmail($data) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = sprintf(
            "UPDATE %s SET type = 1 WHERE customer_id = %d AND code = '%s' LIMIT 1",
            $this->whitelistTable, $data['customer_id'], $data['code']
        );
        
        if ($connection->query($sql)) {
            return true;
        }
        
        return false;
    }
    
    public function cronEmailGraylist() {
        $customers = $this->getCustomerGraylist();
        $customerIds = array ();
        
        if (is_array($customers) && count($customers) > 0) {
            foreach ($customers as $customer) {
                $customerIds[] = $customer['id'];
            }
            
            $this->setCustomerGraylist($customerIds);
        }
        
        exit;
    }
    
    protected function getCustomerGraylist() {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = array ('id' => 'id');
        $where = "sent >= 3 AND type = 0";
        $query = $connection->select()
            ->from($this->whitelistTable, $select)
            ->where($where);
        
        return $connection->fetchAll($query);
    }
    
    protected function setCustomerGraylist($customerIds) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = sprintf(
            "UPDATE %s SET type = 2 WHERE id IN (%s) LIMIT %d",
            $this->whitelistTable, implode(',', $customerIds), count($customerIds)
        );
        
        if ($connection->query($sql)) {
            return true;
        }
        
        return false;
    }
}
