<?php
/**
 * Description of GenerateMegamenu
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class GenerateMegamenu extends Mage_Shell_Abstract {
    protected $_storeId = 1;
    protected $_helper = null;
    protected $_model = null;
    protected $_directory = null;
    protected $_activeCategory = array ();
    
    protected function checkActiveCategory($category) {
        $result = true;
        
        if (!$category->getIsActive()) {
            $result = false;
        }

        if (!$category->getIncludeInMenu()) {
            $result = false;
        }
        
        return $result;
    }

    public function run() {
        $this->setCurrentStore();
        $this->setHelper();
        $this->setModel();
        $this->setDirectory();
        
        $storeCategories = $this->_helper->getStoreCategories();
        
        if (count($storeCategories) > 0) {
            $x = 0;
            
            foreach ($storeCategories as $storeCategory) {
                $storeCategory = $this->_model->load($storeCategory->getId());
                
                if (!$this->checkActiveCategory($storeCategory)) {
                    continue;
                }
                
                $this->_activeCategory[$x] = $this->parseStoreCategory($storeCategory);
                $this->_activeCategory[$x]['shopby'] = $this->getShopbyCmsBlock($storeCategory->getUrlKey());
                
                $storeCategories2 = $storeCategory->getChildrenCategories();
                $y = 0;
                
                if (count($storeCategories2) > 0) {
                    foreach ($storeCategories2 as $storeCategory2) {
                        $storeCategory2 = $this->_model->load($storeCategory2->getId());
                        
                        if (!$this->checkActiveCategory($storeCategory2)) {
                            continue;
                        }
                        
                        $this->_activeCategory[$x]['child'][$y] = $this->parseStoreCategory($storeCategory2);
                        $this->_activeCategory[$x]['child'][$y]['megamenu_staticblock'] = $storeCategory2->getMegamenuStaticBlock() ? $this->getMegamenuCmsBlock($storeCategory2->getMegamenuStaticBlock()) : '';
                        $this->_activeCategory[$x]['child'][$y]['megamenu_image'] = $storeCategory2->getMegamenuImage() ? $storeCategory2->getMegamenuImageUrl($storeCategory2->getMegamenuImage()) : '';
                        
                        $storeCategories3 = $storeCategory2->getChildrenCategories();
                        $z = 0;
                        
                        if (count($storeCategories3) > 0) {
                            foreach ($storeCategories3 as $storeCategory3) {
                                $storeCategory3 = $this->_model->load($storeCategory3->getId());
                                
                                if (!$this->checkActiveCategory($storeCategory3)) {
                                    continue;
                                }
                                
                                $this->_activeCategory[$x]['child'][$y]['child'][$z] = $this->parseStoreCategory($storeCategory3);
                                $z++;
                            }
                        }
                        else {
                            $this->_activeCategory[$x]['child'][$y]['child'] = null;
                        }
                        
                        $y++;
                    }
                }
                else {
                    $this->_activeCategory[$x]['child'] = null;
                }
                
                $x++;
            }
            
            if ($this->createFile()) {
                echo "generate file succeed\n";
            }
            else {
                echo "generate file failed\n";
            }
        }
        else {
            echo "cannot generate file\n";
        }
        
        exit;
    }
    
    protected function setCurrentStore() {
        Mage::app()->setCurrentStore($this->_storeId);
    }
    
    protected function setHelper() {
        $this->_helper = Mage::helper('megamenu');
    }
    
    protected function setModel() {
        $this->_model = Mage::getModel('megamenu/catalog_category');
    }
    
    protected function setDirectory() {
        $this->_directory = $this->_helper->getMegamenuDir();
    }
    
    protected function getShopbyCmsBlock($blockId) {
        return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('shopby-' . $blockId)->toHtml();
    }
    
    protected function getMegamenuCmsBlock($blockId) {
        return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
    }

    protected function parseStoreCategory($storeCategory) {
        return array (
            'id' => $storeCategory->getId(),
            'name' => $storeCategory->getName(),
            'url' => $this->_helper->getCategoryUrl($storeCategory),
            'url_key' => $storeCategory->getUrlKey()
        );
    }
    
    protected function createFile() {
        $filename = $this->_directory . $this->_storeId . ".json";
        
        // check directory
        if (!file_exists($this->_directory)) {
            mkdir($this->_directory, 0777, true);
        }
        
        // check file
        if (file_exists($filename)) {
            unlink($filename);
        }
        
        $handle = fopen($filename, 'w');
        
        if (fwrite($handle, json_encode($this->_activeCategory))) {
            fclose($handle);
            
            return true;
        }
        
        return false;
    }
    
    protected function logProgress($message) {
        if ($this->getArg('verbose')) {
            echo $message . "\n";
        }
    }
}

$shell = new GenerateMegamenu();
$shell->run();
