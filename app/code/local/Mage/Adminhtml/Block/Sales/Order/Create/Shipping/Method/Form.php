<?php

class Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected $_rates;

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_shipping_method_form');
    }

    /**
     * Retrieve quote shipping address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     */
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            /*
            if (!empty($groups)) {

                $ratesFilter = new Varien_Filter_Object_Grid();
                $ratesFilter->addFilter($this->getStore()->getPriceFilter(), 'price');

                foreach ($groups as $code => $groupItems) {
                    $groups[$code] = $ratesFilter->filter($groupItems);
                }
            }
            */
            return $this->_rates = $groups;
        }
        return $this->_rates;
    }

    /**
     * Rertrieve carrier name from store configuration
     *
     * @param   string $carrierCode
     * @return  string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title', $this->getStore()->getId())) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Retrieve current selected shipping method
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    /**
     * Check activity of method by code
     *
     * @param   string $code
     * @return  bool
     */
    public function isMethodActive($code)
    {
        return $code===$this->getShippingMethod();
    }

    /**
     * Retrieve rate of active shipping method
     *
     * @return Mage_Sales_Model_Quote_Address_Rate || false
     */
    public function getActiveMethodRate()
    {
        $rates = $this->getShippingRates();
        if (is_array($rates)) {
            foreach ($rates as $group) {
                foreach ($group as $code => $rate) {
                    if ($rate->getCode() == $this->getShippingMethod()) {
                        return $rate;
                    }
                }
            }
        }
        return false;
    }

    public function getIsRateRequest()
    {
        return $this->getRequest()->getParam('collect_shipping_rates');
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(
            Mage::helper('tax')->getShippingPrice(
                $price,
                $flag,
                $this->getAddress(),
                null,
                //We should send exact quote store to prevent fetching default config for admin store.
                $this->getAddress()->getQuote()->getStore()
            ),
            true
        );
    }

    /* show whether all the items have available express shipping method */
    public function showExpressShippingMethod()
    {
        $showExpress = true;

        $cartItems = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getAllItems();
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
        if ($this->isExpressShippingEnabled() === false) {
            return false;
        }
        
        $showExpress = true;

        $limit = (int) Mage::getStoreConfig('bilna_expressshipping/orderlimit/limit');
        $todayDate = Mage::getModel('core/date')->date('Y-m-d');

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = "sales_order_daily_count";
        $query = "SELECT sales_count FROM $table WHERE sales_date = '$todayDate' LIMIT 1";
        $salesCount = $readConnection->fetchOne($query);

        if ($salesCount) {
            if ($salesCount >= $limit) {
                $showExpress = false;
            }
        }

        return $showExpress;
    }

    /* check whether express shipping enabled or not */
    public function isExpressShippingEnabled()
    {
        $config = Mage::getStoreConfig('bilna_expressshipping/status/enabled');
        if ($config) {
            return true;
        }
        return false;
    }
}
