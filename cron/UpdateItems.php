<?php

require_once '../lib/Netsuite/NetSuiteService.php';
error_reporting(E_ALL ^ E_NOTICE);

$service = new NetSuiteService();
$service->setSearchPreferences(false, 1000, false);

$search = new ItemSearchAdvanced();
$search->savedSearchId = "20";

$request = new SearchRequest();
$request->searchRecord = $search;

$searchResponse = $service->search($request);

if (!$searchResponse->searchResult->status->isSuccess) {
    echo "SEARCH ERROR";
} else {

	if(isset($_POST['submit'])) insert($arrayData);
			 
	echo "SEARCH SUCCESS, records found: " . $searchResponse->searchResult->totalRecords . "<br><br>";
	echo "<br>";
	echo "<form action='nsproduct.php' method='post'><input type='submit' value='Syncronize Product' name='submit' ></form>";
	
    $records = $searchResponse->searchResult->recordList;
	$arrayData = array();
	
	foreach($records as $record){
	 
		echo "<table cellpadding='0' cellspacing='0' border='1'>";
		echo "<tr>
					<td width='100px'>Item Name / Number</td>
					<td width='300px'>UPC Code</td>
					<td width='300px'>Display Name / Code</td>
					<td width='100px'>Units Type</td>
					<td width='100px'>Stock Units</td>
					<td width='100px'>Purchase Units</td>
					<td width='100px'>Sale Units</td>
					<td width='100px'>Subitem of</td>
					<td width='100px'>SKU BILNA</td>
					<td width='100px'>SKU VENDOR</td>
					<td width='100px'>Barcode Bilna</td>
					<td width='100px'>Attribute Set</td>
					<td width='100px'>Type</td>
					<td width='100px'>Partnership Type</td>
					<td width='100px'>Category</td>
					<td width='100px'>Sub Category</td>
					<td width='100px'>Vendor</td>
					<td width='100px'>Brand</td>
					<td width='100px'>Cost</td>
					<td width='100px'>Status Webstore</td>
					<td width='100px'>Weight</td>
					<td width='100px'>Panjang</td>
					<td width='100px'>Lebar</td>
					<td width='100px'>Tinggi</td>
					<td width='100px'>Volume</td>
					<td width='100px'>Is Drop Ship</td>
					<td width='100px'>Is Consignment</td>
					<td width='100px'>Media Image Path</td>
					<td width='100px'>Expected Cost</td>
					<td width='100px'>Event Cost</td>
					<td width='100px'>Weight for Magento</td>
					<td width='100px'>Item Weight</td>
					<td width='100px'>Pricing Group</td>
					<td width='100px'>Base Price</td>
					<td width='100px'>Alternate Price 1</td>
					<td width='100px'>Alternate Price 2</td>
					<td width='100px'>Alternate Price 3</td>
					<td width='100px'>Online Price</td>
			 </tr>";
			 
		$counter = 0;
		
		foreach($record as $rec){
			
			//get into array on attribute item
			$resultArr = valueField($rec,count($rec->customFieldList->customField));
			
			$resultArr['itemId'] = $rec->internalId;
			$resultArr['upcCode'] = $rec->upcCode;
			$resultArr['displayName'] = $rec->displayName;
			
			echo "<tr>";
				echo "<td>".$resultArr['itemId'] . "&nbsp;</td>";
				echo "<td>".$resultArr['upcCode'] . "&nbsp;</td>";
				echo "<td>".$resultArr['displayName']. "&nbsp;</td>";
				if(isset($rec->unitsType->name))
					$resultArr['unittype'] = $rec->unitsType->name;
				else
					$resultArr['unittype']  = "N/A";
				echo "<td>".$resultArr['unittype']. "&nbsp;</td>";
				
				if(isset($rec->stockUnit->name))
					$resultArr['stockUnit'] = $rec->stockUnit->name;
				else
					$resultArr['stockUnit'] = "N/A";
				echo "<td>".$resultArr['stockUnit'] . "&nbsp;</td>";
				
				if(isset($rec->purchaseUnit->name))
					$resultArr['purchaseUnit'] = $rec->purchaseUnit->name;
				else
					$resultArr['purchaseUnit'] = "N/A";
				echo "<td>".$resultArr['purchaseUnit'] . "&nbsp;</td>";
				
				if(isset($rec->saleUnit->name))
					$resultArr['saleUnit'] = $rec->saleUnit->name;
				else
					$resultArr['saleUnit']  = "N/A";
				echo "<td>".$resultArr['saleUnit']  . "&nbsp;</td>";
				
				echo "<td>".$rec->isInactive . "&nbsp;</td>";
				
				/*if($rec->customFieldList->customField[2]->scriptId=='custitem_skubilna')
					$skubilna = $rec->customFieldList->customField[2]->value;
				else
					$skubilna ="N/A";					
				echo "<td>".$skubilna. "&nbsp;</td>";
				
				if($rec->customFieldList->customField[14]->scriptId=='custitem_skuvendor')
					$skuvendor =  (string)$rec->customFieldList->customField[14]->value;
				else
					$skuvendor ="N/A";					
				*/
				
				echo "<td>".$resultArr['custitem_skubilna']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_skuvendor']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_barcodebilna']. "&nbsp;</td>";
				
				echo "<td>"."attribute". "&nbsp;</td>";
				echo "<td>"."type". "&nbsp;</td>";
				echo "<td>"."partnership" . "&nbsp;</td>";
				echo "<td>"."cat" . "&nbsp;</td>";
				echo "<td>"."sub-cat" . "&nbsp;</td>";
				echo "<td>"."vendor" . "&nbsp;</td>";
				echo "<td>"."brand" . "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_cost']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_statuswebstore']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_weight']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_panjang']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_lebar']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_tinggi']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_volume']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_isdropship']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_isconsignment']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_mediaimagepath']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_expectedcost']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_eventcost']. "&nbsp;</td>";
				echo "<td>".$resultArr['custitem_weight_formagento']. "&nbsp;</td>";
				echo "<td>"."&nbsp;</td>";
				echo "<td>"./*$rec->pricingGroup.*/"</td>";
				echo "<td>"."&nbsp;</td>";
				echo "<td>"."&nbsp;</td>";
				echo "<td>"."&nbsp;</td>";
				echo "<td>"."&nbsp;</td>";
				echo "<td>"."&nbsp;</td>";				
			echo "</tr>";
			
			
			$arrayData[$counter] = $resultArr;
			$counter++;
		}
		echo "</table>";
		//var_dump($records);
	 }
	 
	
}

function insert(array $datas){
	// Make a MySQL Connection
	$link = mysqli_connect("localhost", "root", "root", "bilna");

	 if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	 }
	
	
	//echo 'data:'.$datas[0]['custitem_skubilna'];
	
	foreach($datas as $data){
	// Insert a row of information into the table "nsproduct"
	
	//$collection = "('".$data["itemId"]."','".$data["upcCode"]."','".$data["displayName"]."','".$data["unittype"]."','".$data["stockUnit"]."','".$data["purchaseUnit"]."','".$data["saleUnit"]."','','".$data["custitem_skubilna"]."','".$data["custitem_skuvendor"]."','".$data["custitem_barcodebilna"]."','','','','','','','','".$data["custitem_cost"]."','".$data["custitem_statuswebstore"]."','".$data["custitem_weight"]."','".$data["custitem_panjang"]."','".$data["custitem_lebar"]."','".$data["custitem_tinggi"]."','".$data["custitem_volume"]."','".$data["custitem_isdropship"]."','".$data["custitem_isconsignment"]."','".$data["custitem_mediaimagepath"]."','".$data["custitem_expectedcost"]."','".$data["custitem_eventcost"]."','".$data["custitem_weight_formagento"]."',0,'',0,0,0,0,0)";
	
	$collection = "('".$data["itemId"]."','".$data["upcCode"]."','".$data["displayName"]."','".$data["unittype"]."','".$data["stockUnit"]."','".$data["purchaseUnit"]."','".$data["saleUnit"]."','','".$data["custitem_skubilna"]."','".$data["custitem_skuvendor"]."','".$data["custitem_barcodebilna"]."','','','','','','','','".$data["custitem_statuswebstore"]."',1241,14214,14151,7375,1515,'".$data["custitem_isdropship"]."','".$data["custitem_isconsignment"]."','".$data["custitem_mediaimagepath"]."','".$data["custitem_expectedcost"]."','".$data["custitem_eventcost"]."','".$data["custitem_weight_formagento"]."',0,'',0,0,0,0,0)";
	
	
	$query = $query . "INSERT INTO ns_product (itemid,upccode,displayname,unitstype,stockunits,purchaseunits,saleunits,subitem,skubilna,skuvendor,barcodebilna,attributeset,typeprod,partnership,category,subcat,vendor,brand,statuswebstore,weight,panjang,lebar,tinggi,volume,isdropship,isconsignment,mediaimgpath,expectedcost,eventcost,weightformagento,itemweight,pricinggroup,baseprice,altprice1,altprice2,altprice3,onlineprice)VALUES " .$collection . "ON DUPLICATE KEY UPDATE itemid=VALUES(itemid),upccode=VALUES(upccode);";
	}

	/*mysql_query($query)
	or die(mysql_error()."<hr>");*/
	
	mysqli_multi_query($link,$query)
	or die(mysqli_error($link)."<hr>");  
	
	

	echo "Data Inserted!";
}

function valueField(InventoryItem $record,$total){
	$arrFields = array('custitem_skubilna','custitem_skuvendor','custitem_barcodebilna','custitem_cost','custitem_statuswebstore','custitem_weight','custitem_panjang','custitem_lebar','custitem_tinggi','custitem_volume','custitem_isdropship','custitem_isconsignment','custitem_mediaimagepath','custitem_expectedcost','custitem_eventcost','custitem_weight_formagento');
	//$arrFields = array('custitem_skubilna','custitem_skuvendor','custitem_barcodebilna','custitem_attributeset','custitem_partnershiptype','custitem_category','custitem_subcategory','custitem_vendor','custitem_brand','custitem_cost','custitem_statuswebstore','custitem_weight','custitem_panjang','custitem_lebar','custitem_tinggi','custitem_volume','custitem_isdropship','custitem_isconsignment','custitem_mediaimagepath','custitem_expectedcost','custitem_eventcost','custitem_weight_formagento');
	
	$rec = new InventoryItem();
	$rec = $record;
	
	$result = array();
	
	$arr = 0;
	
	if($arr > $total )
		return "N/A";
	
	
	foreach($arrFields as $arrField){
		for($i=0;$i<$total;$i++){
		
			if($rec->customFieldList->customField[$i]->scriptId==$arrField){
				$result[$arrField] = (string)$rec->customFieldList->customField[$i]->value;
				//echo $i . '. ' .$rec->customFieldList->customField[$i]->scriptId . ':'.(string)$rec->customFieldList->customField[$i]->value.'<br>';
			}
		}
	}
	return $result;	
}
?>

