<?php

class Moxy_SocialCommerce_Model_Collectioncover extends Mage_Core_Model_Abstract
{
	protected $FILES;
    protected function _construct(){

       $this->_init("socialcommerce/collectioncover");

    }

	/*
	public function save() 
	{
		$files = $_FILES;
		parent::save();
	}
	
	//public function save() 
	protected function _afterLoad()
	{
	/*	
		if (isset($_FILES)){
			echo var_dump($_FILES);
			
			if ($upFileTmpName = $_FILES['image']['tmp_name']) {
				$cloudinary = \Cloudinary\Uploader::upload($upFileTmpName, [
					'crop'      => 'fill',
					'width'     => '800',
					'height'    => '800',
					'gravity'   => 'face',
					'format'    => 'jpg',
					'tags'      => ['social_commerce', 'image_preset_collection',],
					
				]);
				$presetCollection = $cloudinary['public_id'];
				echo $presetCollection;
			}
			 
$upFileTmpName = $_FILES['image']['tmp_name'];
echo $upFileTmpName;
		}
		echo "fuck";
	 	 
		echo var_dump($FILES);
		if ($FILES) {
			$cover = Mage::helper('socialcommerce')->processAvatar(getcwd() + "/media/" + $this->getImage());
			$this->setImage($cover);
			$this->save();
		}
		//parent::save();
		//echo "wkasdasdasdad";
	}
	 */ 

}
	 
