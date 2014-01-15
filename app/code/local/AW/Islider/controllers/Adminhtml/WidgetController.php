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

class AW_Islider_Adminhtml_WidgetController extends Mage_Adminhtml_Controller_Action {
    public function blockchooserAction() {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $blocksGrid = $this->getLayout()->createBlock('awislider/adminhtml_widget_blockchooser', '', array(
            'id' => $uniqId,
        ));
        $_blockCSS = $this->getLayout()->createBlock('adminhtml/template');
        $_blockCSS->setTemplate('aw_islider/widget/sliders.phtml')
            ->setGridId($uniqId);
        $this->getResponse()->setBody($blocksGrid->toHtml().$_blockCSS->toHtml());
    }
}
