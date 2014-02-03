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


class AW_Blog_Block_Blog extends AW_Blog_Block_Abstract
{
    public function getPosts()
    {
        $collection = parent::_prepareCollection();
        $tag = $this->getRequest()->getParam('tag');
        if ($tag) {
            $collection->addTagFilter(urldecode($tag));
        }
        parent::_processCollection($collection);
        return $collection;
    }

    public function getCategoriesPosts()
    {
        $collection = Mage::getModel("blog/cat")->getCollection()
            ->setOrder('sort_order', 'ASC');

        $data = array();
        foreach ($collection as $row) {
            $data[$row->getCatId()]['layout'] = $row->getLayout(); 
            $data[$row->getCatId()]['catName'] = $row->getTitle();

            $catId = $row->getCatId();

            $posts = Mage::getModel("blog/blog")->getCollection()
                ->addPresentFilter()
                ->addEnableFilter(AW_Blog_Model_Status::STATUS_ENABLED)
                ->addStoreFilter()
                ->setOrder('created_time', 'desc');
            $posts->addFieldToFilter("awblog_post_cat.cat_id", array ('eq' => $catId));
            $posts->getSelect()
                ->joinLeft(
                    array( 'awblog_post_cat' => Mage::getSingleton('core/resource')->getTableName('blog/post_cat') ),
                    "main_table.post_id = awblog_post_cat.post_id",
                    array(
                        'cat_id' => 'awblog_post_cat.cat_id'
                    )
            );
            parent::_processCollection($posts);    

            $data[$row->getCatId()]['post'] = $posts;
        }

        return $data;
    }

    public function getCategory()
    {
        return Mage::getSingleton('blog/cat');
    }

    public function getDataPosts()
    {
        return $this->getCatPosts();
    }

    protected function _prepareLayout()
    {
        if ($this->isBlogPage() && ($breadcrumbs = $this->getCrumbs())) {
            parent::_prepareMetaData(self::$_helper);
            $tag = $this->getRequest()->getParam('tag', false);
            if ($tag) {
                $tag = urldecode($tag);
                $breadcrumbs->addCrumb(
                    'blog',
                    array(
                        'label' => self::$_helper->getTitle(),
                        'title' => $this->__('Return to ' . self::$_helper->getTitle()),
                        'link'  => $this->getBlogUrl(),
                    )
                );
                $breadcrumbs->addCrumb(
                    'blog_tag',
                    array(
                        'label' => $this->__('Tagged with "%s"', self::$_helper->convertSlashes($tag)),
                        'title' => $this->__('Tagged with "%s"', $tag),
                    )
                );
            } else {
                $breadcrumbs->addCrumb('blog', array('label' => self::$_helper->getTitle()));
            }
        }
    }
}