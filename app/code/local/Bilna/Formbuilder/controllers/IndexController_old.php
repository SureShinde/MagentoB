<?php
class Bilna_Formbuilder_IndexController extends Mage_Core_Controller_Front_Action 
{
	public function indexAction()
	{
		$this->loadLayout();     
		$this->renderLayout();
	}

	public function submitAction() 
	{
		$form_id = $this->getRequest()->getPost('form_id');
		$name = $this->getRequest()->getPost('name');
		$email = $this->getRequest()->getPost('email');
		$phone = $this->getRequest()->getPost('phone');
		$comment = $this->getRequest()->getPost('comment');
		$templateId = $this->getRequest()->getPost('template_email');

		$insertData = $this->_insertData();

		if ($insertData) {

			$urlform = $this->_backurl($form_id);
			$redirectPage = Mage::getBaseUrl().$urlform;
			$this->_prepareEmail($name, $email, $phone, $comment, $templateId);

			$message = "<div style='word-spacing:2px;'><p style='margin:0; padding:0;'>".$this->__('Terima kasih atas pertanyaan Anda. Tim ahli kami akan segera menjawab pertanyaan Anda.')."</p>"."<p style='margin:0; padding:0;'>".$this->__('Kami akan mengirimkan jawabannya ke email Anda atau Anda dapat juga melihat jawabannya di : ')."<a href='http://www.facebook.com/MyBilna' style='color: blue; text-decoration:none;'>"."<b>".$this->__('Facebook Bilna')."</b>"."</a> ".$this->__('atau')." <a href='http://www.bilna.com/blog/' style='color: blue; text-decoration:none;'>"."<b>".$this->__('Blog Bilna')."</b>"."</a>"."</p></div>";
			Mage::getSingleton('core/session')->addSuccess($message);

			$this->_redirectPage($redirectPage);

			}
			else 
			{ 
			echo "failed";
			}
	}

	private function _prepareEmail($name, $email, $phone, $comment, $templateId) 
	{
		$emailVars = array (
		'name_from' => $name,
		'email' => $email,
		'phone' => $phone,
		'comment' => $comment,
		'name_to' => 'CS Bilna',
		'email_to' => 'cs@bilna.com'
		);

		$this->_sendEmail($name, $email, $emailVars, $templateId);
	}

	// Redirect Page Function
	private function _redirectPage($url) {
	header("location:".$url);
	exit;
	}
	// End Redirect Page Function

	private function _sendEmail($name, $email, $emailVars, $templateId) 
	{
		$emailSender = array (
				'name' => $name,
				'email' => $email
				);
		$storeId = Mage::app()->getStore()->getId();
		$translate = Mage::getSingleton('core/translate');
		$sendEmail = Mage::getModel('core/email_template')
					->sendTransactional($templateId, $emailSender, $emailVars['email'], $emailVars['name'], $emailVars, $storeId);
		$translate->setTranslateInline(true);

		if ($sendEmail) 
		{
			return true;
		}

		return false;
	}

	private function _insertData() 
	{
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$submit_date=date("Y-m-d H:i:s");
		$dataArr = array (
			$this->getRequest()->getPost('form_id'),
			$this->getRequest()->getPost('name'),
			$this->getRequest()->getPost('email'),
			$this->getRequest()->getPost('phone'),
			$this->getRequest()->getPost('comment'),
			$submit_date
			);

		$sql = "insert into bilna_form_data (form_id, name, email, phone, comment, submit_date) values (?,?,?,?,?,?)";
		$query = $write->query($sql, $dataArr);

		if ($query)
		return true;
		else
		return false;
	}

	private function _backurl($form_id) 
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql        = "select url from bilna_form where id=".$form_id." group by url";
		$row       = $connection->fetchRow($sql);
		$result 	= $row['url'];
		return $result;
	}
}