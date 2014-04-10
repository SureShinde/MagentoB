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
        $identifier = $this->getRequest()->getParam('identifier');
        $collection = Mage::getModel("blog/cat")->getCollection()
            ->addFieldToFilter("main_table.title", array ('neq' => 'Uncategorized'))
            ->addFieldToFilter("main_table.parent_id", array ('eq' => 0))
            ->addFieldToSelect('title')
            ->addFieldToSelect('layout')
            ->addFieldToSelect('identifier');
            //->setOrder('main_table.sort_order', 'ASC');
        
        if($identifier !=''){
            $collection->addFieldToFilter("main_table.identifier", array ('eq' => $identifier));
        }else{
            $collection->getSelect()->group('main_table.parent_id');
        }
        $collection->getSelect()->order('awblog_cat.sort_order', 'ASC')->order('awblog_cat.cat_id', 'ASC');
        $collection->getSelect()
            ->joinLeft(
                array( 'awblog_cat' => Mage::getSingleton('core/resource')->getTableName('blog/cat') ),
                "main_table.cat_id = awblog_cat.parent_id",
                array(
                    'parent_id' => 'main_table.cat_id',
                    'cat_id' => 'awblog_cat.cat_id',
                    'sub_title' => 'awblog_cat.title'
                )
            );    
        $collection->getSelect()->limit(6);
$collection->printLogQuery(true);
        $data = array();
        foreach ($collection as $row) {
            $data[$row->getCatId()]['layout'] = $row->getLayout(); 
            $data[$row->getCatId()]['catName'] = $row->getTitle();
            $data[$row->getCatId()]['subName'] = $row->getSubTitle();

            if($identifier !=''){
                $catId = $row->getCatId();
            }else{
                $catId = $row->getParentId();
            }
            

            $posts = Mage::getModel("blog/blog")->getCollection()
                ->addPresentFilter()
                ->addEnableFilter(AW_Blog_Model_Status::STATUS_ENABLED)
                ->addStoreFilter()
                ->addFieldToSelect('identifier')
                ->addFieldToSelect('title')
                ->addFieldToSelect('created_time')
                ->addFieldToSelect('image_name')
                ->addFieldToSelect('identifier')
                ->addFieldToSelect('short_content')
                ->addFieldToSelect('identifier')
                ->setOrder('created_time', 'desc');
            $posts->addFieldToFilter("apc.cat_id", array('eq' => $row->getCatId()) );
            //$posts->addFieldToFilter("awblog_post_cat.cat_id", array(array ('eq' => $row->getParentId()), array('eq' => $catId)));
            //$posts->addFieldToFilter("awblog_post_cat.post_id", array('in' => array($row->getParentId(), $catId) ));
            $posts->addFieldToFilter("apc.post_id", array('in' => array( new Zend_Db_Expr('(SELECT post_id FROM aw_blog_post_cat WHERE cat_id='.$row->getParentId().')') ) ));
            $posts->getSelect()
                /*->joinLeft(
                    array( 'awblog_post_cat' => Mage::getSingleton('core/resource')->getTableName('blog/post_cat') ),
                    "main_table.post_id = awblog_post_cat.post_id",
                    array(
                        'cat_id' => 'awblog_post_cat.cat_id'
                    )
            	)*/
                ->limit(5);
        
            $posts = parent::_processCollection($posts);    

            $data[$row->getCatId()]['post'] = $posts;
        }

        return $data;
    }

    public function getSliderPost()
    {
        $collection = Mage::getModel("blog/blog")->getCollection()
                ->addPresentFilter()
                ->addEnableFilter(AW_Blog_Model_Status::STATUS_ENABLED)
                ->addStoreFilter()
                ->addFieldToSelect('identifier')
                ->addFieldToSelect('title')
                ->addFieldToSelect('created_time')
                ->addFieldToSelect('image_name')
                ->addFieldToSelect('short_content')
                ->setOrder('created_time', 'desc');
        $collection->addFieldToFilter("main_table.is_slider", array ('eq' => 1));

        return $collection;              
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
