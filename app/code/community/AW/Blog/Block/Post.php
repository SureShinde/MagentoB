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


class AW_Blog_Block_Post extends AW_Blog_Block_Abstract
{
    public function getPost()
    {
        if (!$this->hasData('post')) {
            if ($this->getPostId()) {
                $post = Mage::getModel('blog/post')->load($this->getPostId());
            } else {
                $post = Mage::getSingleton('blog/post');
            }
            $category = Mage::getSingleton('blog/cat')->load(
                $this->getRequest()->getParam(self::$_catUriParam), "identifier"
            );
            if ($category->getIdentifier()) {
                $post->setAddress(
                    $this->getBlogUrl(
                        null,
                        array(
                            self::$_catUriParam  => $category->getIdentifier(),
                            self::$_postUriParam => $post->getIdentifier()
                        )
                    )
                );
            } else {
                $post->setAddress($this->getBlogUrl($post->getIdentifier()));
            }

            $this->_prepareData($post)->_prepareDates($post);

            $this->setData('post', $post);
        }

        return $this->getData('post');
    }

    public function getBookmarkHtml($post)
    {
        if ($this->_helper()->isBookmarksPost()) {
            return $this->setTemplate('aw_blog/bookmark.phtml')->setPost($post)->renderView();
        }
    }

    public function getComment()
    {
        if (!$this->hasData('commentCollection')) {
            $collection = Mage::getModel('blog/comment')
                ->getCollection()
                ->addPostFilter($this->getPost()->getPostId())
                ->setOrder('created_time', 'DESC')
                ->addApproveFilter(2)
            ;
            $collection->setPageSize((int)Mage::helper('blog')->commentsPerPage());
            $this->setData('commentCollection', $collection);
        }
        return $this->getData('commentCollection');
    }

    public function getCommentCount()
    {
        if (!$this->hasData('commentCountCollection')) {
            $collection = Mage::getModel('blog/comment')
                ->getCollection()
                ->addPostFilter($this->getPost()->getPostId())
                ->setOrder('created_time', 'DESC')
                ->addApproveFilter(2)
            ;
            
            $this->setData('commentCountCollection', $collection->count());
        }
        return $this->getData('commentCountCollection');
    }

    public function getCommentsEnabled()
    {
        return Mage::getStoreConfig('blog/comments/enabled');
    }

    public function getLoginRequired()
    {
        return Mage::getStoreConfig('blog/comments/login');
    }

    public function getFormAction()
    {
        return $this->getUrl('*/*/*');
    }

    public function getFormData()
    {
        return $this->getRequest();
    }

    protected function _prepareLayout()
    {
        $this->_prepareCrumbs()->_prepareHead();
    }

    protected function _beforeToHtml()
    {
        Mage::helper('blog/toolbar')->create(
            $this,
            array(
                 'orders'        => array('created_time' => $this->__('Created At'), 'email' => $this->__('Added By')),
                 'default_order' => 'created_time',
                 'dir'           => 'desc',
                 'limits'        => self::$_helper->commentsPerPage(),
                 'method'        => 'getComment'
            )
        );
        return $this;
    }

    protected function _prepareCrumbs()
    {
        $breadcrumbs = $this->getCrumbs();
        if ($breadcrumbs) {
            $helper = $this->_helper();
            $breadcrumbs->addCrumb(
                'blog',
                array(
                     'label' => $helper->getTitle(),
                     'title' => $this->__('Return to %s', $helper->getTitle()),
                     'link'  => Mage::getUrl($helper->getRoute()),
                )
            );

            $title = trim($this->getCategory()->getTitle());
            if ($title) {
                $breadcrumbs->addCrumb(
                    'cat',
                    array(
                         'label' => $title,
                         'title' => $this->__('Return to %s', $title),
                         'link'  => Mage::getUrl(
                             $helper->getRoute(), array('cat' => $this->getCategory()->getIdentifier())
                         ),
                    )
                );
            }

            $breadcrumbs->addCrumb(
                'blog_page', array('label' => htmlspecialchars_decode($this->getPost()->getTitle()))
            );
        }

        return $this;
    }

    protected function getCategory()
    {
        if (!$this->hasData('postCategory')) {
            $this->setData(
                'postCategory', Mage::getSingleton('blog/cat')->load($this->getRequest()->getParam('cat'), "identifier")
            );
        }

        return $this->getData('postCategory');
    }

    protected function _prepareHead()
    {
        parent::_prepareMetaData($this->getPost());

        return $this;
    }

    public function setCommentDetails($name, $email, $comment)
    {
        return $this
            ->setData('commentName', $name)
            ->setData('commentEmail', $email)
            ->setData('commentComment', $comment)
        ;
    }

    public function getCommentText()
    {
        $blogPostModelFromSession = Mage::getSingleton('customer/session')->getBlogPostModel();
        if ($blogPostModelFromSession) {
            return $blogPostModelFromSession->getComment();
        }

        if (!empty($this->_data['commentComment'])) {
            return $this->_data['commentComment'];
        }
        return;
    }

    public function getCommentEmail()
    {
        $blogPostModelFromSession = Mage::getSingleton('customer/session')->getBlogPostModel();
        if ($blogPostModelFromSession) {
            return $blogPostModelFromSession->getEmail();
        }

        if (!empty($this->_data['commentEmail'])) {
            return $this->_data['commentEmail'];
        } elseif ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
            return $customer->getEmail();
        }
        return;
    }

    public function getCommentName()
    {
        $blogPostModelFromSession = Mage::getSingleton('customer/session')->getBlogPostModel();

        $name = null;
        if ($blogPostModelFromSession) {
            $name = $blogPostModelFromSession->getUser();
        }
        if (!empty($this->_data['commentName'])) {
            $name = $this->_data['commentName'];
        } elseif ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
            $name = $customer->getName();
        }
        return trim($name);
    }

    public function getRelatedPost($catId, $postId)
    {
        $posts = Mage::getModel("blog/blog")->getCollection()
            ->addPresentFilter()
            ->addEnableFilter(AW_Blog_Model_Status::STATUS_ENABLED)
            ->addStoreFilter()
            ->addFieldToSelect('title')
            ->addFieldToSelect('created_time')
            ->addFieldToSelect('image_name')
            ->addFieldToSelect('identifier')
            ->setOrder('created_time', 'desc');
//         $posts->addFieldToFilter("awblog_post_cat.cat_id", array ('in' => $catId));
//         $posts->addFieldToFilter("main_table.post_id", array ('neq' => $postId));
        $posts->getSelect()
            ->joinLeft(
                array( 'awblog_post_cat' => Mage::getSingleton('core/resource')->getTableName('blog/post_cat') ),
                "main_table.post_id = awblog_post_cat.post_id",
                array(
                    'cat_id' => 'awblog_post_cat.cat_id'
                ))
     	    ->group('main_table.post_id')
            ->limit(5);
            
        return parent::_processCollection($posts);
    }

    public function getBlogCategoryIdentifier($blockId) {
        $result = '';

        if (!empty ($blockId)) {
            $search = array (' ', '---', '&');
            $replace = '-';
            $identifier = str_replace($search, $replace, $blockId);
            $result = sprintf("blog_%s_related_product", $identifier);
        }

        return strtolower($result);
    }
    
    public function getAwFeaturedProductRelated($blockId) {
        $result = '';
        $identifier = $this->getBlogCategoryIdentifier($blockId);
        $block = Mage::getModel('awfeatured/blocks')->loadByBlockId($identifier);
        
        if ($block) {
            $block->afterLoad();

            if ($block && $block->getRepresentation() && $block->getRepresentation()->getBlock() && $blockObj = $this->getLayout()->createBlock($block->getRepresentation()->getBlock())) {
                $blockObj->setAFPBlock($block);

                $result = $blockObj->toHtml();
            }
        }
        
        return $result;
    }
}
