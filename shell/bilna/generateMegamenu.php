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
    
    protected function setCurrentStore() {
        Mage::app()->setCurrentStore($this->_storeId);
    }
    
    protected function setHelper() {
        $this->_helper = Mage::helper('megamenu');
    }
    
    protected function setModel() {
        $this->_model = Mage::getModel('catalog/category');
    }
    
    protected function setDirectory() {
        $this->_directory = $this->_helper->getMegamenuDir();
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
                $this->_activeCategory[$x] = $this->parseStoreCategory($storeCategory);
                
                $storeCategories2 = $storeCategory->getChildrenCategories();
                $y = 0;
                
                if (count($storeCategories2) > 0) {
                    foreach ($storeCategories2 as $storeCategory2) {
                        $storeCategory2 = $this->_model->load($storeCategory2->getId());
                        $this->_activeCategory[$x]['child'][$y] = $this->parseStoreCategory($storeCategory2);
                        
                        $storeCategories3 = $storeCategory2->getChildrenCategories();
                        $z = 0;
                        
                        if (count($storeCategories3) > 0) {
                            foreach ($storeCategories3 as $storeCategory3) {
                                $storeCategory3 = $this->_model->load($storeCategory3->getId());
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
    
    protected function parseStoreCategory($storeCategory) {
        return array (
            'id' => $storeCategory->getId(),
            'name' => $storeCategory->getName(),
            'url' => $this->_helper->getCategoryUrl($storeCategory),
            'class' => strtolower($storeCategory->getUrlKey())
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
        else {
            $handle = fopen($filename, 'w');
        }
        
        if (fwrite($handle, json_encode($this->_activeCategory))) {
            fclose($handle);
            
            return true;
        }
        
        return false;
    }
}

$shell = new GenerateMegamenu();
$shell->run();
