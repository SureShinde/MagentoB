<?php
require_once dirname(__FILE__) . '/../../lib/aws-autoloader.php';
use Aws\Sqs\SqsClient;

function bouncePermanent() {
	try {
		$client = SqsClient::factory(array(
		    'profile' => 'default',
		    'version' => 'latest',
		    'region' => 'us-east-1'
		));
		
		$QueueUrl = $client->receiveMessage(array(
		    'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/224198310470/BouncedEmail',
		    'MessageAttributeNames' => array('All'),
		    'MaxNumberOfMessages' => 10,
		));

		$data = array();
		foreach ($QueueUrl["Messages"] as $key => $value) {
			$body = json_decode($value["Body"]);
			$Message = json_decode($body->Message);
			if($Message->bounce->bounceType == "Permanent") {
				$data[] = array("ReceiptHandle" => $value["ReceiptHandle"], "emailAddress" => $Message->bounce->bouncedRecipients[0]->emailAddress, "bounceType" => $Message->bounce->bounceType, "diagnosticCode" => $Message->bounce->bouncedRecipients[0]->diagnosticCode);
			}
		}
		
		var_dump($data);

	} catch (Exception $e) {
	    die('Error releasing job back to queue ' . $e->getMessage());
	}	
}

bouncePermanent();

?>