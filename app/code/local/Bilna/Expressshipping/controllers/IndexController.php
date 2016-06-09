<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Bilna_Expressshipping_IndexController extends Mage_Core_Controller_Front_Action
{
    public function getETAAction()
    {
        $expressShippingHelper = Mage::helper('bilna_expressshipping');
        $dateTime = Mage::getModel('core/date')->timestamp(time());
	    $orderDate = date('d F Y', $dateTime);
	    $nextDay = date('d F Y', strtotime($orderDate . ' +1 day'));
	    $isExpressShippingEnabled = Mage::getStoreConfig('bilna_expressshipping/status/enabled');

		if ($isExpressShippingEnabled) {
	    	if ($expressShippingHelper->isBeforeCutOffTime()) {
	            $img = Mage::getModel('core/design_package' )->getSkinUrl('images/') . 'VIP-SHIPMENT-ICON-GREEN.png';
	            $eta = "<div class='in-block container-express'>
	                    <img src='".$img."' class='in-block margin-right-8'  style='width:35px;'>
	                    <div class='in-block'>
	                        <p class='bold'>Tersedia Pengiriman Ekspress!
	                            <span class='tool-box'>
	                                <span class='question'>?</span> 
	                                <span class='pops'>
	                                    <span class='arrow_box in-block stay-float'>
	                                        <span class='logo'>Selesaikan transaksi sebelum jam " . $expressShippingHelper->getDisplayCutOffTime() . " WIB untuk terima produk hari ini. Pengiriman Express tersedia di <strong>Jakarta &amp; Bekasi</strong> khusus produk dengan icon <span class='in-tab'>
	                                            <img src='".$img."'  style='width:12px;'></span>. tidak dapat digabungkan dengan produk tanpa ikon tersebut &amp; COD</span>
	                                    </span>
	                                </span>
	                            </span>
	                        </p>
	                        <p>Terima pesanan tanggal ".$orderDate."</p>
	                    </div>
	                </div>";
	        } else {
	            $img = Mage::getModel( 'core/design_package' )->getSkinUrl('images/') . 'VIP-SHIPMENT-ICON-GREEN.png';
	            $eta = "<div class='in-block container-express'>
	                        <img src='".$img."' class='in-block margin-right-8'  style='width:35px;'>
	                        <div class='in-block'>
	                            <p class='bold'>Tersedia Pengiriman Ekspress!
	                                <span class='tool-box'>
	                                    <span class='question'>?</span> 
	                                    <span class='pops'>
	                                        <span class='arrow_box in-block stay-float'>
	                                            <span class='logo'>Pengiriman Express tersedia di <strong>Jakarta &amp; Bekasi</strong> khusus produk dengan icon <span class='in-tab'>
	                                            <img src='".$img."'  style='width:12px;'></span>. tidak dapat digabungkan dengan produk tanpa ikon tersebut &amp; COD</span>
	                                        </span>
	                                    </span>
	                                </span>
	                            </p>
	                            <p>Terima pesanan tanggal ".$nextDay."</p>
	                        </div>
	                </div>";
	        }
	    }
	    echo $eta;
    }
}
