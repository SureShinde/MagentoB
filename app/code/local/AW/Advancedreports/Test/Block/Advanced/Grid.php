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

class AW_Advancedreports_Test_Block_Advanced_Grid extends EcomDev_PHPUnit_Test_Case
{     
    
    /**
     * Instance of tested object
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }
        
    protected function setUp() 
    {
        parent::setUp();
        
        #set up user
        Mage::getSingleton('admin/session')->setUser(new Varien_Object(array('id'=>1)));        
        
    }

        
    /**
     * Test for AW_Advancedreports_Block_Advanced_Grid::getFilter($name)
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function testGetFilterName($storedData, $postFilterString, $name, $result)
    {
        parse_str($storedData, $data);
        $session = Mage::getSingleton('adminhtml/session');        
        $session->setData(AW_Advancedreports_Helper_Queue::DATA_KEY_LAST_FILTERS, $data);        
        
        $block = new AW_Advancedreports_Block_Advanced_Grid();                              
        parse_str($postFilterString, $filters);
        foreach ($filters as $filterKey => $filterValue){
            $block->setFilter($filterKey, $filterValue);
        }                        
        $block->setId('one')->setId('two');                
        $this->assertEquals($result, $block->getFilter($name));               
    }
}