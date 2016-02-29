<?php
/**
 * SocialCommerce Helper
 *
 * @package   MoxyMagento
 * @copyright Copyright (c) 2015 Moxy (moxy.co.id)
 * @version   Fri Sep 25 11:33:43 2015
 */
class Moxy_SocialCommerce_Helper_Data
extends Mage_Core_Helper_Abstract
{
    public static function stripArray($input)
    {
        $result = array();
        foreach ($input as $key => $value) {
            $cleaned_key = static::stripSingle($key);
            if (is_array($value)) {
                $result[$cleaned_key] = static::stripArray($value); // Recurse
            } else {
                $result[$cleaned_key] = static::stripSingle($value);
            }
        }
        return $result;
    }

    public static function stripSingle($input)
    {
        # Remove invisible content
        $result = preg_replace(
            array(
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu',
            ),
            array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',), $input
        );

        # Remove all remainings tags,
        # replace multiple spaces and line breaks with single space,
        # and trim.
        return trim(preg_replace("!\s+!", ' ', strip_tags($result)));
    }

	public function checkUsernameAvailable($postUsername)
	{
		$profiler = Mage::getModel('socialcommerce/profile')->load($postUsername, 'username');

		# true means available
		return (! $profiler->getEntityId()) ? true : false;
	}

	# Create new custom rewrite for collection detail
    public function createNewCollectionRewrite($id, $slug)
    {
        # TODO loop thru all store ids, and create rewrite for each store id.
        // $store_id = Mage::app()->getStore()->getStoreId();
        $null = new Zend_Db_Expr("NULL");
        $results = Mage::getModel('core/url_rewrite')
            ->setStoreId(1)
            ->setOptions($null)
            ->setIdentifier('collections/' . $id . '-' . $slug)
            ->setRequestPath('collections/' . $id . '-' . $slug)
            ->setTargetPath('user/profile/collection/?id=' . $id . '-' . $slug)
            ->setIsSystem(true)
            ->setEntityType(2)
            ->save();

        /*$results = Mage::getModel('core/url_rewrite')
            ->setStoreId(2)
            ->setOptions('RP')
            ->setIdentifier('collections/' . $id . '-' . $slug)
            ->setRequestPath('collections/' . $id . '-' . $slug)
            ->setTargetPath('user/profile/collection/?id=' . $id . '-' . $slug)
            ->setIsSystem(true)
            ->setEntityType(2)
            ->save();*/
    }

	public function checkCollectionLimit($customerId)
    {
        $wishlistCollection = Mage::getModel('wishlist/wishlist')->getCollection()
            ->filterByCustomerId($customerId);

        $limit = Mage::helper('core_wishlist')->getWishlistLimit();

        if (Mage::helper('enterprise_wishlist')->isWishlistLimitReached($wishlistCollection)) {
            throw new Exception(sprintf('Only %d wishlists can be created.', $limit));
        }
    }

    public function createTemporaryProfile() {

        $customer = Mage::getSingleton('customer/session')->getCustomer();

        # Temporary username
        $username = Mage::getModel('catalog/product_url')->formatUrlKey($customer->getName());
        $profile = Mage::getModel('socialcommerce/profile')->load($username, 'username')->getData();

        # If username exists, improvise
        if ($profile) {

            for ($i = 1; $i < 101; $i++) {
                $slug = $username . '-' . substr(uniqid(), 7);
                $profile = Mage::getModel('socialcommerce/profile')->load($slug, 'username')->getData();

                if (empty($profile)) {
                    $username = $slug;
                    break;
                }
            }
        }

        # Create new customer profile
        $profile = Mage::getModel('socialcommerce/profile');

        # Assign data
        $profile->setCustomerId($customer->getId());
        $profile->setStatus(1);
        $profile->setWishlist(1);
        $profile->setTemporary(1);
        $profile->setUsername($username);

        $profile->save();

        return $username;

    }

    # Input validation
    public function validateInput()
    {
        $postData = $this->postData;
        $postUsername = $postData['username'];
        if (! $postUsername) {
            throw new Exception("Please fill username field");
        }
    }

    public function migrateAvatar($profile)
    {
        $customerAvatar = null;
		echo file_exists(getcwd() . "/media/" . $profile->getAvatar);
		echo '\n';
		if (file_exists(getcwd() . "/media/" . $profile->getAvatar)) {

            $cloudinary = \Cloudinary\Uploader::upload($upFileTmpName, [
                'crop'      => 'fill',
                'width'     => '800',
                'height'    => '800',
                'gravity'   => 'face',
                'format'    => 'jpg',
                'tags'      => ['social_commerce', 'profile_picture',],
            ]);

            $customerAvatar = $cloudinary['public_id'];
			$profile->setCloudAvatar($customerAvatar);
			$profile->save();

        }

        return $customerAvatar;

    }

    # Upload avatar image
    public function processAvatar($customerId)
    {
        $customerAvatar = null;

        if ($upFileTmpName = $_FILES['avatar']['tmp_name']) {

            $image_name = substr(str_shuffle(md5(time())),0,5).'.jpg';
            
            $uploader = new Varien_File_Uploader('avatar');   
            $uploader->setAllowedExtensions(array('jpg','jpeg','png')); 
            $uploader->setAllowRenameFiles(true);  
            $uploader->setFilesDispersion(true);  
            $result = $uploader->save ('media'. DS .'avatar', $image_name);

            $customerAvatar = $result['path'].$result['file'];
            $image = new Varien_Image($customerAvatar);
            $image->constrainOnly(true);
            $image->keepAspectRatio(false);;
            $image->keepFrame(false);
            $image->setWatermarkImageOpacity(0);
            $image->adaptiveResize(800,800);
            $image->save($customerAvatar);

        }

        return ltrim($result['file'], '/');

    }

    # Upload cover image
	public function processCoverset($imagePath)
    {
        $return = null;

        if (file_exits($imagePath)) {

                $cloudinary = \Cloudinary\Uploader::upload($imagePath, [
                    'crop'      => 'fill',
                    'width'     => '800',
                    'height'    => '800',
                    'format'    => 'jpg',
                    'tags'      => ['social_commerce', 'collection_cover',],
                ]);
                $return = $cloudinary['public_id'];
        }

        return $return;

    }
	#
	#
	protected function download_image($image_url) {
		$image_file = 'media'. DS .'collection-cover'. DS . substr(str_shuffle(md5(time())),0,5).'.jpg';
		$dirname = dirname($image_file);
                if (!is_dir($dirname))
                {
                    mkdir($dirname, 0777, true);
                }
		$fp = fopen($image_file, 'w+');

		$ch = curl_init($image_url);
		curl_setopt($ch, CURLOPT_FILE, $fp);          // output to file
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1000);      // some large value to allow curl to run for a long time
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		return $image_file;
	}

    public function processCover()
    {
        if($_FILES) {
            if ($_FILES['cover']['tmp_name']) {
                $image_name = substr(str_shuffle(md5(time())),0,5).'.jpg';
                
                $uploader = new Varien_File_Uploader('cover');   
                $uploader->setAllowedExtensions(array('jpg','jpeg','png')); 
                $uploader->setAllowRenameFiles(true);  
                $uploader->setFilesDispersion(true);  
                $result = $uploader->save ('media'. DS .'collection-cover', $image_name);
  
                $imageUrl = $result['path'].$result['file'];
                $image = new Varien_Image($imageUrl);
                $image->constrainOnly(true);
                $image->keepAspectRatio(false);;
                $image->keepFrame(false);
                $image->setWatermarkImageOpacity(0);
                $image->adaptiveResize(800,800);
                $image->save($imageUrl);

                return ltrim($result['file'], '/');
            }

            if ($_POST['image_url']) {
                $upFileTmpName = $this->download_image($_POST['image_url']);
                $imageUrl = 'media'. DS .'collection-cover'. DS . basename($upFileTmpName);
                $image = new Varien_Image ( $imageUrl );
                $image->constrainOnly(true);
                $image->keepAspectRatio(false);
                $image->keepFrame(false);
                $image->setWatermarkImageOpacity(0);
                $image->adaptiveResize(800,800);
                $image->save ($imageUrl);

                return ltrim(basename($upFileTmpName), '/');
            }

            return null;
        }
    }

    # Check if custom route available for user URL
    public function checkRouteAvailable($postUsername)
    {
        $routeExists = Mage::getModel('core/url_rewrite')
            ->getCollection()
            ->addFieldToFilter('identifier', 'user/' . $postUsername);

        return (count($routeExists) == 0) ? true : false;
    }


    # Check if the customer has public profile already
    public function checkExistingProfile($customerId)
    {
        $profiler = Mage::getModel('socialcommerce/profile')
            ->load($customerId, 'customer_id');

        return ($profiler->getEntityId()) ? true : false;
    }

    # Create new custom rewrite for customer page
    public function createNewRewrite($postUsername)
    {
        # TODO loop thru all store ids, and create rewrite for each store id.
        // $store_id = Mage::app()->getStore()->getStoreId();
        $null = new Zend_Db_Expr("NULL");
        $results = Mage::getModel('core/url_rewrite')
            ->setStoreId(1)
            ->setOptions($null)
            ->setIdentifier('user/' . $postUsername)
            ->setRequestPath('user/' . $postUsername)
            ->setTargetPath('user/profile/index/?u=' . $postUsername)
            ->setIsSystem(true)
            ->setEntityType(2)
            ->save();

        /*$results = Mage::getModel('core/url_rewrite')
            ->setStoreId(2)
            ->setOptions('RP')
            ->setIdentifier('user/' . $postUsername)
            ->setRequestPath('user/' . $postUsername)
            ->setTargetPath('user/profile/index/?u=' . $postUsername)
            ->setIsSystem(true)
            ->setEntityType(2)
            ->save();*/
    }

    # Delete old rewrite in case the user updated her username
    public function deleteRewrite($oldUsername) {}

    # Check if custom route available for collection detail page
    public function checkCollectionRouteAvailable($id, $slug)
    {
        $routeExists = Mage::getModel('core/url_rewrite')
            ->getCollection()
            ->addFieldToFilter('identifier', 'collections/' . $id . '-' . $slug);

        return (count($routeExists) == 0) ? true : false;
    }

    public function getProfileInformation($customer_id) {
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $profile = Mage::getModel('socialcommerce/profile')->load($customer_id, 'customer_id');
        $img = Mage::getDesign()->getSkinUrl('images/').'avatar-f.jpg';
        $title = $customer->getName();
        $url = '';

        if ($profile->getUsername()) {
            $title = $profile->getUsername();
            $url = Mage::getUrl('user/'.$title);

            if ($profile->getCloudAvatar()) {
                $options = array(
                    'secure' => true,
                    'width' => 35,
                    'height' => 35,
                    'crop' => 'fill',
                    'format' => 'jpg'
                );
                $img = cloudinary_url($profile->getCloudAvatar(), $options);

            } elseif ($profile->getAvatar()) {
               $base_media = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
                $img = $base_media.'avatar/'.$profile->getAvatar();
            }

        } else {
            if ($customer->getGender() == 1)
                $img = Mage::getDesign()->getSkinUrl('images/').'avatar-m.jpg';
        }
        return array('profile_image' => $img, 'display_name' => $title, 'url' => $url);
    }

}
