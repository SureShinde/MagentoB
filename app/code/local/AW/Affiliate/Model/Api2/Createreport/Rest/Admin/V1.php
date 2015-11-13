<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Createreport_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Createreport_Rest
{   
    
    const DEFAULT_STORE_ID = 1;
    
    protected $_collection = null;
    
    protected $_customerId = null;

    public function __construct() 
    {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }
    
    protected function _retrieve()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $this->_customerId = $customerId;
        
        return array(
            'form_fields' => array(
                'report_type' => $this->getReportTypeField(), 
                'date_period' => $this->getReportDatePeriodField(),  
                'period_from' => $this->getPeriodFromField(), 
                'period_to' => $this->getPeriodToField(), 
                'detalizations' => $this->getDetalizationsField()
            ), 
            'campaigns' => $this->getCampaignsField()
        );
    }

    public function getReportTypeField()
    {
        return array(
            'label' => 'Report type',
            'html_id' => 'report-type',
            'name' => 'report_type',
            'no_span' => true,
            'values' => $this->getReportTypes(),
        );
    }

    public function getReportDatePeriodField()
    {
        return array(
            'label' => 'Date period',
            'html_id' => 'date-period',
            'name' => 'date_period',
            'no_span' => true,
            'values' => $this->getDefaultDatePeriods(),
        );
    }

    public function getPeriodFromField()
    {
        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        
        $dateDefault = Mage::helper('awaffiliate/report')
                ->getPeriodRange(AW_Affiliate_Model_Source_Report_Period::CUSTOM_PERIOD_DEFAULT);
        
        return $dateDefault['from'];
    }

    public function getPeriodToField()
    {
        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        
        $dateDefault = Mage::helper('awaffiliate/report')
            ->getPeriodRange(AW_Affiliate_Model_Source_Report_Period::CUSTOM_PERIOD_DEFAULT);
        
        return $dateDefault['to'];
    }

    public function getDetalizationsField()
    {
        return array(
            'label' => 'Graph by',
            'html_id' => 'detalization',
            'name' => 'detalization',
            'no_span' => true,
            'values' => $this->getDetalizations(),
        );
    }

    protected function getDetalizations()
    {
        return Mage::getModel('awaffiliate/source_report_detalization')->toOptionArray();
    }

    public function getCampaignsField()
    {
        return array(
            'label' => 'Include Campaigns',
            'html_id' => 'campaigns',
            'name' => 'campaigns',
            'no_span' => true,
            'values' => $this->getCampaigns(),
            'value' => array_keys($this->getCampaigns(true)),
            'select_all' => 'Select All',
            'deselect_all' => 'Deselect All',
        );
    }

    protected function getReportTypes()
    {
        $types = Mage::getModel('awaffiliate/source_report_type')->toOptionArray();
        return $types;
    }

    protected function getDefaultDatePeriods()
    {
        $periods = Mage::getModel('awaffiliate/source_report_period')->toOptionArray();
        return $periods;
    }

    /**
     * Returns associative array $value => $label
     * @return array
     */
    public function toShortOptionArray($options = array())
    {
        $_options = array();
        foreach ($options as $option)
            $_options[$option['value']] = $option['label'];
        return $_options;
    }

    protected function _getHelper($ext = '')
    {
        return Mage::helper('awaffiliate' . ($ext ? '/' . $ext : ''));
    }
    
    protected function __getAffiliate()
    {
        $affiliate = Mage::getModel('awaffiliate/affiliate');

        if ($this->_customerId) {
            $affiliate->loadByCustomerId($this->_customerId);
        } else {
            Mage::throwException('Affiliate ID is empty');
        }
        
        return $affiliate;
    }

    public function toOptionArray()
    {
        if (is_null($this->_collection)) {
            $this->_collection = Mage::getModel('awaffiliate/campaign')->getCollection();
        }
        
        $affiliate = $this->__getAffiliate();
        
        if($affiliate->getId())
        {
            $customer = $affiliate->getCustomer();
        }
        
        $options = array();
        $helper = $this->_getHelper();
        foreach ($this->_collection as $campaign) {
            if (in_array($customer->getGroupId(), $campaign->getAllowedGroups()) &&
                in_array($customer->getWebsiteId(), $campaign->getStoreIds())
            ) {
                $options[] = array('value' => $campaign->getId(), 'label' => $helper->__($campaign->getName()));
            }
        }
        return $options;
    }

    protected function getCampaigns($short = false)
    {
        return $short ? $this->toShortOptionArray($this->toOptionArray()) : $this->toOptionArray();
    }
}