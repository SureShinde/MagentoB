<?php

class Moxy_SocialCommerce_SettingsController
extends Mage_Core_Controller_Front_Action
{
    /**
     * Hold helper object
     */
    protected $helper;

    /**
     * Hold sanitized posted data from request object
     */
    protected $postData = null;

    /**
     * Setup things
     */
    public function _construct()
    {
        parent::_construct();
        $this->helper = Mage::helper('socialcommerce');

        if ($this->getRequest()->getPost()) {
            $postData = $this->getRequest()->getPost();
            $this->postData = $this->helper->stripArray($postData);
        }

    }


    # Settings page only available for logged user
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (! Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    # Display the settings page
    public function indexAction()
    {
        $pageTitle = Mage::app()->getStore()->getFrontendName() .
            " - " . $this->__('My Profile Settings');

        # Check if she got a profile
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $profiler = Mage::getModel('socialcommerce/profile')->load($customer->getId(), 'customer_id');

        if (! $profiler->getId()) {
            # Create a temporary profile for her.
			Mage::helper('socialcommerce')->createTemporaryProfile();
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($pageTitle);
        $this->renderLayout();
    }

    # Processing the form
    public function saveAction()
    {
        $next = $this->getRequest()->getParam('next');
        # Check if request is a post request
        if ($this->postData) {
            try {

                # Populate sent data, validate & sanitize

                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $customerId = $customer->getId();

                $postData = $this->postData;

                # Get username and slug it
                $postUsername = Mage::getModel('catalog/product_url')->formatUrlKey($postData['username']);

                $postAbout = $postData['about'];
                $postLocation = $postData['location'];
                $postDob = $postData['dob'];

                # Update the customer profile
                $profile = Mage::getModel('socialcommerce/profile')
                    ->load($customerId, 'customer_id');

                # Check if user has temporary profile and going to use different username
                if ($profile->getTemporary() && $profile->getUsername() != $postUsername) {

                    # Data validation
                    try {
                        Mage::helper('socialcommerce')->validateInput();
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }

                    # Check if username is still available
                    $usernameAvailable = Mage::helper('socialcommerce')->checkUsernameAvailable($postUsername);

                    if (! $usernameAvailable) {
                        throw new Exception("Username not available"); # Username not available
                    }

                    $oldUsername = $profile->getUsername();

                    $profile->setUsername($postUsername);
                }

                # Check image submission
                $postAvatar = Mage::helper('socialcommerce')->processAvatar($customerId);

                # Assign updated data
                $profile->setStatus(1);
                $profile->setWishlist(1);
                $profile->setTemporary(0);
                $profile->setAbout($postAbout);
                $profile->setLocation($postLocation);

                if ($postAvatar) {
                    //$profile->setCloudAvatar($postAvatar);
                    $profile->setAvatar($postAvatar);
                }

                $profile->save();

                if ($postDob) {
                    $tmp = new DateTime($postDob);
                    $customer->setDob($tmp->format('Y-m-d H:i:s'));
                    $customer->save();
                }

                # Success notification
                $message = 'Your preferences has been successfully saved.';
                Mage::getSingleton('customer/session')
                    ->addSuccess(Mage::helper('socialcommerce')->__($message));

            } catch (Exception $e) {

                # Error notification
                $message = $e->getMessage();
                Mage::getSingleton('customer/session')
                    ->addError(Mage::helper('socialcommerce')->__($message));

                # Form data
                Mage::getSingleton('customer/session')
                    ->setLocalshipData($this->postData);
            }
        }

        if ($next) {
            $this->_redirect(ltrim($next, '/'));
        } else {
            $this->_redirect('social/settings');
        }
    }

    public function addItemToCollectionAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        $wishlistId = (isset($this->postData['wishlist_id'])) ?
            $this->postData['wishlist_id'] : null;

        $itemDescription = (isset($this->postData['item_description'])) ?
            $this->postData['item_description'] : null;

        $productId = (isset($this->postData['product_id'])) ?
            $this->postData['product_id'] : null;

        $wishlistName = (isset($this->postData['name'])) ?
            $this->postData['name'] : null;

        $wishlistDescription = (isset($this->postData['desc'])) ?
            $this->postData['desc'] : null;

        try {

            # She want to create a new collection first
            if ($wishlistName) {
                $visibility = ($this->getRequest()->getParam('visibility', 0) === 'on' ? 1 : 0);
                $wishlist = $this->createNewCollection($customer->getId(), $wishlistName, $visibility, $wishlistDescription);
            } else {
                $wishlist = Mage::getModel('wishlist/wishlist')->load($wishlistId);
            }

            $product = Mage::getModel('catalog/product')->load($productId);

            $buyRequest = new Varien_Object(array(
                // 'description' => $itemDescription,
            ));

            $wishlist->addNewItem($product, $buyRequest);
            $wishlist->save();

            # Set description
            // if ($itemDescription) {
            //     # Below line is not working, should get the item ID in the wishlist, not the wishlist ID
            //     $item = Mage::getModel('wishlist/item')->load($wishlist->getId());
            //     $item->setDescription($itemDescription)->save();
            // }

            # Success notification
            if ($wishlistName) {
                $message = 'New collection created and product has been added into it.';
            } else {
                $message = 'Product added to your collection.';
            }

            Mage::getSingleton('core/session')
                ->addSuccess($message);

        } catch (Exception $e) {

            # Error notification
            $message = $e->getMessage();
            Mage::getSingleton('customer/session')
                ->addError(Mage::helper('socialcommerce')->__($message));
        }

        $this->_redirectReferer();

    }	

    public function createNewCollectionAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId();

        $visibility = ($this->getRequest()->getParam('visibility', 0) === 'on' ? 1 : 0);

        $wishlistName = (isset($this->postData['name'])) ?
            $this->postData['name'] : null;

        $username = (isset($this->postData['username'])) ?
            $this->postData['username'] : null;

        $desc = (isset($this->postData['desc'])) ?
        $this->postData['desc'] : null;
        


        try {

            # Check the limit is disabled
			/*Mage::helper('socialcommerce')->checkCollectionLimit($customerId);*/

            $this->createNewCollection($customerId, $wishlistName, $visibility, $desc);

            # Success notification
            $message = 'Your new collection "'.$wishlistName.'" has been successfully saved.';

            Mage::getSingleton('customer/session')
                ->addSuccess(Mage::helper('socialcommerce')->__($message));

        } catch (Exception $e) {

            # Error notification
            $message = $e->getMessage();
            Mage::getSingleton('customer/session')
                ->addError(Mage::helper('socialcommerce')->__($message));

        }

        $this->_redirect('user/' . $username);

    }

    protected function createNewCollection($customerId, $wishlistName, $visibility, $desc)
    {
        $wishlist = Mage::getModel('wishlist/wishlist');

        $cover = Mage::helper('socialcommerce')->processCover();
            
        $wishlist->setCustomerId($customerId)
            ->setName($wishlistName)
            ->setVisibility($visibility)
            ->setDesc($desc)
            ->generateSharingCode()
            //->setCloudCover($cover)
            ->setCover($cover)
            ->save();

        if ($preset_image = $_POST['preset_image']) {
            $wishlist->setCover($preset_image);
            $wishlist->save();
        }
        return $wishlist;

    }

    public function editCollectionAction()
    {
        # Check if request is a post request
        if ($this->postData) {

            try {

                # Populate sent data, validate & sanitize

                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

                $postData = $this->postData;
                
                $collectionId = $postData['collection_id'];
                if(!$collectionId) $collectionId = $postData['collection_id2'];
                $username = $postData['username'];

                $desc = (isset($this->postData['desc'])) ?
                $this->postData['desc'] : null;

                $title = (isset($this->postData['colname'])) ?
                $this->postData['colname'] : null;

                $del = (isset($this->postData['deleteButton']));
                

                $visibility = (isset($this->postData['visibility'])) ? true : false;

                # TODO need to check if submitted collection is belong to logged user.

                $wishlist = Mage::getModel('wishlist/wishlist')->load($collectionId);
                $wishlist->setVisibility($visibility);

                #Get wishlist name
                $wlname = $wishlist->getName();

                # Check the cover image upload and update description
                $cover = Mage::helper('socialcommerce')->processCover();

                if ($cover) {
                    //$wishlist->setCloudCover($cover);
                    $wishlist->setCover($cover);
                }

                if ($preset_image = $_POST['preset_image']) {
                    $wishlist->setCover($preset_image);
                }

                #for deleting collection
                if($del){
                $vst = '0';
                $viewupdate = $wishlist->setCustomerIdDel($vst);
                }
                
                $descupdate = $wishlist->setDesc($desc);

                if ($cover || $descupdate) {
                  
                    $wishlist->save();

                    # Success notification
                    $message = 'Your collection "'.$wlname.'" has been successfully updated.';

                    if ($del){$message = 'Your collection "'.$wlname.'" has been successfully deleted.';}

                    Mage::getSingleton('customer/session')->addSuccess(Mage::helper('socialcommerce')->__($message));

                }

            } catch (Exception $e) {

                # Error notification
                $message = $e->getMessage();
                Mage::getSingleton('customer/session')
                    ->addError(Mage::helper('socialcommerce')->__($message));

            }
        }
        $this->_redirect('user/' . $username);
    }  

    public function delitemCollectionAction()
    {
        # Get value from query string
        if ($this->getRequest()->getParam('user')) {

            try {

                # Populate sent data, validate & sanitize

                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

                $username = $this->getRequest()->getParam('user');
                $proid = $this->getRequest()->getParam('proid');
                $wlid = $this->getRequest()->getParam('wlid');

                $wishlist = Mage::getModel('wishlist/wishlist')->load($wlid);
                $name = $wishlist->getName();
                $urlname = Mage::getModel('catalog/product_url')->formatUrlKey($name);
                
                $w = Mage::getSingleton('core/resource')->getConnection('core_write');
                $result = $w->query('DELETE FROM wishlist_item WHERE wishlist_id ='.$wlid.' and product_id ='.$proid);

            } catch (Exception $e) {

                # Error notification
                $message = $e->getMessage();
                Mage::getSingleton('customer/session')
                    ->addError(Mage::helper('socialcommerce')->__($message));

            }
        }
        $this->_redirect('collections/' . $wlid . '-' . $urlname); 
    }  

}
