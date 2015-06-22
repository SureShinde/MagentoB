<?php
/**
 * AppPrivileges models
 */
namespace Frontend\Core\Models;

class AppPrivileges extends \Frontend\Core\Models\BaseModel
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
	public $privilege;

	/**
	 *
	 * @var string
	 */
	public $description;

//	public function getSource()
//	{
//		return 'app_privileges';
//	}

	/**
	 * Independent Column Mapping.
	 */
	public function columnMap()
	{
		return [
			'id' => 'id',
			'privilege' => 'privilege',
			'description' => 'description'
		];
	}

	public function initialize()
	{
		parent::initialize();
		$this->belongsTo('app_id', 'Apps', 'id', ['foreignKey' => true]);
	}

}
