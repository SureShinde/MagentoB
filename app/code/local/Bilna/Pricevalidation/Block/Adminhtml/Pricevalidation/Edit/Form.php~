<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('formbuilder_tabs');
        //$this->setDestElementId('edit_form');
        //$this->setTitle('Form Information');
        $this->setTitle(Mage::helper('bilna_pricevalidation')->__('Form Information'));
    }

    protected function _prepareForm()
    {
      $form = new Varien_Data_Form(array(
          'id' => 'edit_form',
          'action' => $this->getUrl('*/*/save', array('profile_id' => $this->getRequest()->getParam('profile_id'))),
          'method' => 'post',
          'enctype' => 'multipart/form-data'
       ));

      $form->setUseContainer(true);
      $this->setForm($form);
      return parent::_prepareForm();
    }
}