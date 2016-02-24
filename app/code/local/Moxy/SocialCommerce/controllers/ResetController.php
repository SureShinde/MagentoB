<?php 
class Moxy_SocialCommerce_ResetController extends Mage_Core_Controller_Front_Action
{
	public function resetCollectionAction()
    {
        $con = Mage::getSingleton('core/resource')->getConnection('core_write');
        $ids = Mage::getModel('wishlist/wishlist')->getCollection()->getAllIds();
        foreach ($ids as $id) {
          $wishlist = Mage::getModel('wishlist/wishlist')->load($id);
          $counter = $wishlist->getCounter();

          $movedata = $con->query('UPDATE wishlist SET last_counter = '.$counter.' WHERE wishlist_id = '.$id);
        }
        
          try {

            $reset = $con->query('UPDATE wishlist SET counter = "0"');
            echo "Data reseted successfully.";

        } catch (Exception $e){
            echo $e->getMessage(); } 

    }    
}
