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
 * This software is designed to work with Magento professional edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Blog_Block_Menu_Sidebar extends AW_Blog_Block_Abstract
{
    public function getRecent()
    {
        // widget declaration
        if ($this->getBlogWidgetRecentCount()) {
            $size = $this->getBlogWidgetRecentCount();
        } else {
            // standard output
            $size = self::$_helper->getRecentPage();
        }

        if ($size) {
            $collection = clone self::$_collection;
            $collection->setPageSize($size);
            $collection->addFieldToSelect('title');
            $collection->addFieldToSelect('created_time');
            $collection->addFieldToSelect('image_name');
            $collection->addFieldToSelect('identifier');
            foreach ($collection as $item) {
                $item->setAddress($this->getBlogUrl($item->getIdentifier()));
            }
     
            return $collection;
        }
        return false;
    }

    public function getCategories()
    {
        $collection = Mage::getModel('blog/cat')
            ->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder('sort_order', 'asc')
        ;
        foreach ($collection as $item) {
            $item->setAddress($this->getBlogUrl(array(self::$_catUriParam, $item->getIdentifier())));
        }
        return $collection;
    }

    public function getComment()
    {
        $collection = Mage::getModel('blog/comment')
            ->getCollection()
            ->addFieldToSelect('created_time')
            ->addFieldToSelect('user')
            ->addFieldToSelect('email')
            ->setOrder('created_time', 'DESC');

        $collection->addFieldToFilter("main_table.status", array ('eq' => 2));
        $collection->getSelect()
            ->joinLeft(
                array( 'awblog_post' => Mage::getSingleton('core/resource')->getTableName('blog/post') ),
                "main_table.post_id = awblog_post.post_id",
                array(
                    'image_name' => 'awblog_post.image_name',
                    'identifier' => 'awblog_post.identifier',
                    'title'      => 'awblog_post.title',
                )
        	)
            ->limit(5);

        $collection->getSelect()->limit(5);

        return $collection;
    }

    public function getPopular()
    {
        // widget declaration
        if ($this->getBlogWidgetRecentCount()) {
            $size = $this->getBlogWidgetRecentCount();
        } else {
            // standard output
            $size = self::$_helper->getRecentPage();
        }

        if ($size) {
            $collection = clone self::$_collection;
            $collection->setPageSize($size);
            $collection->addFieldToSelect('title');
            $collection->addFieldToSelect('created_time');
            $collection->addFieldToSelect('image_name');
            
            foreach ($collection as $item) {
                $item->setAddress($this->getBlogUrl($item->getIdentifier()));
            }
            return $collection;
        }
        return false;
    }

    protected function _beforeToHtml()
    {
        return $this;
    }

    protected function _toHtml()
    {
        if (self::$_helper->getEnabled()) {
            $parent = $this->getParentBlock();
            if (!$parent) {
                return null;
            }

            $showLeft = Mage::getStoreConfig('blog/menu/left');
            $showRight = Mage::getStoreConfig('blog/menu/right');

            $isBlogPage = Mage::app()->getRequest()->getModuleName() == AW_Blog_Helper_Data::DEFAULT_ROOT;

            $leftAllowed = ($isBlogPage && ($showLeft == 2)) || ($showLeft == 1);
            $rightAllowed = ($isBlogPage && ($showRight == 2)) || ($showRight == 1);

            if (!$leftAllowed && ($parent->getNameInLayout() == 'left')) {
                return null;
            }
            if (!$rightAllowed && ($parent->getNameInLayout() == 'right')) {
                return null;
            }

            return parent::_toHtml();
        }
    }
}