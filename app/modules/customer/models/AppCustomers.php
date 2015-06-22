<?php
/**
 * AppCustomers models
 */
namespace Frontend\Core\Models;

use Phalcon\Mvc\Model\Validator\Email as Email;

/**
 * Class AppCustomers
 * @package Api\Core\Models
 */
class AppCustomers extends \Frontend\Core\Models\BaseModel
{

	/**
	 *
	 * @var string
	 */
	public $appId;
	/**
	 *
	 * @var string
	 */
	public $email;
	/**
	 *
	 * @var integer
	 */
	public $name;
	/**
	 *
	 * @var integer
	 */
	public $status;
	/**
	 *
	 * @var string
	 */
	public $level;
	/**
	 *
	 * @var string
	 */
	public $password;
	/**
	 *
	 * @var string
	 */
	public $deletedDate;
	/**
	 *
	 * @var integer
	 */
	public $deletedBy;
	/**
	 *
	 * @var integer
	 */
	public $createdBy;
	/**
	 *
	 * @var integer
	 */
	protected $id;
	/**
	 *
	 * @var string
	 */
	protected $createdDate;


	/**
	 * init relationship
	 */
	public function initialize()
	{
		parent::initialize();
		//Setup a many-to-many relation to Parts through RobotsParts
		$this->belongsTo("appId", "\Api\Core\Models\Apps", "id", [
			'alias' => 'app',
			"foreignKey" => [
				"message" => "The appId does not exist on the Apps model"
			]
		]);
		//$this->useDynamicUpdate(true);
	}

//    public function getSource()
//    {
//        return 'app_customers';
//    }

	/**
	 *
	 */
	public function beforeValidation()
	{
		$this->status = $this->status == null ? new \Phalcon\Db\RawValue('default') : $this->status;
		if (strpos($this->email, '+') && strpos($this->email, '+') < strpos($this->email, '@')) {
			$emails = explode('+', $this->email);
			if (count($emails) > 1) {
				$domains = explode('@', $emails[count($emails) - 1], 2);
				if (count($domains) > 1) {
					$this->email = $emails[0] . '@' . $domains[0];
				}
			}
		}

	}

	/**
	 *
	 */
	public function beforeValidationOnCreate()
	{
		$this->createdDate = $this->createdDate == null ? new \Phalcon\Db\RawValue('default') : $this->createdDate;
	}

	public function beforeValidationOnUpdate()
	{
	}


	/**
	 * @return bool
	 */
	public function validation()
	{

		$this->validate(
			new Email(
				[
					'field' => 'email',
					'required' => true,
				]
			)
		);
		if ($this->validationHasFailed() == true) {
			return false;
		}
		return true;
	}

	/**
	 * Independent Column Mapping.
	 */
	public function columnMap()
	{
		return [
			'id' => 'id',
			'email' => 'email',
			'app_id' => 'appId',
			'name' => 'name',
			'status' => 'status',
			'level' => 'level',
			'password' => 'password',
			'created_date' => 'createdDate',
			'deleted_date' => 'deletedDate',
			'deleted_by' => 'deletedBy',
			'created_by' => 'createdBy'
		];
	}

}
