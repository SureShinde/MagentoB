<?php
require_once dirname(__FILE__) . '/../../lib/aws-autoloader.php';
require_once dirname(__FILE__) . '/../abstract.php';
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
			
			while (true) {
				$result = $client->receiveMessage(array(
				    'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/224198310470/BouncedEmail',
				    'MessageAttributeNames' => array('All'),
				    'MaxNumberOfMessages' => 10,
				));

				if (!$result["Messages"]) {
					break;
				}
			
				foreach ($result["Messages"] as $key => $value) {
					$body = json_decode($value["Body"]);
					$bodyMessage = json_decode($body->Message);
					if($bodyMessage->bounce->bounceType != "Permanent") {
						$client->deleteMessage(array(
						    'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/224198310470/BouncedEmail',
						    'ReceiptHandle' => $value["ReceiptHandle"],
						));
						continue;
					}
					$sql = sprintf("
			            INSERT INTO `bounced_email`(email) VALUES('%s');
			        ", $bodyMessage->bounce->bouncedRecipients[0]->emailAddress);
			        
			        if ($this->write->query($sql)) {
			            echo 'Success running : ' . $sql . "\n";
			            $client->deleteMessage(array(
						    'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/224198310470/BouncedEmail',
						    'ReceiptHandle' => $value["ReceiptHandle"],
						));
			        }
				}
			}

		} catch (Exception $e) {
		    die('Error : ' . $e->getMessage());
		}	
	}
}

$shell = new getBounceEmail();
$shell->init();
$shell->run();
