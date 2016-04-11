<?php

/**
 * paymethod api resource
 *
 * @category   Bilna
 * @package    Bilna_Paymethod
 * @author     Bilna Development Team <core@magentocommerce.com>
 */
class Bilna_Paymethod_Model_Api2_Vtdirect extends Mage_Api2_Model_Resource
{
    protected $_tblVtCharge = 'veritrans_api_log';
    
    //- MySQL Connection
    protected $_dbResource;
    protected $_dbRead;
    protected $_dbWrite;

    public function __construct() {
        $this->_dbResource = Mage::getSingleton('core/resource');
        $this->_dbRead = $this->_dbResource->getConnection('core_read');
        $this->_dbWrite = $this->_dbResource->getConnection('core_write');
    }
    
    public function getQuery($incrementId = null) {
        $sql = "SELECT * FROM `{$this->_tblVtCharge}` WHERE order_no = '".$incrementId."' LIMIT 1";
        
        return $this->_dbRead->fetchRow($sql);
    }

}