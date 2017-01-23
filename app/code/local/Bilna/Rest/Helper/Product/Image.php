<?php
/**
 * Description of Bilna_Rest_Helper_Image
 *
 * @project LOGAN
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Helper_Product_Image extends Mage_Core_Helper_Abstract
{
    const DEFAULT_STORE_ID = 1;

    protected $_width = 0;
    protected $_height = 0;
    protected $_quality = 100;

    protected $_scheduleResize = false;
    protected $_keepAspectRatio = true;
    protected $_keepFrame = true;
    protected $_keepTransparency = true;
    protected $_constrainOnly = false;
    protected $_backgroundColor = [255, 255, 255];

    protected $_file;
    protected $_baseFile;
    protected $_placeholder;
    protected $_newFile;
    protected $_processor;
    protected $_destinationHash;
    protected $_mediaDir;
    protected $_mediaUrl;

    public function init($file)
    {
        $this->reset();
        $this->_file = $file;
    }

    public function resize($width, $height = null)
    {
        if (is_null($width) && is_null($height)) {
            //- logging error here
            return false;
        }

        $this->_width = $width;
        $this->_height = $height;
        $this->_scheduleResize = true;
    }

    public function saveFile()
    {
        $filename = $this->_newFile;
        $this->getImageProcessor()->save($filename);

        return true;
    }

    public function __toString()
    {
        if ($this->_file) {
            $this->setBaseFile($this->_file);
        }

        if (!$this->isCached()) {
            if ($this->_scheduleResize) {
                $this->getImageProcessor()->resize($this->_width, $this->_height);
            }

            $this->saveFile();
        }

        return $this->getUrl();
    }

    protected function reset()
    {
        $this->_width = 0;
        $this->_height = 0;
        $this->_quality = 100;

        $this->_scheduleResize = false;
        $this->_keepAspectRatio = true;
        $this->_keepFrame = true;
        $this->_keepTransparency = true;
        $this->_constrainOnly = false;
        $this->_backgroundColor = [255, 255, 255];

        $this->_file = null;
        $this->_baseFile = null;
        $this->_placeholder = null;
        $this->_newFile = null;
        $this->_processor = null;
        $this->_destinationHash = null;
    }

    protected function isCached()
    {
        return file_exists($this->_newFile);
    }

    protected function getImageProcessor()
    {
        if (!$this->_processor) {
            $this->_processor = new Varien_Image($this->_baseFile);
            $this->_processor->keepAspectRatio($this->_keepAspectRatio);
            $this->_processor->keepFrame($this->_keepFrame);
            $this->_processor->keepTransparency($this->_keepTransparency);
            $this->_processor->constrainOnly($this->_constrainOnly);
            $this->_processor->backgroundColor($this->_backgroundColor);
            $this->_processor->quality($this->_quality);
        }

        return $this->_processor;
    }

    protected function getUrl()
    {
        $path = str_replace($this->getMediaDir() . DS, '', $this->_newFile);
        $path = str_replace(DS, '/', $path);
        return $this->getMediaUrl() . $path;
    }

    protected function setBaseFile($file)
    {
        $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
        $destinationSubdir = $this->getDestinationSubdir();

        if ($file) {
            if (is_string($file) && $file[0] !== '/') {
                $file = '/' . $file;
            }
            if (
                $file === '/no_selection' ||
                !file_exists($baseDir . $file)
            ) {
                $file = null;
            }
        }

        if (!$file) {
            //- check if placeholder defined in config
            $isConfigPlaceholder = Mage::getStoreConfig("catalog/placeholder/{$destinationSubdir}_placeholder");
            $configPlaceholder = '/placeholder/' . $isConfigPlaceholder;

            if ($isConfigPlaceholder && file_exists($baseDir . $configPlaceholder)) {
                $file = $configPlaceholder;
            } else {
                //- replace file with skin or default skin placeholder
                $file = "/images/catalog/product/placeholder/{$destinationSubdir}.jpg";
                $baseDir = Mage::getDesign()->getSkinBaseDir();
                if (!file_exists($baseDir . $file)) {
                    $baseDir = Mage::getDesign()->getSkinBaseDir(['_theme' => 'default']);
                    if (!file_exists($baseDir . $file)) {
                        $baseDir = Mage::getDesign()->getSkinBaseDir(['_theme' => 'default', '_package' => 'base']);
                    }
                }
            }
        }

        if (!$file || !file_exists($baseDir . $file)) {
            //- logging eror here
            return false;
        }
        $this->_baseFile = $baseDir . $file;

        //- build new filename (most important params)
        $path = [
            Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath(),
            'cache',
            self::DEFAULT_STORE_ID,
            $destinationSubdir
        ];
        if (!empty($this->_width) || !empty($this->_height)) {
            $path[] = "{$this->_width}x{$this->_height}";
        }
        $path[] = $this->getDestinationHash();

        // append prepared filename
        $this->_newFile = implode('/', $path) . $file; // the $file contains heading slash
    }

    protected function getPlaceholder()
    {
        if (!$this->_placeholder) {
            $this->_placeholder = "images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg";
        }
        return $this->_placeholder;
    }

    protected function getDestinationSubdir()
    {
        return 'image';
    }

    protected function getDestinationHash()
    {
        if (!$this->_destinationHash) {
            $miscParams = [
                ($this->_keepAspectRatio ? '' : 'non') . 'proportional',
                ($this->_keepFrame ? '' : 'no') . 'frame',
                ($this->_keepTransparency ? '' : 'no') . 'transparency',
                ($this->_constrainOnly ? 'do' : 'not') . 'constrainonly',
                $this->rgbToString($this->_backgroundColor),
                'angle',
                'quality' . $this->_quality
            ];
            $this->_destinationHash = md5(implode('_', $miscParams));
        }
        return $this->_destinationHash;
    }

    protected function getMediaDir()
    {
        if (!$this->_mediaDir) {
            $this->_mediaDir = Mage::getBaseDir('media');
        }
        return $this->_mediaDir;
    }

    protected function getMediaUrl()
    {
        if (!$this->_mediaUrl) {
            $this->_mediaUrl = Mage::getBaseUrl('media');
        }
        return $this->_mediaUrl;
    }

    protected function rgbToString($rgbArray)
    {
        $result = [];
        foreach ($rgbArray as $value) {
            $result[] = $value === null ?
                'null' :
                sprintf('%02s', dechex($value));
        }
        return implode($result);
    }
}
