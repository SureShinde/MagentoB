<?php

class Bilna_Staticarea_Block_Adminhtml_Manage_Edit_Tab_Contents_Ajaxform_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $_form = new Varien_Data_Form(array(
            'id' => 'edit_content_form',
            'method' => 'post'
        ));
        $this->setForm($_form);

        $_formData = Mage::helper('staticarea')->getFormDataContent($this->getRequest()->getParam('id'));
        
        if(is_object($_formData) && $_formData->getData()) {
            $_formData->setData(array(
                'status' => $_formData->getData('status'),
                'staticarea_id' => $_formData->getData('staticarea_id'),
                'content' => $_formData->getData('content'),
                'active_from' => !$_formData->getData('active_from') || @strtotime ($_formData->getData('active_from')) < 1 ? '' : $_formData->getData('active_from'),
                'active_to' => !$_formData->getData('active_to') || @strtotime ($_formData->getData('active_to')) < 1 ? '' : $_formData->getData('active_to'),
                'sort_order' => $_formData->getData('order'),
                'url' => $_formData->getData('url'),
                'url_action' => $_formData->getData('url_action')
            ));
        } else {
            $_formData = array(
                'staticarea_id' => $this->getRequest()->getParam('pid'),
                'sort_order' => 0
            );
        }

        $_fieldset = $_form->addFieldset('general_fieldset', array(
            'legend' => $this->__('General Information')
        ));

        $_fieldset->addField('staticarea_id', 'hidden', array(
            'name' => 'staticarea_id',
            'value' => $this->getRequest()->getParam('pid')
        ));

        /*if($this->getRequest()->getParam('id')) {
            $_fieldset->addField('image_id', 'hidden', array(
                'name' => 'image_id'
            ));
        } else {
            $_fieldset->addField('image_tmp_id', 'hidden', array(
                'name' => 'image_tmp_id'
            ));
        }*/

        $_fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => $this->__('Status'),
            'values' => Mage::getModel('staticarea/source_status')->toOptionArray(),
            'required' => true
        ));

        $_fieldset->addField('active_from', 'date', array (
            'label' => Mage::helper('staticarea')->__('Content Start Date'),
            'title' => Mage::helper('staticarea')->__('Content Start Date'),
            'name' => 'active_from',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => "dd-MMM-yyyy",
            'value' => 'active_from',
            'required' => true,
        ));

        $_fieldset->addField('active_to', 'date', array (
            'label' => Mage::helper('staticarea')->__('Content End Date'),
            'title' => Mage::helper('staticarea')->__('Content End Date'),
            'name' => 'active_to',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => "dd-MMM-yyyy",
            'value' => 'active_to',
            'required' => true,
        ));

        $_fieldset->addField('url', 'text', array(
            'name' => 'url',
            'label' => $this->__('URL'),
            'required' => true
        ));

        $_fieldset->addField('url_action', 'text', array(
            'name' => 'url_action',
            'label' => $this->__('URL Action'),
            'required' => true
        ));

        try {
            $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $config->setData(
                Mage::helper('blog')->recursiveReplace(
                    '/staticarea/',
                    '/' . (string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName') . '/',
                    $config->getData()
                )
            );
        } catch (Exception $ex) {
            $config = null;
        }

        $_fieldset->addField(
            'content',
            'editor',
            array(
                 'name'   => 'content',
                 'label'  => Mage::helper('staticarea')->__('Content'),
                 'title'  => Mage::helper('staticarea')->__('Content'),
                 'style'  => 'width:350px; height:200px;',
                 'config' => $config,
                 'wysiwyg'   => true
            )
        ); 
        
        $_fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => $this->__('Sort Order'),
            'required' => true
        ));

        $_form->setValues($_formData);

        return parent::_prepareForm();
    }
}
