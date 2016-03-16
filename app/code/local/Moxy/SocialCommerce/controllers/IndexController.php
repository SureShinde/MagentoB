<?php

class Moxy_SocialCommerce_IndexController
extends Mage_Core_Controller_Front_Action
{

   public function filterPresetImageAction() {
           
       $category_id = $_POST['category_id'];
       $images = Mage::getModel('socialcommerce/collectioncover')->getCollection()->addFieldToFilter('category_id', $category_id)->setCurPage(1)->setPageSize(12);
       $counter = 1; 
       foreach($images as $image) { 
           ?>
                  <div class="cg-col-sm-3 cg-col-xs-4 bottom-spacer choose-cover">
                    <span class="overlayer-pink">
                      <i class="fa fa-check checked"></i>
                    </span>
                   <img src="/media/<?php echo $image['image'];?>" width="100%" alt=""> 
                  </div>
                 <?php 
           $counter++;
       }  ?>
                 </div>
<?php
       
   }

    public function indexAction()
    {

        $defaultPageSize = 24;

        # UGC Collections, display user's collections here
        $curPage = (int)$this->getRequest()->getParam('p');

        # -TODO-
        # Below line will retrieve ALL visible wishlists, including EMPTY wishlists.
        # Should find a way to exclude empty wishlists from collections and empty name
        $wishlists = Mage::getModel('wishlist/wishlist')
            ->getCollection()
            ->addFilter('visibility', 1)
            ->addFieldToFilter('name', array('neq' => 'NULL'))
            ->addFieldToFilter('name', array('neq' => ' '))
            ->setOrder('updated_at', 'DESC');

        # We need to get all wishlist collection, and filter by:
        # - empty collection -> exclude
        # - default wishlist -> exclude
        $collections = [];

        foreach ($wishlists as $wishlist) {

            # Excluding the default wishlist
            if (Mage::helper('wishlist')->isWishlistDefault($wishlist)) {
                continue;
            }

            $collectionCover = $wishlist->getCover();
            $collectionCloudCover = $wishlist->getCloudCover();

            $items = $wishlist->getItemCollection()->setOrder('added_at', 'DESC');
            $firstItem = $items->getFirstItem();
            $collectionProductImage = null;

            if ($firstItem->getProductId()) {
                $product = Mage::getModel('catalog/product')->load($firstItem->getProductId());
                $collectionProductImage = $product->getImageUrl();
            }

            # Check empty wishlist and get product image
            # for filtering items in product should be more than 4
            if ($items->count() < 4) continue;

            $collectionName = $wishlist->getName();

            $collections[] = [
                'id'            => $wishlist->getId(),
                'customer_id'   => $wishlist->getCustomerId(),
                'name'          => $collectionName,
                'slug'          => $wishlist->getId().'-'.Mage::getModel('catalog/product_url')->formatUrlKey($collectionName),
                'cover'         => $collectionCover,
                'cloud_cover'   => $collectionCloudCover,
                'product_image' => $collectionProductImage,
            ];
        }

        $itemCount = count($collections);

        $lastPageNumber = ceil($itemCount / $defaultPageSize);

        # Simple validation for pagination
        if ($curPage == null || $curPage < 1 || $curPage > $lastPageNumber) {
            $curPage = 1;
        }

        $slice = ($curPage - 1) * $defaultPageSize;

        $collections = array_slice($collections, $slice, $defaultPageSize);

        $this->loadLayout();

        # Assign data
        $block = $this->getLayout()->getBlock('user_content');

        # Assign profile and customer data
        $block->setWishlists($collections);

        $block->setCurPage($curPage);
        $block->setLastPageNumber($lastPageNumber);

        # Set page title
        $pageTitle = $this->__('Collections');
        $this->getLayout()->getBlock('head')->setTitle($pageTitle);

        $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');

        $this->renderLayout();

    }
}