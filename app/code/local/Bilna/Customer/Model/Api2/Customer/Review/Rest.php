<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rest
 *
 * @author bilnadev04
 */
abstract class Bilna_Customer_Model_Api2_Customer_Review_Rest extends Mage_Api2_Model_Resource
{

    /**
     * Retrieve information about specified customer address
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
    }

    /**
     * Get customer addresses list
     *
     * @return array
     */
    protected function _retrieveCollection() 
    {
        $reviewsCollection = Mage::getModel('review/review')->getCollection()
                ->addCustomerFilter($this->getRequest()->getParam('customer_id'))
                ->addStoreFilter(1)
                ->addStatusFilter(1)
                ->setDateOrder();
        
//        $customerId = $this->getRequest()->getParam('customer_id');
//        
//        $reviewsCollection = Mage::getModel('review/review')->load($customerId)->getCollection();
        
        return $reviewsCollection;
    }
}
