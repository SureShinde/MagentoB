<?php
class Moxy_MoxyMagazine_Model_Api extends Mage_Api_Model_Resource_Abstract
{

	public function listCollection($sessionId)
    {

		$server_name = $_SERVER['SERVER_NAME'];

        $session_path = Mage::getBaseDir('session');
        $file= $session_path . '/sess_' . $sessionId;
        $contents=file_get_contents($file);

        if (!$contents) {
            Mage::log('File '. $file .' not found');
            return $data;
        }

        session_start();
        session_decode($contents);

        Mage::getSingleton('core/session', array('name' => 'frontend'));

        // Customer Session
        $session = Mage::getSingleton('customer/session');

        $userId = $_SESSION['customer']['id'];
		$customer = $_SESSION['customer'];

        $customer = $_SESSION['customer'];
		$wishlists = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer)->getCollection()->addFilter('visibility', 1)->setOrder('updated_at', 'DESC');
		//$wishlists = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer)->getCollection()->addFilter('visibility', 1)->setOrder('updated_at', 'DESC');
		/*
		Mage::log(print_r($wishlists), true);
		foreach ($wishlists as $wishlist) {

			Mage::log(">>>>>>>>");
			Mage::log(print_r($wishlist, true));
		}
		 */
		#$wishlists = Mage::getModel('wishlist/wishlist')->getCollection()->addFilter('visibility', 1)->setOrder('updated_at', 'DESC');
		return $wishlists;
		/*
        $wishlist = Mage::getModel('wishlist/wishlist');

        $wishlist->setCustomerId($customerId)
            ->setName($wishlistName)
            ->setVisibility($visibility)
            ->generateSharingCode()
            ->save();

        # Create rewrite, first check the availability
        $slug = Mage::getModel('catalog/product_url')->formatUrlKey($wishlistName);
        $routeAvailable = $this->checkCollectionRouteAvailable($wishlist->getId(), $slug);

        if ($routeAvailable) {

            # Create new route rewrite
            $this->createNewCollectionRewrite($wishlist->getId(), $slug);

        } else {

            # If not available, try another else

            $error = false;
            for ($i = 1;;$i++) {
                $check = $slug . '-' . substr(uniqid(), 7);
                if ($this->checkCollectionRouteAvailable($wishlist->getId(), $check)) break;
                if ($i == 100) {
                    $error = true;
                    break;
                }
            }

            if ($error) {
                throw new Exception("Error Processing Request", 1);
            }

            # Create new route rewrite
            $this->createNewCollectionRewrite($wishlist->getId(), $slug);

        }

        # Check if rewrite still available
        if (! $routeAvailable) {
            throw new Exception("Username not available"); # Route not available
        }

        return $wishlist;
		 */

    }

	public function getQuoteCart($sessionId) {

		$server_name = $_SERVER['SERVER_NAME'];

        $session_path = Mage::getBaseDir('session');
        $file= $session_path . '/sess_' . $sessionId;
        $contents=file_get_contents($file);

        if (!$contents) {
            Mage::log('File '. $file .' not found');
            return [];
        }

        session_start();
        session_decode($contents);

        Mage::getSingleton('core/session', array('name' => 'frontend'));

        // Customer Session
        $session = Mage::getSingleton('customer/session');

        $userId = $_SESSION['customer']['id'];
		$customer = $_SESSION['customer'];
		Mage::log($customer);
		$quoteCollection = Mage::getModel('sales/quote')->getCollection();
		$quoteCollection->addFieldToFilter('customer_id', $userId);
		$quoteCollection->addOrder('updated_at');
		$quote = $quoteCollection->getLastItem();
		$products = array();
		foreach ($quote->getAllItems() as $item) {
			//echo var_dump($quoteItem);

			$product = $item->getProduct();//Mage::getModel('catalog/product')->load($item->getItemId());

			array_push($products, array(
				"id" => $product->getId(),
				"name" => $product->getName(),
				"image" => (string)Mage::helper('catalog/image')->init($product, 'thumbnail'),
				"url" => $product->getProductUrl(),
				"qty" => $item->getQty(),
				"price" => $item->getPrice(),
				"item_id" => $item->getId()
			));
		}
		//Mage::log($products);
		//return $quote['entity_id'];
		$quoteData= $quote->getData();
		$grandTotal=$quoteData['grand_total'];
		return array(
			"entity_id" => $quote['entity_id'],
			"cart_items" => $products,
			"cart_total" => $grandTotal
		);

		/*
		$quote = Mage::getSingleton('sales/quote')->loadByCustomer($customer);
		Mage::log(var_dump($quote));
		Mage::log( $quote->getId());
		Mage::log($quote['entity_id']);
		return $quote['entity_id'];
		 */

	}

    public function getSessionData($sessionId)
    {
        $data = [];

        $server_name = $_SERVER['SERVER_NAME'];

        $session_path = Mage::getBaseDir('session');
        $file= $session_path . '/sess_' . $sessionId;
        $contents=file_get_contents($file);

        if (!$contents) {
            Mage::log('File '. $file .' not found');
            return $data;
        }

        session_start();
        session_decode($contents);

        Mage::getSingleton('core/session', array('name' => 'frontend'));

        // Customer Session
        $session = Mage::getSingleton('customer/session');

        // $data["session"] = $_SESSION;

        // This is for magento enterprise
        // $customerId = $_SESSION['customer']['id'];

        // This is for magento community
        $customerId = $_SESSION['customer_base']['id'];

        if ($customerId) {
            $data["id"] = $customerId;

            // Get customer's wishlist
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($_SESSION['customer']);
            $data["wishlist_count"] = strval(count($wishlist->getItemCollection()));

            // Get customer's cart
            $checkout = Mage::getSingleton('checkout/session');
            $quote_id = $checkout['quote_id_1'];
            $data['quote_id'] = $quote_id;
        }

        return $data;
    }
}
