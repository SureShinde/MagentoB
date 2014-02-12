<?php

class Amasty_Xlanding_Helper_Data extends Mage_Core_Helper_Abstract
{
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 0;
	
	public function getAvailableStatuses()
	{
		return array(
			self::STATUS_ENABLED => Mage::helper('amlanding')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('amlanding')->__('Disabled'),
		);
	}
	
	public function getMenuPositions()
	{
		return array(
			Amasty_Xlanding_Model_Source_Menu::INCLUDE_NO => Mage::helper('amlanding')->__('No'),
			Amasty_Xlanding_Model_Source_Menu::INCLUDE_APPEND => Mage::helper('amlanding')->__('Yes, Append to existing'),
			Amasty_Xlanding_Model_Source_Menu::INCLUDE_PREPEND => Mage::helper('amlanding')->__('Yes, Prepend existing'),
		);
	}
	
	public function getColumnCount()
	{
		return Mage::getStoreConfig('amlanding/advanced/column_count');
	}
	
	public function newFilterActive()
	{
		return (Mage::app()->getRequest()->getParam('am_landing') && Mage::getStoreConfig('amlanding/advanced/new_criteria')
			&& Mage::registry('amlanding_page')->getIsNew() != 0 && !$this->isVersionLessThan(1,7));
	}
	
	public function seoLinksActive()
	{
	   return class_exists('Amasty_Shopby_Block_Catalog_Layer_View') && 
	   		('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Shopby/active')) && 
	   		Mage::getStoreConfig('amshopby/seo/urls');
	}
	
	public function isVersionLessThan($major=5, $minor=3)
    {
        $curr = explode('.', Mage::getVersion()); // 1.3. compatibility
        $need = func_get_args();
        foreach ($need as $k => $v){
            if ($curr[$k] != $v)
                return ($curr[$k] < $v);
        }
        return false;
    }
	
	public function updateDirSepereator($path){
        return str_replace('\\', DS, $path);
    }
	
	//Fungsi-fungsi upload image
	public function getImageUrl($image_file) 
	{
        $url = false;
        if (file_exists(self::$egridImgDir . self::$egridImgThumb . $this->updateDirSepereator($image_file)))
            $url = self::$egridImgURL . self::$egridImgThumb . $image_file;
        else
            $url = self::$egridImgURL . $image_file;
        return $url;
    }
	
	public function getFileExists($image_file) 
	{
        $file_exists = false;
        $file_exists = file_exists(self::$egridImgDir . $this->updateDirSepereator($image_file));
        return $file_exists;
    }
	
	public function getImageThumbSize($image_file) 
	{
        $img_file = $this->updateDirSepereator(self::$egridImgDir . $image_file);
        if ($image_file == '' || !file_exists($img_file))
            return false;
        list($width, $height, $type, $attr) = getimagesize($img_file);
        $a_height = (int) ((self::$egridImgThumbWidth / $width) * $height);
        return Array('width' => self::$egridImgThumbWidth, 'height' => $a_height);
    }
	
	function deleteFiles($image_file) 
	{
        $pass = true;
        if (!unlink(self::$egridImgDir . $image_file))
            $pass = false;
        if (!unlink(self::$egridImgDir . self::$egridImgThumb . $image_file))
            $pass = false;
        return $pass;
    }
}
