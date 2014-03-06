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

class AW_Advancedreports_Test_Block_Store_Switcher extends EcomDev_PHPUnit_Test_Case
{     
    
    /**
     * Test AW_Advancedreports_Block_Store_Switcher::clearParams
     * 
     * @dataProvider dataProvider
     * @param array $params 
     */
    public function testClearParams($params)
    {
        $obj = new AW_Advancedreports_Block_Store_Switcher();
        $result = $obj->clearParams($params);
        $this->assertEquals(NULL, $result['store']);
        $this->assertEquals(NULL, $result['group']);
        $this->assertEquals(NULL, $result['website']);        
    }
    
}