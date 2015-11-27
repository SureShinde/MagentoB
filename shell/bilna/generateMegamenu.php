<?php
/**
 * Description of GenerateMegamenu
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class GenerateMegamenu extends Mage_Shell_Abstract {
    protected $_storeId = null;
    protected $_helper = null;
    protected $_model = null;
    protected $_directory = null;
    protected $_activeCategory = array ();

    public function run() {
        Mage::log('Mega Menu is starting now..');

        $this->setHelper();
        $this->setModel();
        $this->setDirectory();

        Mage::log('Trying to get store lists..');
        $stores = Mage::app()->getStores();
        Mage::log('done');

        Mage::log('Starting looping the stores..');
        foreach ($stores as $store) {
            Mage::log('Starting set current store '.$store->getId());
            $this->setCurrentStore($store->getId());
            Mage::log('done. Now generating..');
            $this->generate();
            Mage::log('Finished generating store ID '.$store->getId());
        }
        Mage::log('Finished looping the stores. Thank you for using Mage Log :)');

        exit;
    }

    protected function checkActiveCategory($category) {
        Mage::log('Starting check active category for '.$category);
        $result = true;

        Mage::log('Starting check is category active for '.$category);
        if (!$category->getIsActive()) {
            $result = false;
        }
        Mage::log('done.');

        Mage::log('Starting check category include in menu for '.$category);
        if (!$category->getIncludeInMenu()) {
            $result = false;
        }

        Mage::log('done. Return Result is '.$result);

        return $result;
    }

    protected function generate() {
        Mage::log('Starting generate store function..');

        Mage::log('Starting get store categories from helper..');
        $storeCategories = $this->_helper->getStoreCategories();
        Mage::log('done');

        Mage::log('Store Category got total number of '.count($storeCategories));
        if (count($storeCategories) > 0) {
            $x = 0;

            Mage::log('Trying to loop the Store Category');
            foreach ($storeCategories as $storeCategory) {
                Mage::log('Trying to load model for store id '.$storeCategory->getId());
                $storeCategory = $this->_model->load($storeCategory->getId());
                Mage::log('done');

                // If Category is not included Mega Menu, skip to next category
                if ($storeCategory->getIncludeInMegamenu() != 1) {
                    Mage::log('Category is not included in Mega Menu. Proceed to next category..');
                    continue;
                }

                Mage::log('Now processing id category '.$storeCategory->getId());
                $_id = $storeCategory->getId();
                $_urlKey = $storeCategory->getUrlKey();

                Mage::log('Check if is an active category');
                if (!$this->checkActiveCategory($storeCategory)) {
                    Mage::log('Category is not an active category. Proceed to next category..');
                    continue;
                }

                Mage::log('Category is an active category. Proceed the script..');

                Mage::log('Trying to parse Store Category');
                $this->_activeCategory[$x] = $this->parseStoreCategory($storeCategory);
                Mage::log('done');

                Mage::log('Trying to get Shopby');
                $this->_activeCategory[$x]['shopby'] = $this->getShopbyCmsBlock($storeCategory->getUrlKey());
                Mage::log('done');

                Mage::log('Starting to get children categories level 2');
                $storeCategories2 = $storeCategory->getChildrenCategories();
                Mage::log('done. It has '.count($storeCategories2).' children categories.');

                $y = 0;

                if (count($storeCategories2) > 0) {
                    Mage::log('Starting looping children categories..');
                    foreach ($storeCategories2 as $storeCategory2) {
                        Mage::log('Starting loading model for children category id '.$storeCategory2->getId());
                        $storeCategory2 = $this->_model->load($storeCategory2->getId());
                        Mage::log('done');

                        // If Category is not included Mega Menu, skip to next category
                        if ($storeCategory2->getIncludeInMegamenu() != 1) {
                            Mage::log('Category is not included in Mega Menu. Proceed to next category..');
                            continue;
                        }

                        Mage::log('Now processing id category '.$storeCategory2->getId());
                        $_id2 = $storeCategory2->getId();
                        $_urlKey2 = $storeCategory2->getUrlKey();

                        Mage::log('Check if is an active category');
                        if (!$this->checkActiveCategory($storeCategory2)) {
                            Mage::log('Category is not an active category. Proceed to next category..');
                            continue;
                        }
                        Mage::log('Category is an active category. Proceed the script..');

                        $this->_activeCategory[$x]['child'][$y] = $this->parseStoreCategory($storeCategory2);
                        $this->_activeCategory[$x]['child'][$y]['parent_id'] = $_id;
                        $this->_activeCategory[$x]['child'][$y]['parent_url_key'] = $_urlKey;

                        Mage::log('Starting define getMegamenuStaticBlock');
                        $this->_activeCategory[$x]['child'][$y]['megamenu_staticblock'] = $storeCategory2->getMegamenuStaticBlock() ? $this->getMegamenuCmsBlock($storeCategory2->getMegamenuStaticBlock()) : '';
                        Mage::log('done. Value is '.$this->_activeCategory[$x]['child'][$y]['megamenu_staticblock']);

                        Mage::log('Starting define getMegamenuImage');
                        $this->_activeCategory[$x]['child'][$y]['megamenu_image'] = $storeCategory2->getMegamenuImage() ? $storeCategory2->getMegamenuImageUrl($storeCategory2->getMegamenuImage()) : '';
                        Mage::log('done. Value is '.$this->_activeCategory[$x]['child'][$y]['megamenu_image']);

                        Mage::log('Starting get children categories level 3');
                        $storeCategories3 = $storeCategory2->getChildrenCategories();
                        Mage::log('done. It has '.count($storeCategories3).' children category.');
                        $z = 0;

                        if (count($storeCategories3) > 0) {
                            Mage::log('Start to process children categories level 3');
                            foreach ($storeCategories3 as $storeCategory3) {
                                Mage::log('Trying to get children model');
                                $storeCategory3 = $this->_model->load($storeCategory3->getId());
                                Mage::log('done');

                                // If Category is not included Mega Menu, skip to next category
                                if ($storeCategory3->getIncludeInMegamenu() != 1) {
                                    Mage::log('Category is not included in Mega Menu. Proceed to next category..');
                                    continue;
                                }

                                Mage::log('Check if is an active category..');
                                if (!$this->checkActiveCategory($storeCategory3)) {
                                    Mage::log('ID '.$storeCategory3->getId(). ' is not an active category. Proceed to next category.');
                                    continue;
                                }
                                Mage::log('Category is an active category. Proceed the script..');

                                Mage::log('Defining the parameters..');
                                $this->_activeCategory[$x]['child'][$y]['child'][$z] = $this->parseStoreCategory($storeCategory3);
                                $this->_activeCategory[$x]['child'][$y]['child'][$z]['parent_id'] = $_id;
                                $this->_activeCategory[$x]['child'][$y]['child'][$z]['parent_url_key'] = $_urlKey;
                                $this->_activeCategory[$x]['child'][$y]['child'][$z]['parent_sub_id'] = $_id2;
                                $this->_activeCategory[$x]['child'][$y]['child'][$z]['parent_sub_url_key'] = $_urlKey2;
                                Mage::log('done');
                                $z++;
                            }
                            Mage::log('End of process children categories level 3');
                        }
                        else {
                            Mage::log('It has no children category.');
                            $this->_activeCategory[$x]['child'][$y]['child'] = null;
                        }

                        $y++;
                    }
                    Mage::log('Finished looping children categories');
                }
                else {
                    Mage::log('This category do not have child categories.');
                    $this->_activeCategory[$x]['child'] = null;
                }
                Mage::log('Proceed to next id category..');
                $x++;
            }
            Mage::log('End of looping Store Category');

            Mage::log('Starting to generate file..');
            if ($this->createFile()) {
                Mage::log('Generate file succeed');
                echo "storeId {$this->_storeId}: generate file succeed\n";
            }
            else {
                Mage::log('Generate file failed');
                echo "storeId {$this->_storeId}: generate file failed\n";
            }
        }
        else {
            echo "storeId {$this->_storeId}: cannot generate file\n";
            Mage::log("storeId {$this->_storeId}: cannot generate file\n");
        }
    }

    protected function setCurrentStore($storeId) {
        Mage::log('Starting set current store for '.$storeId);
        $this->_storeId = $storeId;
        Mage::app()->setCurrentStore($storeId);
        Mage::log('done');
    }

    protected function setHelper() {
        Mage::log('Starting set helper function..');
        $this->_helper = Mage::helper('megamenu');
        Mage::log('done');
    }

    protected function setModel() {
        Mage::log('Starting set Model function..');
        $this->_model = Mage::getModel('megamenu/catalog_category');
        Mage::log('done');
    }

    protected function setDirectory() {
        Mage::log('Starting set Directory function..');
        $this->_directory = $this->_helper->getMegamenuDir();
        Mage::log('done');
    }

    protected function getShopbyCmsBlock($blockId) {
        Mage::log('Starting get Shopby function..');
        return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('shopby-' . $blockId)->toHtml();
        Mage::log('done');
    }

    protected function getMegamenuCmsBlock($blockId) {
        Mage::log('Starting create Block function..');
        return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
        Mage::log('done');
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
        Mage::log('Starting create file function..');

        $filename = $this->_directory . $this->_storeId . ".json";
        Mage::log('File destination prepared: '.$filename);

        Mage::log('Checking directory..');
        // check directory
        if (!file_exists($this->_directory)) {
            mkdir($this->_directory, 0777, true);
        }
        Mage::log('done');

        Mage::log('Checking file..');
        // check file
        if (file_exists($filename)) {
            unlink($filename);
        }
        Mage::log('done');

        Mage::log('Setting the file permission for writing file');
        $handle = fopen($filename, 'w');
        Mage::log('done');

        Mage::log('Trying to create the file');
        if (fwrite($handle, json_encode($this->_activeCategory))) {
            Mage::log('done');
            fclose($handle);

            return true;
        }
        Mage::log('Failed to write the file.');
        return false;
    }

    protected function logProgress($message) {
        if ($this->getArg('verbose')) {
            Mage::log('logProgress : '.$message);
            echo $message . "\n";
        }
    }
}

$shell = new GenerateMegamenu();
$shell->run();
