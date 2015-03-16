<?php
class Alw_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
	/********************************   Start ***************************************************/
	//all function is used to convert number into words
	
	public function convertNumber($num)  
	{  
	   list($num, $dec) = explode(".", $num);  
	  
	   $output = "";  
	  
	   if($num{0} == "-")  
	   {  
	      $output = "negative ";  
	      $num = ltrim($num, "-");  
	   }  
	   else if($num{0} == "+")  
	   {  
	      $output = "positive ";  
	      $num = ltrim($num, "+");  
	   }  
	  
	   if($num{0} == "0")  
	   {  
	      $output .= "zero";  
	   }  
	   else  
	   {  
	      $num = str_pad($num, 36, "0", STR_PAD_LEFT);  
	      $group = rtrim(chunk_split($num, 3, " "), " ");  
	      $groups = explode(" ", $group);  
	  
	      $groups2 = array();  
	      foreach($groups as $g) $groups2[] = $this->convertThreeDigit($g{0}, $g{1}, $g{2});  
	  
	      for($z = 0; $z < count($groups2); $z++)  
	      {  
		 if($groups2[$z] != "")  
		 {  
		    $output .= $groups2[$z].$this->convertGroup(11 - $z).($z < 11 && !array_search('', array_slice($groups2, $z + 1, -1))  
		     && $groups2[11] != '' && $groups[11]{0} == '0' ? " and " : ", ");  
		 }  
	      }  
	  
	      $output = rtrim($output, ", ");  
	   }  
	  
	   if($dec > 0)  
	   {  
	      $output .= " point";  
	      for($i = 0; $i < strlen($dec); $i++) $output .= " ".$this->convertDigit($dec{$i});  
	   }  
	  
	   return $output;  
	}  
	  
	  
	  
	public function convertGroup($index)  
	{  
	   switch($index)  
	   {  
	      case 11: return " decillion";  
	      case 10: return " nonillion";  
	      case 9: return " octillion";  
	      case 8: return " septillion";  
	      case 7: return " sextillion";  
	      case 6: return " quintrillion";  
	      case 5: return " quadrillion";  
	      case 4: return " trillion";  
	      case 3: return " billion";  
	      case 2: return " million";  
	      case 1: return " thousand";  
	      case 0: return "";  
	   }  
	}  
	  
	public function convertThreeDigit($dig1, $dig2, $dig3)  
	{  
	   $output = "";  
	  
	   if($dig1 == "0" && $dig2 == "0" && $dig3 == "0") return "";  
	  
	   if($dig1 != "0")  
	   {  
	      $output .= $this->convertDigit($dig1)." hundred";  
	      if($dig2 != "0" || $dig3 != "0") $output .= " and ";  
	   }  
	  
	   if($dig2 != "0") $output .= $this->convertTwoDigit($dig2, $dig3);  
	   else if($dig3 != "0") $output .= $this->convertDigit($dig3);  
	  
	   return $output;  
	}  
	  
	public function convertTwoDigit($dig1, $dig2)  
	{  
	   if($dig2 == "0")  
	   {  
	      switch($dig1)  
	      {  
		 case "1": return "ten";  
		 case "2": return "twenty";  
		 case "3": return "thirty";  
		 case "4": return "forty";  
		 case "5": return "fifty";  
		 case "6": return "sixty";  
		 case "7": return "seventy";  
		 case "8": return "eighty";  
		 case "9": return "ninety";  
	      }  
	   }  
	   else if($dig1 == "1")  
	   {  
	      switch($dig2)  
	      {  
		 case "1": return "eleven";  
		 case "2": return "twelve";  
		 case "3": return "thirteen";  
		 case "4": return "fourteen";  
		 case "5": return "fifteen";  
		 case "6": return "sixteen";  
		 case "7": return "seventeen";  
		 case "8": return "eighteen";  
		 case "9": return "nineteen";  
	      }  
	   }  
	   else  
	   {  
	      $temp = $this->convertDigit($dig2);  
	      switch($dig1)  
	      {  
		 case "2": return "twenty-$temp";  
		 case "3": return "thirty-$temp";  
		 case "4": return "forty-$temp";  
		 case "5": return "fifty-$temp";  
		 case "6": return "sixty-$temp";  
		 case "7": return "seventy-$temp";  
		 case "8": return "eighty-$temp";  
		 case "9": return "ninety-$temp";  
	      }  
	   }  
	}  
	  
	public function convertDigit($digit)  
	{  
	   switch($digit)  
	   {  
	      case "0": return "zero";  
	      case "1": return "one";  
	      case "2": return "two";  
	      case "3": return "three";  
	      case "4": return "four";  
	      case "5": return "five";  
	      case "6": return "six";  
	      case "7": return "seven";  
	      case "8": return "eight";  
	      case "9": return "nine";  
	   }  
	}  
	
	/********************************   End      ***************************************************/
	
	//Get if the product is new
	public function isProductNew($product)
	{
		$todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
		$newsFrom = $product->getData('news_from_date');
		$newsTo = $product->getData('news_to_date');
		if(($newsFrom!=''&&$newsTo=='')||($newsFrom==''&&$newsTo!='')){
		if(($todayDate >= $newsFrom) && (($todayDate <= $newsTo)||($newsTo==null))):
			return 1;
		else:
			return 0;
		endif;
		}
		else{
			return 0;
		}
	}
	
	//Get if the product is new
	public function isProductSpecial($_product)
	{
		// Get the Special Price
		$specialprice = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialPrice(); 
		// Get the Special Price FROM date
		$specialPriceFromDate = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialFromDate();
		// Get the Special Price TO date
		$specialPriceToDate = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialToDate();
		// Get Current date
		$today =  time();

			if ($specialprice):
			if($today >= strtotime( $specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime( $specialPriceFromDate) && is_null($specialPriceToDate)):
			return 1;
			else:	
				return 0;
			endif;
		endif;
	}
	
	
	public function getContinueShoppingCategoryUrl(){
			$continueShoppingCategoryUrl = false;

			/**
			 * If we are on the cart page just after we added an item to the cart,
			 * we use its category for "Continue Shopping" redirect
			 */
				$lastProductAddedToCartId = Mage::getSingleton('checkout/session')->getLastAddedProductId(true);
				if($lastProductAddedToCartId) {
					$productCategoryIdsArray = Mage::getModel('catalog/product')->load($lastProductAddedToCartId)->getCategoryIds();
					$continueShoppingCategoryUrl = Mage::getModel('catalog/category')->load($productCategoryIdsArray[0])->getUrl();
				}
		 
			/**
			 * Otherwise, if we are on the cart page at any other moment, we make sure
			 * that all items do belong to the same category and, if this is
			 * the case, we use this unique category for "Continue Shopping" redirect
			 * 
			 * If all cart items do not belong to the same category, we are
			 * compelled to let Magento process in its standard way because we 
			 * cannot tell which category is the one to redirect to!
			 */
			if(!$continueShoppingCategoryUrl) {
				$allCategoryIds = array();
				$cartItems = Mage::helper('checkout/cart')->getQuote()->getAllVisibleItems();
				foreach($cartItems as $cartItem) {
					$productCategoryIds = Mage::getModel('catalog/product')->load($cartItem->getProductId())->getCategoryIds();
					$allCategoryIds = array_merge($allCategoryIds, $productCategoryIds);
				}
				$allCategoryIds = array_unique($allCategoryIds);
				if(count($allCategoryIds) === 1) {
					$continueShoppingCategoryUrl = Mage::getModel('catalog/category')->load(reset($allCategoryIds))->getUrl();
				}
			}
			return $lastProductAddedToCartId;
		}	
}
?>

