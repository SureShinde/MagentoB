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

class AW_Islider_Block_Representations_Default_Block extends AW_Islider_Block_Representations_Common {
    public function getContainerStyleString() {
        $_styleString = '';
        if($this->getWidth())
            $_styleString .= sprintf('width: %spx;', $this->getWidth());
        if($this->getHeight())
            $_styleString .= sprintf('height: %spx;', $this->getHeight());
        return $_styleString;
    }

    public function getAutohideNavigation() {
        return $this->getAWISBlockParam('nav_autohide');
    }

    public function getAnimationSpeed() {
        return $this->getAWISBlockParam('animation_speed');
    }

    public function getFirstFrameTimeout() {
        return $this->getAWISBlockParam('first_timeout');
    }

    public function getHeight() {
        return $this->getAWISBlockParam('height');
    }

    public function getWidth() {
        return $this->getAWISBlockParam('width');
    }

    public function getSwitchEffect() {
        return $this->getAWISBlockParam('switch_effect');
    }

    public function getAWISBlockParam($key) {
        if($this->getAWISBlock())
            return $this->getAWISBlock()->getData($key);
    }

    protected function _beforeToHtml() {
        $this->setTemplate('aw_islider/representations/default/block.phtml');
        return parent::_beforeToHtml();
    }
}
