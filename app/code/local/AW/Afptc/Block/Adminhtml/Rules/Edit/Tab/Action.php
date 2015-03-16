<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Afptc
 * @version    1.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Afptc_Block_Adminhtml_Rules_Edit_Tab_Action extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $rule = Mage::registry('awafptc_rule');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('action_');
        
    
        $fieldset = $form->addFieldset('action_fieldset', array(
            'legend'=> $this->__('Action')
        ));
        
        if(!$rule->getId())
            $rule->setDiscount(100);      
        $fieldset->addField('discount', 'text', array(
            'label' => $this->__('Discount Amount Applied to Product, %'),
            'title' => $this->__('Discount Amount Applied to Product, %'),
            'required' => true,
            'name' => 'discount'
        ));
        
          $fieldset->addField('free_shipping', 'select', array(
            'label' => $this->__('Free Shipping'),
            'title' => $this->__('Free Shipping'),
            'name' => 'free_shipping',
            'options' => array(
                '1' => $this->__('Yes'),
                '0' => $this->__('No'),
            ),
        ));
       
         
          $sessionData = Mage::registry('aw-afptc-session-data');
          $form->addFieldset('awafptc_grid_fieldset', array('class' => 'aw-afptc-grid-products', 'legend' => $this->__('Action Product')))  
              ->addField('awafptc_grid_product', 'select', array(        
                    'name' => 'awafptc_grid_product',
                    'formdata' => $sessionData ? $sessionData : $rule,
          ))->setRenderer($this->getLayout()->createBlock('awafptc/adminhtml_rules_edit_renderer_products')); 
 
          if($sessionData) {
             $form->setValues($sessionData->getData());
          }
          else {
             $form->setValues($rule->getData()); 
          }
          
   
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
