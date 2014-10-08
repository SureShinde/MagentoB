<?php
/**
 * Description of AW_Featured_Model_Observer_Generatefeatured
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class AW_Featured_Model_Observer_Generatefeatured {
    private $_data = array ();
    private $_limit = 100;

    public function process() {
        $this->getFeaturedList();
    }
    
    private function getFeaturedList() {
        echo "create/insert table start\n";
        
        $collections = Mage::getModel('awfeatured/blocks')->getCollection();
        $collections->addFieldToFilter('is_active', '1');
        
        foreach ($collections as $collection) {
            $this->_data = array (
                'id' => $collection->getId(),
                'store_id' => $collection->getStore(),
                'block_id' => $collection->getBlockId(),
                'type_data' => unserialize($collection->getTypeData()),
                'autoposition' => $collection->getAutoposition(),
                'automation_type' => $collection->getAutomationType(),
                'automation_data' => unserialize($collection->getAutomationData())
            );
            
            /**
             * create table bilna_featured_product_n
             */
            if (!$this->createTableBilnaFeaturedProduct()) {
                break;
            }
            
            /**
             * get data collection
             */
            $productCollection = $this->getProductsCollection();
            $productCollection->getSelect()->limit($this->_limit);
            
            if ($productCollection) {
                if (!$this->insertProductsCollection($productCollection)) {
                    echo "error";
                    break;
                }
            }
        }
        
        echo "create/insert table finish\n";
        exit;
    }
    
    private function createTableBilnaFeaturedProduct() {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "CREATE TABLE IF NOT EXISTS bilna_featured_product_" . $this->_data['id'] . " (
            id int(11) unsigned NOT NULL AUTO_INCREMENT,
            entity_id int(10) unsigned NOT NULL COMMENT 'Entity Id',
            type_id varchar(32) NOT NULL DEFAULT 'simple' COMMENT 'Type Id',
            attribute_set_id smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Attribute Set Id',
            visibility smallint(5) unsigned DEFAULT NULL COMMENT 'Visibility',
            inventory_in_stock smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'cataloginventory_stock_item.is_in_stock',
            qty decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'cataloginventory_stock_item.qty',
            min_qty decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'cataloginventory_stock_item.min_qty',
            manage_stock smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'cataloginventory_stock_item.manage_stock',
            backorders smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'cataloginventory_stock_item.backorders',
            request_path varchar(255) DEFAULT NULL COMMENT 'Request Path',
            position int(11) DEFAULT NULL COMMENT 'Position',
            store_id smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store ID',
            currency char(5) DEFAULT NULL COMMENT 'Currency',
            price decimal(12,4) DEFAULT NULL COMMENT 'Price',
            tax_class_id smallint(5) unsigned DEFAULT '0' COMMENT 'Tax Class ID',
            final_price decimal(12,4) DEFAULT NULL COMMENT 'Final Price',
            minimal_price decimal(12,4) DEFAULT NULL COMMENT 'Minimal Price',
            min_price decimal(12,4) DEFAULT NULL COMMENT 'Min Price',
            max_price decimal(12,4) DEFAULT NULL COMMENT 'Max Price',
            tier_price decimal(12,4) DEFAULT NULL COMMENT 'Tier Price',
            rating_summary smallint(6) NOT NULL DEFAULT '0' COMMENT 'Summarized rating',
            reviews_count smallint(6) NOT NULL DEFAULT '0' COMMENT 'Qty of reviews',
            name varchar(255) DEFAULT NULL COMMENT 'Name',
            short_description text COMMENT 'Short Description',
            small_image varchar(255) DEFAULT NULL COMMENT 'Small Image',
            thumbnail varchar(255) DEFAULT NULL COMMENT 'Thumbnail',
            image varchar(255) DEFAULT NULL COMMENT 'Image',
            msrp decimal(12,4) DEFAULT NULL COMMENT 'Msrp',
            msrp_enabled smallint(6) DEFAULT NULL COMMENT 'Msrp Enabled',
            msrp_display_actual_price_type varchar(255) DEFAULT NULL COMMENT 'Msrp Display Actual Price Type',
            aw_os_category_display smallint(6) DEFAULT NULL COMMENT 'Aw Os Category Display',
            aw_os_category_position varchar(255) DEFAULT NULL COMMENT 'Aw Os Category Position',
            aw_os_category_image varchar(255) DEFAULT NULL COMMENT 'Aw Os Category Image',
            aw_os_category_image_path varchar(255) DEFAULT NULL COMMENT 'Aw Os Category Image Path',
            aw_os_category_text varchar(255) DEFAULT NULL COMMENT 'Aw Os Category Text',
            news_from_date datetime DEFAULT NULL COMMENT 'News From Date',
            news_to_date datetime DEFAULT NULL COMMENT 'News To Date',
            is_new tinyint(1) DEFAULT 0 NOT NULL COMMENT 'Is new product',
            PRIMARY KEY (id),
            KEY IDX_BILNA_FEATURED_PRODUCT_" . $this->_data['id'] . "_ENTITY_ID (entity_id),
            KEY IDX_BILNA_FEATURED_PRODUCT_" . $this->_data['id'] . "_ATTRIBUTE_SET_ID (attribute_set_id),
            KEY IDX_BILNA_FEATURED_PRODUCT_" . $this->_data['id'] . "_NAME (name),
            KEY IDX_BILNA_FEATURED_PRODUCT_" . $this->_data['id'] . "_PRICE (price)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Bilna Featured Product " . $this->_data['id'] . "';
        TRUNCATE TABLE bilna_featured_product_" . $this->_data['id'] . ";";
        $query = $write->query($sql);
        
        if ($query) {
            echo "create table bilna_featured_product_" . $this->_data['id'] . " -> success\n";
            return true;
        }
        else {
            echo "create table bilna_featured_product_" . $this->_data['id'] . " -> failed\n";
            return false;
        }
    }
    
    private function insertProductsCollection($productCollection) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $sql  = sprintf("INSERT INTO bilna_featured_product_%d ", $this->_data['id']);
        $sql .= "("
                . "entity_id," //1
                . "type_id," //2
                . "attribute_set_id," //3
                . "visibility," //4
                . "inventory_in_stock," //5
                . "qty," //6
                . "min_qty," //7
                . "manage_stock," //8
                . "backorders," //9
                . "request_path," //10
                . "position," //11
                . "store_id," //12
                . "currency," //13
                . "price," //14
                . "tax_class_id," //15
                . "final_price," //16
                . "minimal_price," //17
                . "min_price," //18
                . "max_price," //19
                . "tier_price," //20
                . "rating_summary," //21
                . "reviews_count," //22
                . "name," //23
                . "short_description," //24
                . "small_image," //25
                . "thumbnail," //26
                . "image," //27
                . "msrp," //28
                . "msrp_enabled," //29
                . "msrp_display_actual_price_type," //30
                . "aw_os_category_display," //31
                . "aw_os_category_position," //32
                . "aw_os_category_image," //33
                . "aw_os_category_image_path," //34
                . "aw_os_category_text," //35
                . "news_from_date," //36
                . "news_to_date," //37
                . "is_new) "; //38
        $sql .= "VALUES ";
        $separator = false;

        foreach ($productCollection as $product) {
            /**
             * insert data to table bilna_featured_product_n
             */
            if ($separate) {
                $sql .= ", ";
            }

            $sql .= sprintf(
                "(%d, '%s', %d, %d, %d,"
                . "%d, %d, %d, %d, '%s',"
                . "%d, %d, '%s', %d, %d,"
                . "%d, %d, %d, %d, %d,"
                . "%d, %d, '%s', '%s',"
                . "'%s', '%s', '%s', %d, %d,"
                . "'%s', %d, '%s', '%s', '%s',"
                . "'%s', '%s', '%s', %d) ",
                $product->getEntityId(),
                mysql_real_escape_string($product->getTypeId()),
                $product->getAttributeSetId(),
                $product->getVisibility(),
                $product->getInventoryInStock(),
                $product->getQty(),
                $product->getMinQty(),
                $product->getManageStock(),
                $product->getBackorders(),
                $product->getRequestPath(),
                $product->getPosition(),
                $this->_data['store_id'][0],
                mysql_real_escape_string($this->_getCurrentCurrency()),
                $product->getPrice(),
                $product->getTaxClassId(),
                $product->getFinalPrice(),
                $product->getMinimalPrice(),
                $product->getMinPrice(),
                $product->getMaxPrice(),
                $product->getTierPrice(),
                $product->getRatingSummary(),
                $product->getReviewsCount(),
                mysql_real_escape_string($product->getName()),
                mysql_real_escape_string($product->getShortDescription()),
                mysql_real_escape_string($product->getSmallImage()),
                mysql_real_escape_string($product->getThumbnail()),
                mysql_real_escape_string(Mage::helper('awfeatured/images')->getProductImage($product, $product->getData('image_id'))->resize(152,152)),
                $product->getMsrp(),
                $product->getMsrpEnabled(),
                $product->getMsrpDisplayActualPriceType(),
                $product->getAwOsCategoryDisplay(),
                $product->getAwOsCategoryPosition(),
                $product->getAwOsCategoryImage(),
                $product->getAwOsCategoryImagePath(),
                $product->getAwOsCategoryText(),
                $product->getNewsFromDate(),
                $product->getNewsToDate(),
                $this->_checkProductIsNew($product->getNewsFromDate(), $product->getNewsToDate())
            );
            $separate = true;
        }

        $sql .= ";";
        $query = $write->query($sql);

        if ($query) {
            return true;
        }
        else {
            echo "error.. process stop..!!";
            return false;
        }
    }
    
    private function getProductsCollection() {
        $_productCollection = null;
        
        if (is_null($_productCollection)) {
            switch ($this->_data['automation_type']) {
                case AW_Featured_Model_Source_Automation::NONE:
                    $_productCollection = $this->_getCollectionForIds();
                    $automationData = $this->_data['automation_data'];
                    $productSortingType = $automationData['product_sorting_type'];
                    
                    if ($productSortingType == AW_Featured_Model_Source_Automation_Productsort::RANDOM) {
                        $_productCollection = $this->_getRandomProductsCollection($_productCollection);
                    }
                    else if ($productSortingType == AW_Featured_Model_Source_Automation_Productsort::OLDFIRST) {
                        $_productCollection->getSelect()->order('entity_id asc');
                    }
                    else {
                        $_productCollection->getSelect()->order('entity_id desc');
                    }
                    
                    break;
                
                case AW_Featured_Model_Source_Automation::RANDOM:
                    $_productCollection = $this->_getRandomProductsCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::TOPSELLERS:
                    $_productCollection = $this->_getTopSellersCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::TOPRATED:
                    $_productCollection = $this->_getTopRatedCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::MOSTREVIEWED:
                    $_productCollection = $this->_getMostReviewedCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::RECENTLYADDED:
                    $_productCollection = $this->_getRecentlyAddedCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::CURRENTCATEGORY:
                    $_productCollection = $this->_getCurrentCategoryCollection();
                    break;
                
                default:
                    $_productCollection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
                    //$this->setIsEmpty(true);
                    break;
            }
            
            $_productCollection->addMinimalPrice();
            $_productCollection->joinOveralRating();
            $_productCollection->joinReviewsCount();
            $attr = array (
                'name', 'short_description', 'small_image', 'thumbnail', 'image',
                'msrp', 'msrp_enabled', 'msrp_display_actual_price_type', 'aw_os_category_display',
                'aw_os_category_position', 'aw_os_category_image', 'aw_os_category_image_path', 'aw_os_category_text','news_from_date','news_to_date'

            );
            $_productCollection->addAttributeToSelect($attr);
        }
        
        return $_productCollection;
    }
    
    private function _getCollectionForIds() {
        $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        
        if ($this->_data['automation_data']) {
            $_automationData = $this->_data['automation_data'];
            $_products = isset ($_automationData['products']) ? @explode(',', $_automationData['products']) : array ();
            $_products = array_filter($_products, array (Mage::helper('awfeatured'), 'removeEmptyItems'));
            
            if ($_products) {
                $_collection->addAttributeToFilter('entity_id', $_products);
            }
            
            $_collection->getSelect()->joinLeft(
                array ('pi' => $_collection->getTable('awfeatured/productimages')),
                '(pi.product_id = e.entity_id) AND (pi.block_id = ' . $this->_data['id'] . ')',
                array ('image_id')
            );
        }
        
        return $_collection;
    }
    
    private function _prepareCollection($_collection) {
        $_visibility = array (
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
        );
        $_collection->addAttributeToFilter('visibility', $_visibility)
            ->addAttributeToFilter('status', array ('in' => Mage::getSingleton('catalog/product_status')->getVisibleStatusIds()));
        
        if (!$this->_getShowOutOfStock()) {
            Mage::getSingleton('awfeatured/stock')->addInStockFilterToCollection($_collection);
        }
        
        $_collection->addUrlRewrites()
            ->addStoreFilter($this->_data['store_id'][0])
            ->groupByAttribute('entity_id');
        
        return $_collection;
    }
    
    protected function _getRandomProductsCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        $_collection->addMinimalPrice();
        
        if (null === $collection) {
            $this->_addCategoriesFilter($_collection);
            $_automationData = $this->_data['automation_data'];
            //$limit = isset ($_automationData['quantity_limit']) ? $_automationData['quantity_limit'] : 10;
            $limit = $this->_limit;

            $ids = $_collection->getAllIds();

            $newArr = array ();
            
            if ($limit > count($ids)) {
                $limit = count($ids);
            }
            
            $randomPositions = (array) array_rand($ids, $limit);
            
            foreach ($randomPositions as $value) {
                $newArr[] = $ids[$value];
            }
            
            $ids = $newArr;
            $_collection->addFieldToFilter('entity_id', array ('in' => array ($ids)));
        }

        $_collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _getTopSellersCollection($collection = null) {
        if (null === $collection) {
            $collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
            $this->_addCategoriesFilter($collection);
        }
        $collection
            ->addOrderedQty()
            ->sortByOrderedQty();
        $this->_postprocessCollection($collection);
        
        return $collection;
    }
    
    protected function _getTopRatedCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $_collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        if (null === $collection) {
            $this->_addCategoriesFilter($_collection);
        }
        
        $_collection->joinOveralRating();
        $_collection->sortByRating();
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _postprocessCollection($collection) {
        //$_automationData = $this->_data['automation_data'];
        //$_pSize = isset ($_automationData['quantity_limit']) ? $_automationData['quantity_limit'] : 10;
        //$collection->setPageSize($_pSize);
        //$collection->setPageSize($this->_limit);
        //$collection->setCurPage(1);
        
        return $collection;
    }
    
    protected function _getMostReviewedCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $_collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        if (null === $collection) {
            $this->_addCategoriesFilter($_collection);
        }
        
        $_collection->joinReviewsCount();
        $_collection->sortByReviewsCount();
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _getRecentlyAddedCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $_collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        if (null === $collection) {
            $this->_addCategoriesFilter($_collection);
        }
        
        $_collection->addAttributeToSort('created_at', 'desc');
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _getCurrentCategoryCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $_collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        if (Mage::registry('current_category') && Mage::registry('current_category')->getId()) {
            $_collection->addCategoriesFilter(Mage::registry('current_category')->getId(), true);
            
            switch ($this->getAFPBlockAutomationData('current_category_type')) {
                case AW_Featured_Model_Source_Automation_Currentcategory::RANDOM:
                    $this->_getRandomProductsCollection($_collection);
                    break;
                
                case AW_Featured_Model_Source_Automation_Currentcategory::TOPSELLERS:
                    $this->_getTopSellersCollection($_collection);
                    break;
                
                case AW_Featured_Model_Source_Automation_Currentcategory::TOPRATED:
                    $this->_getTopRatedCollection($_collection);
                    break;
                
                case AW_Featured_Model_Source_Automation_Currentcategory::MOSTREVIEWED:
                    $this->_getMostReviewedCollection($_collection);
                    break;
                
                case AW_Featured_Model_Source_Automation_Currentcategory::RECENTLYADDED:
                default:
                    $this->_getRecentlyAddedCollection($_collection);
                    break;
            }
        }
        //else {
        //    $this->setIsEmpty(true);
        //}
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _addCategoriesFilter($collection) {
        $_automationData = $this->_data['automation_data'];
        $_categories = isset($_automationData['categories']) ? @explode(',', $_automationData['categories']) : array();
        $_categories = array_filter($_categories, array(Mage::helper('awfeatured'), 'removeEmptyItems'));
        
        if ($_categories) {
            $collection->addCategoriesFilter($_categories);
        }
        
        return $collection;
    }
    
    private function _getShowOutOfStock() {
        $_show = true;
        
        if (($_ciHelper = Mage::helper('cataloginventory')) && method_exists($_ciHelper, 'isShowOutOfStock')) {
            $_show = $_ciHelper->isShowOutOfStock();
        }
        
        return $_show;
    }
    
    private function _checkProductIsNew($newFromDate, $newToDate) {
        $result = 0;
        
        if (!empty ($newFromDate) && !empty ($newToDate)) {
            $now = strtotime(date('Y-m-d'));
            $newFrom = strtotime(substr($newFromDate, 0, 10));
            $newTo = strtotime(substr($newToDate, 0, 10));

            if ($newFrom >= $now && $newTo <= $now) {
                $result = 1;
            }
        }
        
        return $result;
    }
    
    private function _getCurrentCurrency() {
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        //$currencyName = Mage::app()->getLocale()->currency($currencyCode)->getName();
        $currencySymbol = Mage::app()->getLocale()->currency($currencyCode)->getSymbol();
        
        return $currencySymbol;
    }
}
