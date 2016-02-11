<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Onsale_Model_Observer {
	 
     public function controllerPredispatch() {
	 
        if($this->_getPathInfo() == 'catalog_product_action_attribute_save') {

            $this->_unsetEmptyPositions();
            
            if(!empty($_FILES)) {
		
                $onSaleFiles = array(
                    "0" => "aw_os_product_image",
                    "1" => "aw_os_category_image"
                );
		 
                $path = AW_Onsale_Model_Entity_Attribute_Backend_Image::getUploadDirName();
                $onSaleAllowedExt = AW_Onsale_Model_Entity_Attribute_Backend_Image::getAllowedImgExt();
		 
                foreach($onSaleFiles as $file) {
                    if(isset($_FILES[$file])) {
                        try {
                            $uploader = new Varien_File_Uploader($_FILES[$file]);
                            $uploader->setAllowedExtensions($onSaleAllowedExt);
                            $uploader->setAllowRenameFiles(true);
                            $uploader->save($path);
                            $_POST['attributes'][$file] = $_FILES[$file]['name'];
                        }
                        catch(Exception $e) {
                            if ($e->getCode() != Varien_File_Uploader::TMP_NAME_EMPTY) {
                                Mage::logException($e);
                            }
                        }
                    }
                }
            }		
	}		 
    }

    private function _unsetEmptyPositions() {

        $onSalePositions = array(
          "0" => "aw_os_product_position",
          "1" => "aw_os_category_position"
        );

	foreach($onSalePositions as $position) {
            if(isset($_POST['attributes'])) {
                if(isset($_POST['attributes'][$position])) {
                    if(trim($_POST['attributes'][$position]) == '') {
                        unset($_POST['attributes'][$position]);
                    }
                }
            }
        }
    }
	
    private function _getPathInfo($sep = "_") {	

        $request = Mage::app()->getRequest();
        return "{$request->getControllerName()}$sep{$request->getActionName()}";

    } 
}