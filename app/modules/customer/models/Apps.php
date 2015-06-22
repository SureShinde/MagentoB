<?php
/**
 * Apps models
 */
namespace Frontend\Core\Models;


use Phalcon\Db\RawValue;
use Phalcon\Mvc\Model\Validator\Email;

class Apps extends \Frontend\Core\Models\BaseModel
{
	/**
	 *
	 * @var string
	 */
	public $name;
	/**
	 *
	 * @var string
	 */
	public $websites;
	/**
	 *
	 * @var string
	 */
	public $ips;
	/**
	 *
	 * @var integer
	 */
	public $status;
	/**
	 *
	 * @var integer
	 */
	public $roleName;
	/**
	 *
	 * @var string
	 */
	public $email;
	/**
	 *
	 * @var string enum of website, vendor, thirdparty, brand
	 */
	public $type;


	/**
	 *
	 * @var string
	 */
	protected $id;
	/**
	 *
	 * @var string
	 */
	protected $secret;

	/**
	 * @param $id
	 * @param $secret
	 * @return bool|\Phalcon\Mvc\Model
	 */
	public static function getAuth($id, $secret)
	{
		if ($model = self::findFirst(['conditions' => 'id = ?1 AND secret = ?2 and status=1', 'bind' => [1 => $id, 2 => $secret]]))
		{
			return $model;
		}
		return false;
	}

	public function initialize()
	{
		parent::initialize();
		//Setup a many-to-many relation to Parts through RobotsParts
		$this->hasMany("id", '\Api\Dashboard\Models\AppCustomers', "appId", [
			'alias' => 'AppCustomer',
			"foreignKey" => [
				"message" => "The appId does not exist on the Apps model"
			]
		]);
		$this->hasMany("roleName", '\Api\Core\Models\AppRoles', "name", [
			'alias' => 'Roles',
			"foreignKey" => [
				"message" => "The Role doesn't exist on Apps model"
			]
		]);
		$this->useDynamicUpdate(true);
	}

	public function beforeValidation()
	{
		$this->status = $this->status == null ? new RawValue('default') : $this->status;
		$this->secret = md5(rand(0, 1000));
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

	public function beforeValidationOnUpdate()
	{
	}

//	public function getSource()
//    {
//        return 'apps';
//    }

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

	public function beforeValidationOnCreate()
	{
		$this->id = md5(rand(0, 1000));
		$this->secret = md5(rand(0, 1000));
	}

	public function regeneratedSecret()
	{
		if (!empty($this->id)) {
			$this->secret = md5(rand(0, 1000));
			if ($this->save()) {
				$messages = [];
				foreach ($this->getMessages() as $message) {
					$messages = ', ' . $message->getMessage();
//					echo "Field: ", $message->getField();
//					echo "Type: ", $message->getType();
				}
				throw new \Exception(implode(', ', $messages));
			}
		} else throw new \Exception('should existing record');
	}

	public function getACL()
	{
		$admin_role_id = $this->roleName;
		$acl_file = $this->_normalise($this->getDI()->get('config')->acl . "/");

		if (!file_exists($acl_file) && !mkdir($acl_file, 0777, 1)) {
			throw new \Exception('acl tmp cache folder cannot created');
		}

		$acl_file .= "acl_$admin_role_id.data";

		if (!is_file($acl_file) || !is_readable($acl_file)) {
			$rs = $this->getRoles()->toArray();
			$acl = new \Phalcon\Acl\Adapter\Memory;
//			die(var_dump($rs));
			if (count($rs) > 0) {
				$acl->setDefaultAction(\Phalcon\Acl::DENY);
				$rolename = $rs[0]['name'];
				$acl->addRole($rolename);
//				$rs			= AdminRules::findByAdminRoleId($admin_role_id)->toArray();
				for ($i = 0; $i < count($rs); $i++) {
					$resource_id = new \Phalcon\Acl\Resource($rs[$i]['privilege']);
					$privileges = trim(strtolower($rs[$i]['access'])) == 'all' ? ['add', 'edit', 'delete', 'list', 'other'] : explode(',', $rs[$i]['access']);

					if (!in_array('other', $privileges))
						$privileges[] = 'other';

					$acl->addResource($resource_id, $privileges);
					foreach ($privileges as $k => $v) {
						$acl->allow($rolename, $rs[$i]['privilege'], $v);
					}

				}
				file_put_contents($acl_file, serialize($acl));
				chmod($acl_file, 0666);
			} else {
				if ($this->isWebsite()) {
					$acl->setDefaultAction(\Phalcon\Acl::ALLOW);
				}
			}
		} else {
			$acl = unserialize(file_get_contents($acl_file));
		}
		//echo "<pre>";print_r($acl);die();
		return $acl;
	}

	protected function _normalise($path, $encoding = "UTF-8")
	{

		// Attempt to avoid path encoding problems.
		$path = iconv($encoding, "$encoding//IGNORE//TRANSLIT", $path);

		// Process the components
		$parts = explode('/', $path);
		$safe = [];
		foreach ($parts as $idx => $part) {

			if (($idx > 0 && trim($part) == "") || $part == '.') {
				continue;
			} elseif ('..' == $part) {
				array_pop($safe);
				continue;
			} else {
				$safe[] = $part;
			}
		}

		// Return the "clean" path
		$path = implode(DIRECTORY_SEPARATOR, $safe);
		return $path;
	}

	/**
	 * @return mixed
	 */
	public function getRoles()
	{
		$manager = $this->getDI()->get('modelsManager');
		$builder = $manager->createBuilder();
		$rs = $builder
			->addFrom('Api\Core\Models\AppRoles', 'r')
			->where('r.name = ?1', [1 => $this->roleName])
			->join('Api\Core\Models\AppPrivileges', 'r.privilegeId=p.id', 'p', 'inner')
//		;
//
//		$builder1 = clone $builder;
//
//		$rs = $builder->columns('p.*,v.*,i.*')
//			->orderBy($parameter['order'])
//			->limit($parameter['limit'])
//			->offset($parameter['offset'])
			->getQuery()->execute();
		return $rs;
	}

	/**
	 * @return bool
	 */
	public function isWebsite()
	{
		if ($this->id)
			return AppCustomers::count(['conditions' => 'appId = ?1 AND level = ?2', 'bind' => [1 => $this->id, 2 => 'administrator']]) ? true : false;
		return false;
	}

	/**
	 * Independent Column Mapping.
	 */
	public function columnMap()
	{
		return [
			'id' => 'id',
			'secret' => 'secret',
			'name' => 'name',
			'websites' => 'websites',
			'ips' => 'ips',
			'status' => 'status',
			'role_name' => 'roleName',
			'email' => 'email',
			'type' => 'type'
		];
	}

}
