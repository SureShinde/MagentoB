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


class AW_Afptc_Block_Adminhtml_Rules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'awafptc';
        $this->_controller = 'adminhtml_rules';
        
         $this->_formScripts[] = "
                
                 function saveAndContinueEdit(url) {
                           editForm.submit(
                                url.replace(/{{tab_id}}/ig,awafptc_info_tabsJsTabs.activeTab.id)
                           );
                  }
                  document.observe('dom:loaded', function() {                  
                    awAfptcGridJsObject.reloadParams = {}; 
                    awAfptcGridJsObject.reloadParams.checkedValues = '" . Mage::registry('awafptc_rule')->getProductId() . "'
                        
                        var element = $('general_show_popup');
                        var index = element[element.selectedIndex].value;                           
                        if(index != 1) {
                             $('general_show_once').up('tr').style.display = 'none'; 
                             $('general_priority').up('tr').style.display = 'none';  
                        }

                         Event.observe('general_show_popup', 'change', function(event) {                    
                            var element = Event.element(event);
                            var index = element[element.selectedIndex].value;                         
                            if(index == 1) {
                                $('general_show_once').up('tr').style.display = null; 
                                $('general_priority').up('tr').style.display = null; 
                            }
                            else {
                                $('general_show_once').up('tr').style.display = 'none';   
                                $('general_priority').up('tr').style.display = 'none'; 
                            }
                        }); 
                  });
             ";
        
        Mage::register('aw-afptc-session-data', Mage::getSingleton('adminhtml/session')->getFormActionData(true), true);
 
        parent::__construct();
    }

    public function getHeaderText()
    {
        $auction = Mage::registry('awafptc_rule');
        if ($auction->getId()) {
            if ($auction->getName()) {
                return $this->__("Edit Rule '%s'", $this->htmlEscape($auction->getName()));
            }
            return $this->__("Edit Rule #'%s'", $this->htmlEscape($auction->getId()));
        } else {
            return $this->__('Create New Rule');
        }
    }

    protected function _prepareLayout()
    {
        $this->_addButton('save_and_continue', array(
            'label' => $this->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
            'class' => 'save'
                ), 10);

        parent::_prepareLayout();
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
                    '_current' => true,
                    'back' => 'edit',
                    'tab' => '{{tab_id}}'
        ));
    }

}
