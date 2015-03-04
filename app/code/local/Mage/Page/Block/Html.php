<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Page
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Page_Block_Html extends Mage_Core_Block_Template
{
    protected $_urls = array();
    protected $_title = '';

    public function __construct()
    {
        parent::__construct();
        $this->_urls = array(
            'base'      => Mage::getBaseUrl('web'),
            'baseSecure'=> Mage::getBaseUrl('web', true),
            'current'   => $this->getRequest()->getRequestUri()
        );

        $action = Mage::app()->getFrontController()->getAction();
        if ($action) {
            $this->addBodyClass($action->getFullActionName('-'));
        }

        $this->_beforeCacheUrl();
    }

    public function getBaseUrl()
    {
        return $this->_urls['base'];
    }

    public function getBaseSecureUrl()
    {
        return $this->_urls['baseSecure'];
    }

    public function getCurrentUrl()
    {
        return $this->_urls['current'];
    }

    /**
     *  Print Logo URL (Conf -> Sales -> Invoice and Packing Slip Design)
     *
     *  @return	  string
     */
    public function getPrintLogoUrl ()
    {
        // load html logo
        $logo = Mage::getStoreConfig('sales/identity/logo_html');
        if (!empty($logo)) {
            $logo = 'sales/store/logo_html/' . $logo;
        }

        // load default logo
        if (empty($logo)) {
            $logo = Mage::getStoreConfig('sales/identity/logo');
            if (!empty($logo)) {
                // prevent tiff format displaying in html
                if (strtolower(substr($logo, -5)) === '.tiff' || strtolower(substr($logo, -4)) === '.tif') {
                    $logo = '';
                }
                else {
                    $logo = 'sales/store/logo/' . $logo;
                }
            }
        }

        // buld url
        if (!empty($logo)) {
            $logo = Mage::getStoreConfig('web/unsecure/base_media_url') . $logo;
        }
        else {
            $logo = '';
        }

        return $logo;
    }

    public function getPrintLogoText()
    {
        return Mage::getStoreConfig('sales/identity/address');
    }

    public function setHeaderTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function getHeaderTitle()
    {
        return $this->_title;
    }

    /**
     * Add CSS class to page body tag
     *
     * @param string $className
     * @return Mage_Page_Block_Html
     */
    public function addBodyClass($className)
    {
        $className = preg_replace('#[^a-z0-9]+#', '-', strtolower($className));
        $this->setBodyClass($this->getBodyClass() . ' ' . $className);
        return $this;
    }

    public function getLang()
    {
        if (!$this->hasData('lang')) {
            $this->setData('lang', substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2));
        }
        return $this->getData('lang');
    }

    public function setTheme($theme)
    {
        $arr = explode('/', $theme);
        if (isset($arr[1])) {
            Mage::getDesign()->setPackageName($arr[0])->setTheme($arr[1]);
        } else {
            Mage::getDesign()->setTheme($theme);
        }
        return $this;
    }

    public function getBodyClass()
    {
        return $this->_getData('body_class');
    }

    public function getAbsoluteFooter()
    {
        return Mage::getStoreConfig('design/footer/absolute_footer');
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        return $this->_afterCacheUrl($html);
    }
    
    public function getStoreCategoryIdentifier($categoryActive = array ()) {
        $_helper = Mage::helper('megamenu');
        $_storeCategories = $_helper->getMegamenuData();
        $_storeCategoryIdentifier = '';
        
        if (count($_storeCategories) > 0) {
            foreach ($_storeCategories as $_storeCategory) {
                if (count($categoryActive)) {
                    if ($_storeCategory['id'] == $categoryActive['category']) {
                        $_storeCategoryIdentifier = "style-" . $_storeCategory['url_key'];
                        break;
                    }
                }
                else {
                    if ($_storeCategory['id'] == $_helper->getCurrentMainCategory()) {
                        $_storeCategoryIdentifier = "style-" . $_storeCategory['url_key'];
                        break;
                    }
                }
            }
        }
        
        return $_storeCategoryIdentifier;
    }
    
    public $_categoryPath;
    public function getCategoryActive() {
        $path = array ();
        $categoryActive = array ();
        
        if (Mage::getSingleton('cms/page')->getIdentifier() == 'home' && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') {
            return false;
        }
            
        if ($category = $this->getCategory()) {
            $pathInStore = $category->getPathInStore();
            $pathIds = array_reverse(explode(',', $pathInStore));
            $categories = $category->getParentCategories();
            $x = 0;

            // add category path breadcrumb
            foreach ($pathIds as $categoryId) {
                if (isset ($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                    $path['category' . $categoryId] = array (
                        'id' => $categoryId,
                        'group' => $this->getCategoryGroup($x)
                    );
                    $x++;
                }
            }
            
            $this->_categoryPath = $path;
        }
        
        if (count($this->_categoryPath) > 0) {
            foreach ($this->_categoryPath as $categoryPath) {
                $categoryActive[$categoryPath['group']] = $categoryPath['id'];
            }
        }
        
        return $categoryActive;
    }
    
    public function getCategoryGroup($categoryLevel) {
        $group = array (
            0 => 'category',
            1 => 'subcategory',
            2 => 'subsubcategory'
        );
        
        return $group[$categoryLevel];
    }

    public function getCategory() {
        return Mage::registry('current_category');
    }
    
    public function getProduct() {
        return Mage::registry('current_product');
    }
    
    public function _isCategoryLink($categoryId) {
        if ($this->getProduct()) {
            return true;
        }
        
        if ($categoryId != $this->getCategory()->getId()) {
            return true;
        }
        
        return false;
    }
    
    public function getCurrentMainCategoryActive() {
        $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
        $result = '';
        
        if (Mage::registry('current_category')) {
            $parentId = Mage::registry('current_category')->getParentId();
            
            if ($parentId == $rootCategoryId) {
                $result = Mage::registry('current_category')->getUrlKey();
            }
            else {
                $result = $this->getParentStyle(Mage::registry('current_category')->getId());
            }
        }
        
        return 'style-' . $result;
    }
    
    protected function getParentStyle($id) {
        $_helper = Mage::helper('megamenu');
        $_categories = $_helper->getMegamenuData();
        $result = '';
        
        if ($_categories && count($_categories) > 0) {
            foreach ($_categories as $_category) {
                if ($_category['id'] == $id) {
                    $result = $_category['url_key'];
                    break;
                }
                else {
                    if ($_category['child'] && count($_category['child']) > 0) {
                        foreach ($_category['child'] as $_subcategory) {
                            if ($_subcategory['id'] == $id) {
                                $result = $_subcategory['parent_url_key'];
                                break;
                            }
                            else {
                                if ($_subcategory['child'] && count($_subcategory['child']) > 0) {
                                    foreach ($_subcategory['child'] as $_subsubcategory) {
                                        if ($_subsubcategory['id'] == $id) {
                                            $result = $_subsubcategory['parent_url_key'];
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $result;
    }
    
    public function getCurrentMainCategoryIdActive() {
        $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
        $result = '';
        
        if (Mage::registry('current_category')) {
            $parentId = Mage::registry('current_category')->getParentId();
            
            if ($parentId == $rootCategoryId) {
                $result = Mage::registry('current_category')->getId();
            }
            else {
                $result = $this->getParentIdCurrentCategory(Mage::registry('current_category')->getId());
            }
        }
        
        return $result;
    }
    
    protected function getParentIdCurrentCategory($id) {
        $_helper = Mage::helper('megamenu');
        $_categories = $_helper->getMegamenuData();
        $result = '';
        
        if ($_categories && count($_categories) > 0) {
            foreach ($_categories as $_category) {
                if ($_category['id'] == $id) {
                    $result = $_category['id'];
                    break;
                }
                else {
                    if ($_category['child'] && count($_category['child']) > 0) {
                        foreach ($_category['child'] as $_subcategory) {
                            if ($_subcategory['id'] == $id) {
                                $result = $_subcategory['parent_id'];
                                break;
                            }
                            else {
                                if ($_subcategory['child'] && count($_subcategory['child']) > 0) {
                                    foreach ($_subcategory['child'] as $_subsubcategory) {
                                        if ($_subsubcategory['id'] == $id) {
                                            $result = $_subsubcategory['parent_id'];
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $result;
    }
    
    public function getMegamenuData() {
        $_helper = Mage::helper('megamenu');
        
        return $_helper->getMegamenuData();
    }
}
