<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Generatereport_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Generatereport_Rest
{
    
    protected $_customerId = null;
    
    protected function _retrieve() 
    {   
        $customerId = $this->getRequest()->getParam('customer_id');
        $reportType = $this->getRequest()->getParam('report_type');
        $datePeriod = $this->getRequest()->getParam('date_period');
        $periodFrom = $this->getRequest()->getParam('period_from');
        $periodTo = $this->getRequest()->getParam('period_to');
        $detalization = $this->getRequest()->getParam('detalization');
        $campaignsUriParam = $this->getRequest()->getParam('campaigns');
        $campaigns = explode(',', $campaignsUriParam);
        
        $params = array(
            'customer_id' => $customerId, 
            'report_type' => $reportType, 
            'date_period' => $datePeriod, 
            'period_from' => $periodFrom, 
            'period_to' => $periodTo, 
            'detalization' => $detalization, 
            'campaigns' => $campaigns, 
            
        );
        //var_dump($params);die;
        $this->_customerId = $params['customer_id'];
        
        return $this->getReportAsJson($params);
    }

    protected function getReportAsJson($postData)
    {
        if (!$this->_customerId) {
            return 'Customer is not logged in';
        }
        
        //$this->_getSession()->setCreateReportFormData($postData);
        //var_dump(json_encode($postData));die;
        
        $messages = array();
        
        $response = new Varien_Object();
        $response->setError(0);
        
        $affiliate = Mage::getModel('awaffiliate/affiliate');
        if ($this->_customerId) {
            $affiliate->loadByCustomerId($this->_customerId);
        }

        $affiliateId = intval($affiliate->getId());
        if ($affiliateId < 1) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Unable to get the affiliate ID');
        }

        if (!isset($postData['report_type'])) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Report type is not specified');
        }

        Mage::register('current_affiliate', $affiliate);
        
        if ($response->getError() == 0) {
            if ($postData['report_type'] == AW_Affiliate_Model_Source_Report_Type::SALES) {
                $report = new AW_Affiliate_Block_Report_View_Sales();
                
                $items = $report->getItems();
                $detalizationLabel = $report->getDetalizationLabel();
                $campaigns = $report->getCampaigns();
                $defaultCurrencySymbol = $report->getDefaultCurrencySymbol();
                $maxRangesLimitExceeded = $report->getMaxRangesLimitExceeded();
                $totals = $report->getTotals();
                
                $response = array(
                    'item' => $items, 
                    'detalizationLabel' => $detalizationLabel, 
                    'campaigns' => $campaigns, 
                    'defaultCurrencySymbol' => $defaultCurrencySymbol, 
                    'maxRangesLimitExceeded' => $maxRangesLimitExceeded, 
                    'totals' => $totals, 
                    
                );
                
            } elseif ($postData['report_type'] == AW_Affiliate_Model_Source_Report_Type::TRANSACTIONS) {
                $report = new AW_Affiliate_Block_Report_View_Transactions();
                
                $items = $report->getItems();
                $detalizationLabel = $report->getDetalizationLabel();
                $campaigns = $report->getCampaigns();
                $defaultCurrencySymbol = $report->getDefaultCurrencySymbol();
                $maxRangesLimitExceeded = $report->getMaxRangesLimitExceeded();
                $totals = $report->getTotals();
                
                $response = array(
                    'item' => $items, 
                    'detalizationLabel' => $detalizationLabel, 
                    'campaigns' => $campaigns, 
                    'defaultCurrencySymbol' => $defaultCurrencySymbol, 
                    'maxRangesLimitExceeded' => $maxRangesLimitExceeded, 
                    'totals' => $totals, 
                    
                );
                
            } elseif ($postData['report_type'] == AW_Affiliate_Model_Source_Report_Type::TRAFFIC) {
                $report = new AW_Affiliate_Block_Report_View_Traffic();
                
                $items = $report->getItems();
                $detalizationLabel = $report->getDetalizationLabel();
                $campaigns = $report->getCampaigns();
                $defaultCurrencySymbol = $report->getDefaultCurrencySymbol();
                $maxRangesLimitExceeded = $report->getMaxRangesLimitExceeded();
                $totals = $report->getTotals();
                
                $response = array(
                    'item' => $items, 
                    'detalizationLabel' => $detalizationLabel, 
                    'campaigns' => $campaigns, 
                    'defaultCurrencySymbol' => $defaultCurrencySymbol, 
                    'maxRangesLimitExceeded' => $maxRangesLimitExceeded, 
                    'totals' => $totals, 
                    
                );
                
            } else {
                $response->setError(1);
                $messages[] = Mage::helper('awaffiliate')->__('Invalid report type');
            }
        }
        
        return array(
            'message' => $messages, 
            'response' => $response 
        );
    }
}