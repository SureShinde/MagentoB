<?php

namespace Frontend\Core\Models;

class Path extends \Frontend\Core\Models\BaseModel
{

    /**
     *
     * @var string
     */
    public $path;

	public function setPath($path){
		$this->path = $path;
		return $this;
	}

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
		parent::initialize();
        $this->hasMany('path', 'Api\Core\Models\Brands', 'path', array('alias' => 'Brands'));
        $this->hasMany('path', 'Api\Core\Models\Categories', 'path', array('alias' => 'Categories'));
        $this->hasMany('path', 'Api\Core\Models\Category_products', 'product_path', array('alias' => 'Category_products'));
        $this->hasMany('path', 'Api\Core\Models\Products', 'brand_product_path', array('alias' => 'Products'));
        $this->hasMany('path', 'Api\Core\Models\Products', 'path', array('alias' => 'Products'));
        $this->hasMany('path', 'Api\Core\Models\Static_pages', 'path', array('alias' => 'Static_pages'));
        $this->hasMany('path', 'Api\Core\Models\Vendors', 'path', array('alias' => 'Vendors'));
    }

//    public function getSource()
//    {
//        return 'path';
//    }

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'path' => 'path'
        );
    }

}
