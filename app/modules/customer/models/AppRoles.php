<?php
/**
 * AppRoles models
 */
namespace Frontend\Core\Models;

class AppRoles extends \Frontend\Core\Models\BaseModel
{

	/**
	 *
	 * @var integer
	 */
	protected $id;

	/**
	 *
	 * @var string
	 */
	public $name;

	/**
	 *
	 * @var integer
	 */
	public $privilegeId;

	/**
	 *
	 * @var integer
	 */
	public $status;

	public function beforeValidationOnUpdate()
	{
	}


//	public function getSource()
//	{
//		return 'app_roles';
//	}

	/**
	 * Independent Column Mapping.
	 */
	public function columnMap()
	{
		return [
			'id' => 'id',
			'name' => 'name',
			'privilege_id' => 'privilegeId',
			'status' => 'status'
		];
	}

}
