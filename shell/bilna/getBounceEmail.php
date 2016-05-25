<?php
require_once dirname(__FILE__) . '/../../lib/aws-autoloader.php';
use Aws\Sqs\SqsClient;

class getBounceEmail extends Mage_Shell_Abstract {
    protected $resource;
    protected $write;

    public function init() {
        $this->resource = Mage::getSingleton('core/resource');
        $this->write = $this->resource->getConnection('core_write');
    }

	function run() {
		try {
			$client = SqsClient::factory(array(
			    'profile' => 'default',
			    'version' => 'latest',
			    'region' => 'us-east-1'
			));
			
			$result = $client->receiveMessage(array(
			    'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/224198310470/BouncedEmail',
			    'MessageAttributeNames' => array('All'),
			    'MaxNumberOfMessages' => 10,
			));

			$data = array();
			foreach ($result["Messages"] as $key => $value) {
				$body = json_decode($value["Body"]);
				$bodyMessage = json_decode($body->Message);
				if($bodyMessage->bounce->bounceType == "Permanent") {
					$data[] = array("ReceiptHandle" => $value["ReceiptHandle"], "emailAddress" => $bodyMessage->bounce->bouncedRecipients[0]->emailAddress, "bounceType" => $bodyMessage->bounce->bounceType, "diagnosticCode" => $bodyMessage->bounce->bouncedRecipients[0]->diagnosticCode);

					$sql = sprintf("
			            INSERT INTO `message`(`email`)
			            VALUES('%s');
			        ", $$bodyMessage->bounce->bouncedRecipients[0]->emailAddress);
			        
			        if ($this->write->query($sql)) {
			            $this->logProgress('Success running SQL statement : ' . $sql);
			            $client->deleteMessage(array(
						    'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/224198310470/BouncedEmail',
						    'ReceiptHandle' => $value["ReceiptHandle"],
						));
			        }

				}
			}

		} catch (Exception $e) {
		    die('Error releasing job back to queue ' . $e->getMessage());
		}	
	}
}

$shell = new getBounceEmail();
$shell->init();
$shell->run();
