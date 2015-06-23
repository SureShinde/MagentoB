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

class AW_Islider_LinkController extends Mage_Core_Controller_Front_Action {
    const AWIS_COOKIE_PREFIX = 'awislider-images-';

    protected function outAction() {
        if($this->getRequest()->getParam('sid')) {
            $_image = Mage::getModel('awislider/image')->load($this->getRequest()->getParam('sid'));
            if($_image->getData()) {
                $_image->setData('clicks_total', $_image->getData('clicks_total')+1);
                if(!isset($_COOKIE[self::AWIS_COOKIE_PREFIX.$_image->getData('id')])) {
                    setcookie(self::AWIS_COOKIE_PREFIX.$_image->getData('id'), '1', strtotime('+1 year', time()));
                    $_image->setData('clicks_unique', $_image->getData('clicks_unique')+1);
                }
                $_image->save();
                return $this->getResponse()->setRedirect($_image->getData('url'));
            }
        }
        return $this->_redirectReferer();
    }
}
