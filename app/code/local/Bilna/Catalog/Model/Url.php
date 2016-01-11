<?php
class Bilna_Catalog_Model_Url extends Mage_Catalog_Model_Url
{
    /**
     * Refresh all rewrite urls for some store or for all stores
     * Used to make full reindexing of url rewrites
     *
     * @param int $storeId
     * @return Mage_Catalog_Model_Url
     */
    public function refreshRewrites($storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshRewrites($store->getId());
            }
            return $this;
        }

        $this->clearStoreInvalidRewrites($storeId);
        $this->refreshCategoryRewrite($this->getStores($storeId)->getRootCategoryId(), $storeId, false);
        //$this->refreshProductRewrites($storeId);
        $this->getResource()->clearCategoryProduct($storeId);

        return $this;
    }

    /**
     * Refresh category rewrite
     *
     * @param Varien_Object $category
     * @param string $parentPath
     * @param bool $refreshProducts
     * @return Mage_Catalog_Model_Url
     */
    protected function _refreshCategoryRewrites(Varien_Object $category, $parentPath = null, $refreshProducts = true)
    {
        try {
            if ($category->getId() != $this->getStores($category->getStoreId())->getRootCategoryId()) {
                if ($category->getUrlKey() == '') {
                    $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
                }
                else {
                    $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
                }

                $idPath      = $this->generatePath('id', null, $category);
                $targetPath  = $this->generatePath('target', null, $category);
                $requestPathWithParent = $this->getCategoryRequestPathWithParent($category, $parentPath);
                $requestPath = $this->getCategoryRequestPath($category, $parentPath);

                $requestPathPrependedTrimmed = $this->trimPrependedPrefix($requestPath);

                if ($requestPathPrependedTrimmed != $requestPathWithParent) {
                    /* this is to preserve the old url with parent and will redirect to the new url */
                    $rewriteDataWithParent = array(
                        'store_id'      => $category->getStoreId(),
                        'category_id'   => $category->getId(),
                        'product_id'    => null,
                        'id_path'       => $idPath,
                        'request_path'  => $requestPathWithParent,
                        'target_path'   => $requestPath,
                        'options'       => 'RP',
                        'is_system'     => 0,
                    );

                    $this->_rewrite = null;

                    $this->getResource()->saveRewrite($rewriteDataWithParent, $this->_rewrite);
                }

                $rewriteData = array(
                    'store_id'      => $category->getStoreId(),
                    'category_id'   => $category->getId(),
                    'product_id'    => null,
                    'id_path'       => $idPath,
                    'request_path'  => $requestPath,
                    'target_path'   => $targetPath,
                    'is_system'     => 1,
                );

                $this->getResource()->saveRewrite($rewriteData, $this->_rewrite);

                if ($this->getShouldSaveRewritesHistory($category->getStoreId())) {
                    $this->_saveRewriteHistory($rewriteData, $this->_rewrite);
                }

                if ($category->getUrlKey() != $urlKey) {
                    $category->setUrlKey($urlKey);
                    $this->getResource()->saveCategoryAttribute($category, 'url_key');
                }
                if ($category->getUrlPath() != $requestPathWithParent) {
                    $category->setUrlPath($requestPathWithParent);
                    $this->getResource()->saveCategoryAttribute($category, 'url_path');
                }
            }
            else {
                if ($category->getUrlPath() != '') {
                    $category->setUrlPath('');
                    $this->getResource()->saveCategoryAttribute($category, 'url_path');
                }
            }

            if ($refreshProducts) {
                $this->_refreshCategoryProductRewrites($category);
            }

            foreach ($category->getChilds() as $child) {
                $this->_refreshCategoryRewrites($child, $category->getUrlPath() . '/', $refreshProducts);
            }
        }
        catch(Exception $e) {
            Mage::log(date("Y-m-d H:i:s") . " reindex catalog url : " . $e->getMessage());  
        }

        return $this;
    }

    /**
     * Get unique category request path
     *
     * @param Varien_Object $category
     * @param string $parentPath
     * @return string
     */
    public function getCategoryRequestPath($category, $parentPath)
    {
        $storeId = $category->getStoreId();
        $idPath  = $this->generatePath('id', null, $category);
        $suffix  = $this->getCategoryUrlSuffix($storeId);

        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();
        }

        if ($category->getUrlKey() == '') {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
        }
        else {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
        }

        $categoryUrlSuffix = $this->getCategoryUrlSuffix($category->getStoreId());

        if (null === $parentPath) {
            $parentPath = $this->getResource()->getCategoryParentPath($category);
        }
        elseif ($parentPath == '/') {
            $parentPath = '';
        }
        $parentPath = Mage::helper('catalog/category')->getCategoryUrlPath($parentPath,
                                                                           true, $category->getStoreId());

        /*
        $is_unique = true;
        // ensure the URL Key must be unique
        if ($parentPath != '')
            $is_unique = $this->check_unique_url_key($category, $urlKey);

        // if the URL Key is not unique, generate the Request Path by adding parent path as the prefix
        if (!$is_unique) {
            $parentPath = str_replace('/', '-', $parentPath);
            $requestPath = $parentPath . $urlKey . $categoryUrlSuffix;
        }
        // if the URL Key is unique, then we do not have to prepend the parent path
        else {
            $requestPath = $urlKey . $categoryUrlSuffix;
        }
        */

        $prependedPrefix = $this->prependURLSuffix($category);
        $requestPath = $prependedPrefix . $urlKey . $categoryUrlSuffix;
    
        if (isset($existingRequestPath) && $existingRequestPath == $requestPath . $suffix) {
            return $existingRequestPath;
        }

        if ($this->_deleteOldTargetPath($requestPath, $idPath, $storeId)) {
            return $requestPath;
        }

        return $this->getUnusedPathCustom($category->getStoreId(), $requestPath, $prependedPrefix, $urlKey,
                                    $this->generatePath('id', null, $category)
        );
    }

    public function getCategoryRequestPathWithParent($category, $parentPath)
    {
        $storeId = $category->getStoreId();
        $idPath  = $this->generatePath('id', null, $category);
        $suffix  = $this->getCategoryUrlSuffix($storeId);

        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();
        }

        if ($category->getUrlKey() == '') {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
        }
        else {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
        }

        $categoryUrlSuffix = $this->getCategoryUrlSuffix($category->getStoreId());

        if (null === $parentPath) {
            $parentPath = $this->getResource()->getCategoryParentPath($category);
        }
        elseif ($parentPath == '/') {
            $parentPath = '';
        }
        $parentPath = Mage::helper('catalog/category')->getCategoryUrlPath($parentPath,
                                                                           true, $category->getStoreId());

        $requestPath = $parentPath . $urlKey . $categoryUrlSuffix;
    
        if (isset($existingRequestPath) && $existingRequestPath == $requestPath . $suffix) {
            return $existingRequestPath;
        }

        return $this->getUnusedPathCustom($category->getStoreId(), $requestPath, '', $urlKey,
                                    $this->generatePath('id', null, $category)
        );
    }

    private function prependURLSuffix($category) {
        $prependedPrefix = 'cp/';

        /* check whether the categories are Promo or Brand */
        $path = $category->getPath();
        $ids = explode('/', $path);
        for ($i = 0 ; $i < count($ids) ; $i++) {
            // if current category falls under Promo
            if ($ids[$i] == 3930) {
                $prependedPrefix = 'pp/';
                break;
            }
            else
            // if current category falls under Brand
            if ($ids[$i] == 4261) {
                $prependedPrefix = 'bp/';
                break;
            }
        }

        return $prependedPrefix;
    }

    private function trimPrependedPrefix($requestPath) {
        $paths = explode('/', $requestPath);
        $array = array();
        if(count($paths) > 1) {
            for($i = 1 ; $i < count($paths) ; $i++)
                $array[$i-1] = $paths[$i];
            $trimmedPath = implode('/', $array);
        }

        return $trimmedPath;
    }

    private function check_unique_url_key($category, $urlKey) {
        $count = Mage::getModel ('catalog/category') 
            ->getCollection() 
            ->addAttributeToFilter('url_key', $urlKey) //load the "sales" category 
            ->count();
        if ($count <= 1)
            return true;

        return false;
    }

    private function getUnusedPathCustom($storeId, $requestPath, $prependedPrefix, $urlKey, $idPath)
    {
        if (strpos($idPath, 'product') !== false) {
            $suffix = $this->getProductUrlSuffix($storeId);
        } else {
            $suffix = $this->getCategoryUrlSuffix($storeId);
        }
        if (empty($requestPath)) {
            $requestPath = '-';
        } elseif ($requestPath == $suffix) {
            $requestPath = '-' . $suffix;
        }

        /**
         * Validate maximum length of request path
         */
        if (strlen($requestPath) > self::MAX_REQUEST_PATH_LENGTH + self::ALLOWED_REQUEST_PATH_OVERFLOW) {
            $requestPath = substr($requestPath, 0, self::MAX_REQUEST_PATH_LENGTH);
        }

        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            if ($this->_rewrites[$idPath]->getRequestPath() == $requestPath) {
                return $requestPath;
            }
        }
        else {
            $this->_rewrite = null;
        }

        $rewrite = $this->getResource()->getRewriteByRequestPath($requestPath, $storeId);
        if ($rewrite && $rewrite->getId()) {
            if ($rewrite->getIdPath() == $idPath) {
                $this->_rewrite = $rewrite;
                return $requestPath;
            }
            // match request_url abcdef1234(-12)(.html) pattern
            $match = array();
            $regularExpression = '#^([0-9a-z/-]+?)(-([0-9]+))?('.preg_quote($suffix).')?$#i';
            if (!preg_match($regularExpression, $requestPath, $match)) {
                return $this->getUnusedPath($storeId, '-', $idPath);
            }

            $match[1] = $match[1] . '-';
            $match[4] = isset($match[4]) ? $match[4] : '';

            // we use matcher instead of match[1] to get the fully matched regex
            $matcher = substr($match[0], 0, strlen($match[0]) - 1);
            $matcher = $matcher . '-';

            // get the last incremental number
            // if not found, set it to 0
            $lastRequestPath = $this->getResource()
                ->getLastUsedRewriteRequestIncrement($matcher, $match[4], $storeId);
            if ($lastRequestPath) {
                $match[3] = $lastRequestPath;
            }
            else
                $match[3] = 0;

            // check whether there is already a record with the idpath
            // if it exists, no need to increment the number
            $currentRequestPath = $this->check_idpath_existence($storeId, $prependedPrefix, $urlKey, $idPath);

            if ($currentRequestPath == "false") {
                return $matcher
                    . (isset($match[3]) ? ($match[3]+1) : '1')
                    . $match[4];
            }
            else
            {
                return $currentRequestPath;
            }
        }
        else {
            return $requestPath;
        }
    }

    /* check whether there is already a record with the idpath
        if it exists, no need to increment the number
    */
    private function check_idpath_existence($storeId, $prependedPrefix, $urlKey, $idPath) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('core/url_rewrite');
        $where = "id_path = '$idPath' AND store_id = $storeId AND request_path LIKE '$prependedPrefix$urlKey-%/' LIMIT 1";
        $query = "SELECT request_path FROM $table WHERE $where";

        $result = $readConnection->fetchOne($query);
        
        if ($result)
            return $result;
        else
            return "false";
    }
}
