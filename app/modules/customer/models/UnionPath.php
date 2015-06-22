<?php

namespace Frontend\Core\Models;

use Frontend\Core\Libraries\DefaultConstant;

class UnionPath extends BaseModel
{

    /**
     *
     * @var string
     */
    protected $path;

    /**
     *
     * @var string
     */
	protected $type;

    /**
     *
     * @var integer
     */
	protected $typeId;

//    public function getSource()
//    {
//        return 'union_path';
//    }

	public function save($data=null,$whiteList=null){
		throw new \Phalcon\Mvc\Model\Exception(str_replace($this->getDI()->get('config')->errConst->toArray(),['UnionPath','Save'],DefaultConstant::RECORD_CANT_REMOVE_MESSAGE),DefaultConstant::RECORD_CANT_REMOVE);
	}
	public function insert($data=null,$whiteList=null){
		throw new \Phalcon\Mvc\Model\Exception(str_replace($this->getDI()->get('config')->errConst->toArray(),['UnionPath','Insert'],DefaultConstant::RECORD_CANT_REMOVE_MESSAGE),DefaultConstant::RECORD_CANT_REMOVE);
	}
	public function update($data=null,$whiteList=null){
		throw new \Phalcon\Mvc\Model\Exception(str_replace($this->getDI()->get('config')->errConst->toArray(),['UnionPath','Update'],DefaultConstant::RECORD_CANT_REMOVE_MESSAGE),DefaultConstant::RECORD_CANT_REMOVE);
	}


    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return [
            'path' => 'path', 
            'type' => 'type',
			'type_id' => 'typeId',
			'table' => 'table',
			'table_id' => 'tableId',
        ];
    }

}
