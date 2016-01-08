<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * @var model of form
     */
    protected $model;

    /**
    * @var Identifier of form persistance
     */
    protected $formId;

    public function __construct() {
      parent::__construct();
      $this->model = Mage::getModel('bilna_formbuilder/form');
      $this->formId = (int) $this->getRequest()->getParam('id');
    }
    
    protected function _prepareForm() {

  		$formbuilder = Mage::registry('formbuilder_form');
      $form = new Varien_Data_Form();
      /*$form = new Varien_Data_Form(array(
          'id' => 'edit_input',
          'action' => $this->getUrl('*//*/save', array('id' => $this->getRequest()->getParam('id'))),
          'method' => 'post',
      ));*/
  		$form->setHtmlIdPrefix('formbuilder_');
  		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('General')));
  		$data = $this->model->findById($this->formId);
      $data['form_title'] = $data["title"];

  		/*foreach($data->getData() as $key=>$value){
  		echo $key."---".$value."<br>";
  		}die;*/

  		$fieldset->addField('form_title', 'text', array(
  		   'label'	=> 'Title',
  		   'name'   => 'form_title',
  		   //'value'=> $data["title"], //dimatikan karena dibwh sdh ada $form->setValues($data);
      ));

  		$fieldset->addField('url', 'text', array(
         'label'  => 'URL',
         'name'   => 'url',
         //'value'=> $data["url"],
      ));

      $fieldset->addField('success_message', 'text', array(
        'label' => 'Success message',
        'name' => 'success_message'
      ));

      $fieldset->addField('static_success', 'text', array(
        'label' => 'Static success',
        'name' => 'static_success'
      ));

      $fieldset->addField('static_info', 'text', array(
        'label' => 'Static info',
        'name' => 'static_info'
      ));

      $fieldset->addField('static_failed', 'text', array(
        'label' => 'Static failed',
        'name' => 'static_failed'
      ));

      $fieldset->addField('force_flow', 'text', array(
        'label' => 'Force flow',
        'name' => 'force_flow'
      ));

      $fieldset->addField('termsconditions', 'text', array(
        'label' => 'Terms & Conditions',
        'name' => 'termsconditions'
      ));

      $fieldset->addField('freeproducts', 'text', array(
        'label' => 'Free products',
        'name' => 'freeproducts'
      ));

      $fieldset->addField('class', 'text', array(
        'label' => 'Class',
        'name' => 'class'
      ));

      $fieldset->addField('button_text', 'text', array(
        'label' => 'Button text',
        'name' => 'button_text'
      ));

      $fieldset->addField('sent_email', 'select', array(
        'label' => 'Sent email',
        'name' => 'sent_email',
        'values' => array(
          array(
             'value' => 1,
             'label' => Mage::helper('bilna_formbuilder')->__('Yes'),
           ),
           array(
               'value' => 0,
               'label' => Mage::helper('bilna_formbuilder')->__('No'),
           ),
        )
      ));

      $fieldset->addField('email_id', 'text', array(
        'label' => 'Email id',
        'name' => 'email_id'
      ));

      $fieldset->addField('success_redirect', 'select', array(
        'label' => 'Success Redirect',
        'name' => 'success_redirect',
        'values'     => array(
                       array(
                           'value' => 1,
                           'label' => Mage::helper('bilna_formbuilder')->__('Yes'),
                       ),
                       array(
                           'value' => 0,
                           'label' => Mage::helper('bilna_formbuilder')->__('No'),
                       ),
                      )
        ));

      $fieldset->addField('url_success', 'text', array(
        'label' => 'url_success',
        'name' => 'url_success'
      ));

      $fieldset->addField('email_share_apps', 'select', array(
        'label' => 'Email share',
        'name' => 'email_share_apps',
        'values' => array(
          array(
             'value' => 1,
             'label' => Mage::helper('bilna_formbuilder')->__('Yes'),
           ),
           array(
               'value' => 0,
               'label' => Mage::helper('bilna_formbuilder')->__('No'),
           ),
        )
      ));

      $fieldset->addField('social_title', 'text', array(
        'label' => 'Social title',
        'name' => 'social_title'
      ));

      $fieldset->addField('social_desc', 'text', array(
        'label' => 'Social desc',
        'name' => 'social_desc'
      ));

      $fieldset->addField('social_image', 'text', array(
        'label' => 'Social image',
        'name' => 'social_image'
      ));

      $fieldset->addField('fue', 'text', array(
        'label' => 'Fue',
        'name' => 'fue'
      ));

  		$outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

  		$fieldset->addField('active_from', 'date', array(
         'label'  => $this->__('Aktif Dari'),
         'name'   => 'active_from',
  			 'title'	=> $this->__('Aktif Dari'),
  			 'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
         'input_format' => $outputFormat,
  			 'format'	=> $outputFormat,
         'time' => true,
         //'value'=> $data["active_from"],
      ));

  		$fieldset->addField('active_to', 'date', array(
         'label'  => $this->__('Aktif Sampai'),
         'name'   => 'active_to',
  			 'title'	=> $this->__('Aktif Sampai'),
  			 'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
  			 'format'	=> $outputFormat,
         'input_format' => $outputFormat,
         'time' => true,
         //'value'=> $data["active_to"],
      ));

  		$fieldset->addField('status', 'select', array(
         'label'			=> $this->__('Status'),
         //'class'   	=> 'required-entry',
         //'required'	=> true,
         'name'      	=> 'status',
         //'value'    => $data["status"],
         //'note'    	=> Mage::helper('bilna_formbuilder')->__('Status Note:</br>0=Enabled</br>1=Disabled'),
  			 'values'     => array(
                       array(
                           'value' => 0,
                           'label' => Mage::helper('bilna_formbuilder')->__('Enabled'),
                       ),
                       array(
                           'value' => 1,
                           'label' => Mage::helper('bilna_formbuilder')->__('Disabled'),
                       ),
                   		 ),
         //'onchange'	=> 'checkStatusEnabled()'
      ));

      $form->setUseContainer(true);
      $form->setValues($data);
      $this->setForm($form);
      return parent::_prepareForm();
	}
}
