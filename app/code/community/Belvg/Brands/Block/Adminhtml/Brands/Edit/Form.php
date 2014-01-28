<?php

/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Brands
 * @author Pavel Novitsky <pavel@belvg.com>
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

/**
 * Tab edit form
 */
class Belvg_Brands_Block_Adminhtml_Brands_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init edit form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $params = array();
        $params['id'] = (int)$this->getRequest()->getParam('id', 0);

        if ($store_id = (int)$this->getRequest()->getParam('store', Mage_Core_Model_App::ADMIN_STORE_ID)) {
            $params['store'] = $store_id;
        }

        $form = new Varien_Data_Form(array(
                        'id' => 'edit_form',
                        'action' => $this->getUrl('*/*/save', $params),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data'
                        )
        );

        $form->setUseContainer(TRUE);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}