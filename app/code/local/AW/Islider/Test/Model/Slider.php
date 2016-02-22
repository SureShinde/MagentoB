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
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

/**
 * phpunit --coverage-html ./report UnitTests
 */
class AW_Islider_Test_Model_Slider extends EcomDev_PHPUnit_Test_Case {
    /**
     * Model loading test
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function testAfterLoad($passId) {
        $sliderModel = Mage::getModel('awislider/slider')->load($passId);
        
        $this->assertEquals(
            explode(',', $this->expected('id'.$passId)->getStore()),
            $sliderModel->getData('store')
        );
    }
    
    /**
     * Model loading test
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function testAfterSave($passId, $store) {
        $sliderModel = Mage::getModel('awislider/slider');
        $sliderModel->setId($passId);
        $sliderModel->setData(array(
            'name' => '1',
            'block_id' => '1',
            'store' => explode(',', $store),
            'autoposition' => 1,
            'switch_effect' => '1',
            'width' => 1,
            'height' => 1
        ));
        $sliderModel->save();
        
        unset($sliderModel);
        $sliderModel = Mage::getModel('awislider/slider')->load($passId);
        $this->assertEquals(
            $this->expected('id'.$passId)->getStore(),
            implode(',', $sliderModel->getData('store'))
        );
        
        $sliderModel->delete();
    }
}
