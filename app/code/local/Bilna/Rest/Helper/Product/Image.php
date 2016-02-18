<?php
/**
 * Description of Bilna_Rest_Helper_Image
 *
 * @project LOGAN
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Helper_Product_Image extends Mage_Core_Helper_Abstract {
    const DEFAULT_STORE_ID = 1;
    
    protected $_scheduleResize = false;
    
    protected $_width = 0;
    protected $_height = 0;
    protected $_quality = 100;

    protected $_keepAspectRatio = true;
    protected $_keepFrame = true;
    protected $_keepTransparency = true;
    protected $_constrainOnly = false;
    protected $_backgroundColor = array (255, 255, 255);

    protected $_file;
    protected $_baseFile;
    protected $_placeholder;
    protected $_isBaseFilePlaceholder;
    protected $_newFile;
    protected $_processor;
    protected $_destinationSubdir;
    protected $_angle;

    protected $_watermarkFile;
    protected $_watermarkPosition;
    protected $_watermarkWidth;
    protected $_watermarkHeigth;
    protected $_watermarkImageOpacity = 70;
    
    protected function reset() {
        $this->_width = 0;
        $this->_height = 0;
        $this->_quality = 100;
        
        $this->_scheduleResize = false;
        $this->_keepAspectRatio = true;
        $this->_keepFrame = true;
        $this->_keepTransparency = true;
        $this->_constrainOnly = false;
        $this->_backgroundColor = array (255, 255, 255);
        
        $this->_file = null;
        $this->_baseFile = null;
        $this->_placeholder = null;
        $this->_isBaseFilePlaceholder = false;
        $this->_newFile = null;
        $this->_processor = null;
        $this->_destinationSubdir = null;
        $this->_angle = null;
    }

    public function init($file) {
        $this->reset();
        $this->_file = $file;
    }

    public function resize($width, $height = null) {
        if (is_null($width) && is_null($height)) {
            //- logging error here
            return false;
        }
        
        $this->_width = $width;
        $this->_height = $height;
        $this->_scheduleResize = true;
    }
    
    public function saveFile() {
        $filename = $this->getNewFile();
        $this->_getImageProcessor()->save($filename);
        
        return true;
    }
    
    public function __toString() {
        $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        
        if ($this->getFile()) {
            $this->setBaseFile($this->getFile());
        }
        
        if ($this->isCached()) {
            return $this->getUrl();
        }
        else {
            if ($this->_scheduleResize) {
                $this->_getImageProcessor()->resize($this->_width, $this->_height);
            }
            
            $this->saveFile();
            $url = $this->getUrl();
        }
        
        return $url;
    }
    
    protected function isCached() {
        return $this->_fileExists($this->_newFile);
    }
    
    protected function _getImageProcessor() {
        if (!$this->_processor) {
            $this->_processor = new Varien_Image($this->getBaseFile());
        }
        
        $this->_processor->keepAspectRatio($this->_keepAspectRatio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality($this->_quality);
        
        return $this->_processor;
    }

    protected function getUrl() {
        $baseDir = Mage::getBaseDir('media');
        $path = str_replace($baseDir . DS, "", $this->_newFile);
        
        return Mage::getBaseUrl('media') . str_replace(DS, '/', $path);
    }
    
    protected function getFile() {
        return $this->_file;
    }
    
    protected function getBaseFile() {
        return $this->_baseFile;
    }
    
    protected function getNewFile() {
        return $this->_newFile;
    }
    
    protected function setBaseFile($file) {
        $this->_isBaseFilePlaceholder = false;

        if (($file) && (0 !== strpos($file, '/', 0))) {
            $file = '/' . $file;
        }
        
        $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        if ('/no_selection' == $file) {
            $file = null;
        }
        
        if ($file) {
            if ((!$this->_fileExists($baseDir . $file)) || !$this->_checkMemory($baseDir . $file)) {
                $file = null;
            }
        }
        
        if (!$file) {
            //- check if placeholder defined in config
            $isConfigPlaceholder = Mage::getStoreConfig("catalog/placeholder/{$this->getDestinationSubdir()}_placeholder");
            $configPlaceholder = '/placeholder/' . $isConfigPlaceholder;
            
            if ($isConfigPlaceholder && $this->_fileExists($baseDir . $configPlaceholder)) {
                $file = $configPlaceholder;
            }
            else {
                //- replace file with skin or default skin placeholder
                $skinBaseDir = Mage::getDesign()->getSkinBaseDir();
                $skinPlaceholder = "/images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg";
                $file = $skinPlaceholder;
                
                if (file_exists($skinBaseDir . $file)) {
                    $baseDir = $skinBaseDir;
                }
                else {
                    $baseDir = Mage::getDesign()->getSkinBaseDir(array ('_theme' => 'default'));
                    
                    if (!file_exists($baseDir . $file)) {
                        $baseDir = Mage::getDesign()->getSkinBaseDir(array ('_theme' => 'default', '_package' => 'base'));
                    }
                }
            }
            
            $this->_isBaseFilePlaceholder = true;
        }

        $baseFile = $baseDir . $file;

        if ((!$file) || (!file_exists($baseFile))) {
            //throw new Exception(Mage::helper('catalog')->__('Image file was not found.'));
            //- logging eror here
            return false;
        }

        $this->_baseFile = $baseFile;

        //- build new filename (most important params)
        $path = array (
            Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath(),
            'cache',
            self::DEFAULT_STORE_ID,
            $path[] = $this->getDestinationSubdir()
        );
        
        if ((!empty ($this->_width)) || (!empty ($this->_height))) {
            $path[] = "{$this->_width}x{$this->_height}";
        }

        // add misk params as a hash
        $miscParams = array (
            ($this->_keepAspectRatio ? '' : 'non') . 'proportional',
            ($this->_keepFrame ? '' : 'no') . 'frame',
            ($this->_keepTransparency ? '' : 'no') . 'transparency',
            ($this->_constrainOnly ? 'do' : 'not') . 'constrainonly',
            $this->_rgbToString($this->_backgroundColor),
            'angle' . $this->_angle,
            'quality' . $this->_quality
        );

        //- if has watermark add watermark params to hash
        //if ($this->getWatermarkFile()) {
        //    $miscParams[] = $this->getWatermarkFile();
        //    $miscParams[] = $this->getWatermarkImageOpacity();
        //    $miscParams[] = $this->getWatermarkPosition();
        //    $miscParams[] = $this->getWatermarkWidth();
        //    $miscParams[] = $this->getWatermarkHeigth();
        //}

        $path[] = md5(implode('_', $miscParams));

        // append prepared filename
        $this->_newFile = implode('/', $path) . $file; // the $file contains heading slash
    }
    
    protected function getPlaceholder() {
        if (!$this->_placeholder) {
            $this->_placeholder = "images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg";
        }
        
        return $this->_placeholder;
    }
    
    protected function _fileExists($filename) {
        if (file_exists($filename)) {
            return true;
        }
        
        return false;
    }
    
    protected function _checkMemory($filename) {
        return true;
    }
    
    protected function getDestinationSubdir() {
        return 'image';
    }
    
    protected function _rgbToString($rgbArray) {
        $result = array ();
        
        foreach ($rgbArray as $value) {
            if (null === $value) {
                $result[] = 'null';
            }
            else {
                $result[] = sprintf('%02s', dechex($value));
            }
        }
        
        return implode($result);
    }
}
