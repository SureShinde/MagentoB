<?php

require_once 'Mage/Checkout/controllers/CartController.php';
class Acommerce_AddMultipleProducts_AddController extends Mage_Checkout_CartController
{
    public function indexAction()
    {
        $prodId = Mage::app()->getRequest()->getParam('checkProductId');
        $prodIds = explode(',', $prodId);
        $prodQty = Mage::app()->getRequest()->getParam('checkQty');
        $prodQtys = explode(',', $prodQty);

        $errMsg = '';
        $addedProduct = array();

        $cart = $this->_getCart();
        foreach($prodIds as $idx=>$prodId){
            try {

                $product = Mage::getModel('catalog/product')
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->load($prodId);

                if ($product->getId()) {
                    $eventArgs = array(
                        'product' => $product,
                        'qty' => $prodQtys[$idx],
                        'additional_ids' => array(),
                        'request' => $this->getRequest(),
                        'response' => $this->getResponse(),
                    );

                    Mage::dispatchEvent('checkout_cart_before_add', $eventArgs);
                    $cart->addProduct($product, $prodQtys[$idx]);
                    Mage::dispatchEvent('checkout_cart_after_add', $eventArgs);
                    Mage::dispatchEvent('checkout_cart_add_product', array('product'=>$product));

                    array_push($addedProduct, $product->getName());
                }
            } catch (Mage_Core_Exception $e){
                $errMsg .= $e->getMessage();
                // Mage::getSingleton('core/session')->addError($e->getMessage());
                // $this->_redirectReferer();
            }
        }

        $cart->save();

        if($errMsg!=''){
            Mage::getSingleton('checkout/session')->addError($errMsg);
        } else {
            $succeedProduct = count($addedProduct);
            if($succeedProduct>0){
                $listAddedProduct = implode($addedProduct, ', ');
                if($succeedProduct==1){
                    $message = $this->__('%s was successfully added to your shopping cart.', $listAddedProduct);
                } else {
                    $message = $this->__('%s were successfully added to your shopping cart.', $listAddedProduct);
                }
            }
            Mage::getSingleton('checkout/session')->addSuccess($message);
        }

        $this->_redirect('checkout/cart');
    }
}