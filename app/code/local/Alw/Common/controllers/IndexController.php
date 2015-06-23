<?php
class Alw_Common_IndexController extends Mage_Core_Controller_Front_Action {

	public  function indexAction() {
	//echo "Success";
	echo Mage::app()->getLayout()->createBlock('core/template')->setTemplate('extra_pages/vistor_page.phtml')->toHtml();
	}

	public  function productpageAction() {
	echo Mage::app()->getLayout()->createBlock('catalog/product')->setTemplate('extra_pages/product_details.phtml')->toHtml();
	//echo Mage::app()->getLayout()->createBlock('catalog/product_view')->setTemplate('catalog/product/view.phtml')->toHtml();
	}
	
	public function sendAction()
	{
		Mage::getModel('common/observer')->updateDeal();
	}	
	
	public  function vistorSaveAction() {
		$flag  = 0;
		$data = $this->getRequest()->getPost();
		if($data){		
		
		/* Email send to Admin*/
		$emailTemplate  = Mage::getModel('core/email_template')->loadDefault('vistor_email_template');
		//Create an array of variables to assign to template
		$emailTemplateVariables = array();
		$emailTemplateVariables['vistor_name'] = ucwords($data['name']);
		$emailTemplateVariables['brand'] = ucwords($data['brand']);
		$emailTemplateVariables['parfume'] = ucwords($data['parfume']);
		$emailTemplateVariables['user_email'] =$data['email'];
		
		
		$emailTemplateVariables['sender_name'] = Mage::getStoreConfig('trans_email/ident_general/name');
		$emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
		$emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
		
		$emailTemplate->setTemplateSubject('Inquiry of Merk and Parfume');
		//$emailTemplate->send($data['email'], $data['name'], $emailTemplateVariables);
		
		try{
			$emailTemplate->send(Mage::getStoreConfig('trans_email/ident_general/email'), Mage::getStoreConfig('trans_email/ident_general/name'), $emailTemplateVariables);
			Mage::getSingleton('core/session')->addSuccess($this->__('Your Enquiry has sent to Admin.'));
		
		}catch(Exception $e){
		Mage::getSingleton('core/session')->addError($e->getMessage());
		Mage::getSingleton('core/session')->setUserData($data);
		}
		$this->_redirect('/');	
		}
	
	}
}
?>