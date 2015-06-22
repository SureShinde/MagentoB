<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

    
class AW_Advancedreports_Model_Additional_Reports extends Varien_Object
{
    const UNIT_NAME_PREFIX = 'AW_ARUnit';

    protected $_reports = array();

    public function getReports()
    {
        if ( !$this->_reports ){
            $reports = array();        
            # Collect additional reports data here
            $searchDir = Mage::getStoreConfig('system/filesystem/code') 
                            .    DS . "local" 
                            .    DS . "AW"                             
                            .    DS . "Advancedreports" 
                            .    DS . "etc" 
                            .    DS . "additional"
                            ;
            if (!is_dir($searchDir)){ return; }
            $files = scandir( $searchDir );
            foreach ( $files as $file ){
                $fileName = $searchDir . DS . $file; 
                if ( is_file( $fileName ) ){
                    $info = pathinfo($fileName);
                    if (isset($info['extension']) && strtolower($info['extension']) == "xml"){
                        $name = basename($fileName, ".xml");                                                    
                        try{
                            $element = simplexml_load_file( $fileName );
                        }
                        catch(Exception $e){
                            //TODO Catch same error
                            continue;
                        }
                        if ($element){                                                        
                            if (strtolower($element->$name->active) == "true")
                            {
                                $sysName = self::UNIT_NAME_PREFIX.uc_words($name);
                                $item = Mage::getModel('advancedreports/additional_item');
                                $item->setName($name);
                                $item->setTitle((string)$element->$name->title);
                                $item->setVersion((string)Mage::getConfig()->getNode("modules/{$sysName}/version"));
                                $item->setRequiredVersion((string)$element->$name->required_version);
                                $item->setSortOrder((string)$element->$name->sort_order);                                
                                $reports[] = $item;
                            }                            
                                                        
                        }                        
                    }
                } 
            }    
            $this->_reports = $reports;                            
        }                
        return $this->_reports;
    }    
    
    public function getCount()
    {
        return count($this->getReports());        
    }
    
    public function getTitle($name)
    {
        if ($this->getCount()){
            foreach ($this->getReports() as $report){
                if ($report->getName() == $name){
                    return $report->getTitle();
                }
            }
        }
        
    }
}    