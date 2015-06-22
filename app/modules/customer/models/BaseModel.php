<?php
/**
 * abstract BaseModel
 * User: Akbar
 * Date: 11/26/2014
 * Time: 2:27 PM
 */

namespace Frontend\Core\Models;


use Frontend\Core\Libraries\DefaultConstant;
use Phalcon\Mvc\Model;

/**
 * Class BaseModel
 * @package Api\Core\Models
 * @property-read int id
 * @property bool status
 * @property-read string createdDate
 * @property-read string lastUpdatedDate
 * @method static \Api\Core\Models\BaseModel findFirstById(int $id)
 * @method static \Phalcon\Mvc\Model\Resultset findById(int $id)
 */
abstract class BaseModel extends Model
{

	/**
	 * @param null $parameters
	 * @return \Phalcon\Mvc\Model\Resultset
	 */
	public static function find($parameters = null)
	{

		//Convert the parameters to an array
		if (!is_array($parameters)) {
			$parameters = [$parameters];
		}

		//Check if a cache key wasn't passed
		//and create the cache parameters
		if (!isset($parameters['cache'])) {
			$parameters['cache'] = [
				"key" => '['.get_called_class() .']:['. self::_createKey($parameters).']',
				"lifetime" => 3
			];
		}elseif(isset($parameters['cache']['lifetime']) && !$parameters['cache']['lifetime']){
			unset($parameters['cache']);
		}
//		die(var_dump());
//		echo get_called_class();
		return parent::find($parameters);
	}

	/**
	 * Implement a method that returns a string key based
	 * on the query parameters
	 * @param array $parameters
	 * @return string
	 */
	protected static function _createKey(array $parameters)
	{
//		$uniqueKey = array();
//		foreach ($parameters as $key => $value) {
//			if (is_scalar($value)) {
//				$uniqueKey[] = $key . ':' . $value;
//			} else {
//				if (is_array($value)) {
//					$uniqueKey[] = $key . ':[' . self::_createKey($value) . ']';
//				}
//			}
//		}
//		return join(',', $uniqueKey);
		global $di;
		return $di->get('bilna')->createKey($parameters);
	}

	public static function findFirst($parameters = null)
	{
//		$di = \Phalcon\DI\FactoryDefault::getDefault();
		global $di;
		if (isset($parameters['cache'])) {
			if (!($cache = $di->get('modelsCache'))) return parent::findFirst($parameters);
			$key = get_called_class() . self::_createKey($parameters);
			if (($result = $cache->get('NEGATIVE' . $key)) !== NULL) {
				return $result;
			}
		}
		//Convert the parameters to an array
//		if (!is_array($parameters)) {
//			$parameters = array($parameters);
//		}


//		//Check if a cache key wasn't passed
//		//and create the cache parameters
//		if (!isset($parameters['cache'])) {
//			$parameters['cache'] = array(
//				"key" => $key,
//				"lifetime" => 3
//			);
//		}
//		(var_dump($parameters));
//		echo get_called_class();


		$result = parent::findFirst($parameters);
		if (empty($result)) {
			if (!isset($cache) && !($cache = $di->get('modelsCache'))) return $result;
			if ($result === NULL) $result = false;

			if (!isset($key))
				$key = get_called_class() . self::_createKey($parameters);

			$cache->save('NEGATIVE' . $key, $result, 15);
//			die(var_dump($result));
		}
		return $result;

	}

	public function initialize()
	{
		$this->useDynamicUpdate(true);
		$this->skipAttributesOnUpdate(['createdDate']);
	}

	protected static $_columnMaps = null;
	public function columnMap(){

		if($this->getModelsMetaData())
		{
//			die(var_dump($this->_modelsMetaData->readMetaData($this)));
//			0 : all
//			1 : linked
//			2 : non-PK
//			3 : not-null
//			4 : type db,  not 0=> , 1=>, 2=> , 4=> , 6=>text
//			5 : numeric
//			8 : PK
//			9 : by type int(1)/string(2)

			$this->_modelsMetaData->setAutomaticCreateAttributes($this, ['last_updated_date' => true,'lastUpdatedDate' => true],true);
		}

		$namespaceClass = get_class($this);

		if(isset(static::$_columnMaps[$namespaceClass]))return static::$_columnMaps[$namespaceClass];

		if($this->getModelsMetaData())
		{
//			$modelNamespace = $namespaceClass;
			static::$_columnMaps[$namespaceClass] = array_flip($this->_modelsMetaData->readMetaData($this)[0]);
			foreach(static::$_columnMaps[$namespaceClass] as $ori=>$cc)
				static::$_columnMaps[$namespaceClass][$ori] = lcfirst(\Phalcon\Text::camelize($ori));

			return static::$_columnMaps[$namespaceClass];
		}
		return [];
	}


	public function __get($property)
	{
		if (property_exists($this, $property)) {
			$prop = new \ReflectionProperty($this, $property);

			if (isset($this->$property) && $prop->isProtected())
			{
				return $this->$property;
			}
			if(in_array($property,$this->columnMap())){
				return null;
			}

		}elseif(in_array($property,$this->columnMap())){
			return null;
		}
		return parent::__get($property);
	}

	/*public static function __callStatic($name, $arguments)
	{
		var_dump($name);

//		if(strpos($name,'Categories'))die('ok2');
		$return = parent::__callStatic($name,$arguments);
//		die(var_dump(get_class($return)));
		return $return;
	}*/

	/*public function __call ( $name , $arguments ){

		if(strpos($name,'get')===0){
			$attribute = lcfirst(substr($name,3));
			if(in_array($attribute,$this->columnMap())){
				if(isset($this->$attribute))return $this->$attribute;
				else null;
			}
		}
		elseif(strpos($name,'set')===0){
			$attribute = lcfirst(substr($name,3));
			if(in_array($attribute,$this->columnMap())){
				$prop = new \ReflectionProperty($this, $attribute);

				if (!$prop->isProtected())
				{
					$this->$attribute = $arguments[0];
					return;
				}
			}
		}
		die(var_dump($name));
		return parent::__call($name,$arguments);
		//throw new \Exception('Tried to call unknown method '.get_class($this).'::'.$f);
	}*/

	public function __isset($property){
		$this->columnMap();
		$namespaceClass = get_class($this);
		if(isset(static::$_columnMaps[$namespaceClass]) && isset(static::$_columnMaps[$namespaceClass][\Phalcon\Text::uncamelize($property)])){
			$this->$property = null;
//			return false;
		}

		return false;
	}

	public function afterSave()
	{
		if(($args = func_get_args())&&count($args))$log = $args[0];
		else $log = null;
		if(!($log instanceof \Phalcon\Mvc\Collection))return;

		if($this->hasSnapshotData()) {

			//update log
			if($this->hasChanged()){
//				$log = new CustomerCollections;
//				$log->id = $this->customerId;
				$this->logUpdate($log);
			}
		}elseif($this->id){

			//insert logger
//			$log = new CustomerCollections;
//			$log->id = $this->customerId;
			$this->logInsert($log);
		}
	}

	protected function logUpdate(\Phalcon\Mvc\Collection $log){
		$log->action = 'update';

		// getChangedFields
		$fields = array_flip($this->getChangedFields());
		$fields['id'] = 1;
		$log->newObject = array_intersect_key($this->toArray(),$fields);
		$log->oldObject = array_intersect_key($this->getSnapshotData(),$fields);

		$log->tableName = $this->getSchema();
		try{
//					die('ok');
			$log->save();
		}catch(\Exception $e){

		}

	}

	protected function logInsert(\Phalcon\Mvc\Collection $log){
		$log->action = 'insert';
		$log->newObject = $this->toArray();
		$log->tableName = $this->getSchema();
		try{
//				die('ok');
			$log->save();
			foreach ($log->getMessages() as $e) {
				$m[] = $e->getMessage();
			}
//				die(var_dump($m));
		}catch(\Exception $e){
//				die(var_dump($e->getMessage()));
		}

	}

	/**
	 * note: for cascaded delete impact master, please define it yourself what to log
	 * @return bool
	 */
	public function delete(){
		if(($args = func_get_args())&&count($args))$log = $args[0];
		else $log = null;

		if(($log instanceof \Phalcon\Mvc\Collection) && $this->hasSnapshotData()) {

			//update log
//				$log = new CustomerCollections;
//				$log->id = $this->customerId;
				$log->action = 'delete';


				$log->oldObject = $this->toArray();
				$log->tableName = $this->getSchema();
				try{
//					die('ok');
					$log->save();
				}catch(\Exception $e){

				}
		}
		return parent::delete();
	}

	public function beforeValidationOnUpdate(){
		if($this->hasSnapshotData() && isset($this->status) && !is_object($this->status)  && $this->hasChanged('status')){
			$olddata = $this->getSnapshotData();
			if($olddata['status'] && !$this->status){
				$message = new Model\Message(
					DefaultConstant::RECORD_REVERSE_UPDATE.'|'.str_replace($this->getDI()->get('config')->errConst->toArray(),[basename(get_called_class()),($this->id?'Update':'Create'),'status','Pending'],DefaultConstant::RECORD_REVERSE_UPDATE_MESSAGE),
					"status",
					"RECORD_REVERSE_UPDATE"
				);
				$this->appendMessage($message);
				return false;

			}

		}
		return true;
	}

	public function __set($property,$value)
	{
		if(!property_exists($this, $property) && in_array($property,$this->columnMap())){
			if(isset($this->$property)){
				$prop = new \ReflectionProperty($this, $property);

				if ($prop->isProtected())throw new \Exception('Inaccessible property '.$property.' trying to set value to '.$value);

			}
			$this->$property = $value;
		}else{
			parent::__set($property,$value);
		}

	}

		protected function validateDate($date, $format = 'Y-m-d H:i:s')
	{
		$d = \DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}


	//checkPath
	protected function checkPath(array $path, $boolreturn=TRUE){
		// if $boolreturn return Path and return boolean else {use UnionPath and join Path}

		if($boolreturn){
			return Path::find(['conditions'=>'FIND_IN_SET(?1,path)','bind'=>[1=>implode(',',$path)]]);
		}else{
			//use modelsManager for joining;
		}
	}

	//insertPath
	/**
	 * @param array $path of \Api\Core\Models\Path
	 */

	protected function insertPath(array $path){
		foreach($path as $p)$p->save();
	}
}