<?php
/**
 * One page checkout status
 *
 * @category   Mage
 * @category   Mage
 * @package    Mage_Checkout
 * @author     Bilna Dev Team
 */
class Mage_Checkout_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Abstract
{
    protected $_rates;
    protected $_address;

    public function getShippingRates()
    {

        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();

            $groups = $this->getAddress()->getGroupedAllShippingRates();
            /*
            if (!empty($groups)) {
                $ratesFilter = new Varien_Filter_Object_Grid();
                $ratesFilter->addFilter(Mage::app()->getStore()->getPriceFilter(), 'price');

                foreach ($groups as $code => $groupItems) {
                    $groups[$code] = $ratesFilter->filter($groupItems);
                }
            }
            */

            return $this->_rates = $groups;
        }

        return $this->_rates;
    }

    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }

    public function getAddressShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
    }

    /* show whether all the items have available express shipping method */
    public function showExpressShippingMethod()
    {
        $showExpress = true;

        $cartItems = Mage::getModel("checkout/cart")->getItems();
        foreach($cartItems as $item) {
            $product = $item->getProduct()->load();
            if ( is_null($product->getExpressShipping()) || $product->getExpressShipping() == 0 ) {
                $showExpress = false;
                break;
            }
        }

        return $showExpress;
    }

    /* check the number of sales order with express shipping method */
    public function checkExpressShippingSalesCount()
    {
        $showExpress = true;
        $expressSOLimit = 600;

        /* Format our dates */
        $fromDate = date('Y-m-d 17:00:00', strtotime("-1 day"));
        $toDate = date('Y-m-d H:i:s');
         
        /* Get the collection */
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
            ->addAttributeToFilter('shipping_method', array('like' => '%Express_Shipping%'));
        $order_count = $orders->getSize();

        if ($order_count > $expressSOLimit)
            $showExpress = false;

        return $showExpress;
    }

}
