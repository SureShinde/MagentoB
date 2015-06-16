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

class AW_Advancedreports_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{       
    /**
     * Instance of tested object
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }
    
    
    /**
     * Test getLastWeekLabel method when sunday is first day
     * 
     * @loadFixture
     */    
    public function testGetLastBusinessWeekLabelMonFri()
    {
        $this->assertEquals('Last business week (Mon - Fri)', $this->_helper()->getLastBusinessWeekLabel());
    }    
    
    /**
     * Test getLastWeekLabel method when sunday is first day
     * 
     * @loadFixture
     */    
    public function testGetLastBusinessWeekLabelMonSat()
    {
        $this->assertEquals('Last business week (Mon - Sat)', $this->_helper()->getLastBusinessWeekLabel());
    }    
    
    /**
     * Test getLastBusinessWeekRange method
     * 
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function testGetLastBusinessWeekRangeMonFri($today, $firstDay, $lastDay)
    {        
        $result = $this->_helper()->getLastBusinessWeekRange(new Zend_Date($today, Zend_Date::ISO_8601));                     
        $this->assertEquals(array('from'=>$firstDay, 'to'=>$lastDay), $result->getData() );                
    }    
    
    /**
     * Test getLastBusinessWeekRange method
     * 
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function testGetLastBusinessWeekRangeMonSat($today, $firstDay, $lastDay)
    {        
        $result = $this->_helper()->getLastBusinessWeekRange(new Zend_Date($today, Zend_Date::ISO_8601));                     
        $this->assertEquals(array('from'=>$firstDay, 'to'=>$lastDay), $result->getData() );                
    }    
    
    /**
     * Test getLastWeekRange method when monday is first day
     * 
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function testGetLastWeekRangeMon($today, $firstDay, $lastDay)
    {        
        $result = $this->_helper()->getLastWeekRange(new Zend_Date($today,  Zend_Date::ISO_8601));
        $this->assertEquals(array('from'=>$firstDay, 'to'=>$lastDay), $result->getData() );        
    }    
    
    /**
     * Test getLastWeekRange method when sunday is first day
     * 
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function testGetLastWeekRangeSun($today, $firstDay, $lastDay)
    {        
        $result = $this->_helper()->getLastWeekRange(new Zend_Date($today,  Zend_Date::ISO_8601));
        $this->assertEquals(array('from'=>$firstDay, 'to'=>$lastDay), $result->getData() );        
    }    
    
    /**
     * Test getLastWeekLabel method when sunday is first day
     * 
     * @loadFixture
     */    
    public function testGetLastWeekLabelSun()
    {
        $this->assertEquals('Last week (Sun - Sat)', $this->_helper()->getLastWeekLabel());
    }
    
    /**
     * Test getLastWeekLabel method when monday is first day
     * 
     * @loadFixture
     */    
    public function testGetLastWeekLabelMon()
    {
        $this->assertEquals('Last week (Mon - Sun)', $this->_helper()->getLastWeekLabel());
    }
}