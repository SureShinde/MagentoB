<?php



		$I = new TestGuy($scenario);
		$I->wantTo('test API page is working');

//		$I->amOnPage('/login');
//		$I->canSee('Sign In');
///*		
		//Brand
		//$id = $I->haveRecord('Api\Brand\Models\Brands', ['name'=>'dancow','logo'=>'dancow','description'=>'susu','key'=>'dancow','path'=>'dancow']);
		//$model = $I->grabRecord('Api\Brand\Models\Brands', ['name'=>'dancow','logo'=>'dancow','description'=>'susu','key'=>'dancow','path'=>'dancow']);

		$I->sendPOST('/brand/set',
			array('data'=>
				'{"clientId": "546c626d52e9f",
				  "clientToken": "1416389229", 
				  "name":"static page 1", 
				  "status":1, 
				  "path":"//", 
				  "key":"sp1'.rand(1000,9999).'", 
				  "content":"<div>"
				  }'));
		$I->seeResponseContainsJson(array('responseCodeDescription' => 'OK'));
		$I->seeResponseIsJson();
		$json = json_decode($I->grabResponse());
		
		$I->sendPOST('/brand/set',
			array('data'=>
				'{"clientId": "546c626d52e9f",
				  "clientToken": "1416389229", 
				  "name":123, 
				  "status":1, 
				  "path":"//", 
				  "key":"sp1'.rand(1000,9999).'",
				  "pageLayout":3,
				  "displayMode":9999999999999999, 
				  "content":"<div>"
				  }'));
		$I->seeResponseContainsJson(array('responseCodeDescription' => 'ORM Failed:: Max displayMode is 32767'));
		$I->seeResponseIsJson();
		$json = json_decode($I->grabResponse());
		
		
		$req = array('data'=>'{"clientId": "546c626d52e9f", "pageLayout":"3", "displayMode":"1", "clientToken": "1416389229", "name":"static page 1", "status":1, "path":"//", "key":"sp1'.rand(1000,9999).'", "content":"<div>","logo":"abc","description":"apa aja"}');
		$I->sendPOST('/brand/set',$req);
		$I->seeResponseContainsJson(array('responseCodeDescription' => 'OK'));
		$I->seeResponseIsJson();
		$json = json_decode($I->grabResponse());
		
		$I->sendPOST('/brand/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "id" : "'.$json->id.'"}'));
		$I->seeResponseContainsJson(array('responseCodeDescription' => 'OK'));
		$I->seeResponseIsJson();
		$req = (array)json_decode($req['data']);
		unset($req['clientId'],$req['clientToken']);
		foreach($req as $k=>$r)
			$I->seeResponseContainsJson(array($k=>(string)$r));
		
		//$response = $I->sendAjaxPostRequest('/brand/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "id" : "'.$id.'"}'));
		//$I->canSee('OK');
		//$I->seeResponseContainsJson(array('responseCodeDescription' => 'OK'));
		
		$I->seeRecord('Api\Brand\Models\Brands', ['name'=>'dancow','logo'=>'dancow','description'=>'susu','key'=>'dancow','path'=>'dancow']);
		
		$req = array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "brandId":"'.$json->id.'"}');
		$I->sendPOST('/brand/remove',$req);
		$I->seeResponseContainsJson(array('responseCodeDescription' => 'OK'));
		$I->seeResponseIsJson();

		$I->dontSeeRecord('Api\Brand\Models\Brands', ['name'=>'dancow','logo'=>'dancow','description'=>'susu','key'=>'dancow','path'=>'dancow']);
		
		
		
		$I->sendPOST('/category/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "categoryId" : "9"}'));
		$I->seeResponseContainsJson(array('responseCodeDescription' => 'OK'));
		
		return;
//*/
		
		
		
		$response = $I->sendAjaxPostRequest('/brand/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "id" : "27"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');

		$response = $I->sendAjaxPostRequest('/brand/product/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "brandId", "filter" : "=", "value" : "27"}],"sortBy" : [{"field" : "brandId", "value" : "asc"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');

		$response = $I->sendAjaxPostRequest('/brand/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "id", "filter" : "=", "value" : "27"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');

		//$response = $I->sendAjaxPostRequest('/brand/remove',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "brandId":"27", "products":["1"]}'));
		//$I->canSee('OK');
		//$I->dontSee('Notice');
		//$I->dontSee('Error');

		// /brand/product/remove
		
		
		$response = $I->sendAjaxPostRequest('/brand/set',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "name":"static page 1", "status":1, "path":"//", "key":"sp1'.rand(1000,9999).'", "content":"<div>","logo":"abc","description":"apa aja"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');

		//$response = $I->sendAjaxPostRequest('/brand/product/set',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "brandId":"154", "products":[{"id":"1","sort":"1","productPath":"//"}]}'));
		//$I->canSee('OK');
		//$I->dontSee('Notice');
		//$I->dontSee('Error');

		$response = $I->sendAjaxPostRequest('/category/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "position", "filter" : ">=", "value" : "1"}], "sortBy" : [{"field" : "name", "value" : "ASC"}], "limit" : "5"}'));
		$I->canSee('OK');
		
		$response = $I->sendAjaxPostRequest('/category/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "categoryId" : "9"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		//category Filter
		
		$response = $I->sendAjaxPostRequest('/category/products/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "categoryId", "filter" : "=", "value" : "3"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/category/getmegamenu',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		// /category/remove 
		
		// /category/products/remove

		$response = $I->sendAjaxPostRequest('/category/set',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "parentId" : "9", "name" : "Pants Cheap", "description" : "sample description pants cheap", "displayMode" : "1", "pageLayout" : "1", "includeInMenu" : "1", "staticAreaId": "13", "level" : "2", "position" : "1", "url_path" : "pants-cheap.html", "isActived" : "1", "key":"test'.rand(1000,9999).'.html" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		

		//$response = $I->sendAjaxPostRequest('/category/products/set',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "categoryId":"1", "products":[{"id":"1","sort":"1","productPath":"//"}]}'));
		//$I->canSee('OK');
		//$I->dontSee('Notice');
		//$I->dontSee('Error');
		
		// CUSTOMER HERE
		
		$response = $I->sendAjaxPostRequest('/customer/credential/check',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "type": "1", "email": "abdul.aziz@bilna.com", "password": "love"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/credential/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "email", "filter" : "=", "value" : "abdul.aziz@bilna.com"},{"field" : "type", "filter" : "=", "value" : "1"}],"sortBy" : [{"field" : "type", "value" : "ASC"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/get',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "customerId" : "77" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "firstName", "filter" : "=", "value" : "akang"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/email/get',array('data'=>'{
"clientId": "90794e3b050f815354e3e29e977a88ab", 
"clientToken": "123", 
"conditions" : [ 
                    {"field" : "email", "filter" : "=", "value" : "abdul.aziz@bilna.com"},
                    {"field" : "verify", "filter" : "=", "value" : "1"}
                ],
"sortBy"  :   [
                    {"field" : "email", "value" : "asc"}
                ], 
"limit"   : "5"
}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/group/get',array('data'=>'{
"clientId": "90794e3b050f815354e3e29e977a88ab", 
"clientToken": "123", 
"conditions" : [ {"field" : "code", "filter" : "=", "value" : "silver"},{"field" : "type", "filter" : "=", "value" : "Silver Reseller"}],
"sortBy" : [{"field" : "code", "value" : "asc"}]
}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/customergroup/get',array('data'=>'{
"clientId": "90794e3b050f815354e3e29e977a88ab", 
"clientToken": "123", 
"conditions" : [ {"field" : "customerId", "filter" : "=", "value" : "77"},{"field" : "groupId", "filter" : "=", "value" : "21"}],
"sortBy" : [{"field" : "customerId", "value" : "asc"}], 
"limit" : "5"
}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/address/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "customerId", "filter" : "=", "value" : "77"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/address/get',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "addressId" : "2" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/district/get-provinces',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "limit" : "50"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/customer/district/get-cities',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "province", "filter" : "=", "value" : "DKI Jakarta"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		



		
		
		
		
		
		
		
		
		$response = $I->sendAjaxPostRequest('/product/attribute/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "id" : "1"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/attribute/group-get',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "id":"1"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/attribute/groups-get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "id", "filter" : "=>", "value" : "1"}], "limit" : "5", "sortBy" : [ {"field" : "code", "value" : "asc"}]}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/attributeoption/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "productAttributeId", "filter" : "=", "value" : "9"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/attribute/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "id", "filter" : ">=", "value" : "1"}], "limit" : "5", "sortBy" : [ {"field" : "code", "value" : "asc"}]}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/attributeset/get',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "id" : "1" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/attributeset/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "id", "filter" : "=", "value" : "1"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/get',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "id":"58"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/category/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "productId", "filter" : "=", "value" : "58"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/image/gets',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "productId" : "58" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/price/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "productId" : "28"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/price/list',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "productId" : "28" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/rating/get',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "id" : "5" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/rating/get',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "id" : "5" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/rating/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "id", "filter" : "=", "value" : "5"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/gets',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "conditions" : [{"field":"id","filter":"=","value":"11"}] }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/stock/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "productId" : "28"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/product/get',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "id":"58"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		//setAttribute
		
		//setAttributeGroup
		
		//setAttributeOption
		
		//setAttributeSet
		
		//setProduct
		
		//setProductCategories
		
		//setProductImages
		
		//setProductPrice
		
		//setProductRating
		
		//setProductStock
		

		$response = $I->sendAjaxPostRequest('/staticarea/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "id" : "13"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/staticarea/content/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "id", "filter" : "=", "value" : "13"}]}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/staticarea/getIndexed',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "id" : "13" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/staticarea/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "type", "filter" : "=", "value" : "1"}], "sortBy" : [{"field" : "id", "value" : "ASC"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		//removeStaticArea
		
		//removeStaticAreaContent
		
		//setStaticArea
		
		//setStaticAreaContent
		
		$response = $I->sendAjaxPostRequest('/staticpage/get',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "id" : "14" }'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		$response = $I->sendAjaxPostRequest('/staticpage/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "id", "filter" : "=", "value" : "14"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');
		
		//removeStaticPage
		
		//setStaticPage
	
		$response = $I->sendAjaxPostRequest('/vendor/get',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "id" : "10"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');

		$response = $I->sendAjaxPostRequest('/vendor/product/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "vendorId", "filter" : "=", "value" : "6"}],"sortBy" : [{"field" : "brandId", "value" : "asc"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');

		$response = $I->sendAjaxPostRequest('/vendor/gets',array('data'=>'{"clientId": "90794e3b050f815354e3e29e977a88ab", "clientToken": "123", "conditions" : [ {"field" : "id", "filter" : "=", "value" : "10"}], "limit" : "5"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');

		//$response = $I->sendAjaxPostRequest('/vendor/remove',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "vendorId":"27", "products":["1"]}'));
		//$I->canSee('OK');
		//$I->dontSee('Notice');
		//$I->dontSee('Error');

		// /vendor/product/remove
		
		
		$response = $I->sendAjaxPostRequest('/vendor/set',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "name":"static page 1", "status":1, "path":"//", "key":"sp1'.rand(1000,9999).'", "content":"<div>","logo":"abc","description":"apa aja"}'));
		$I->canSee('OK');
		$I->dontSee('Notice');
		$I->dontSee('Error');

		//$response = $I->sendAjaxPostRequest('/brand/product/set',array('data'=>'{"clientId": "546c626d52e9f",  "clientToken": "1416389229", "brandId":"154", "products":[{"id":"1","sort":"1","productPath":"//"}]}'));
		//$I->canSee('OK');
		//$I->dontSee('Notice');
		//$I->dontSee('Error');

		
		
		
		
		
		
		
		/*

		$I->amOnPage('/index/foo');
		$I->canSee('Session: bar');
		$I->canSee('Cookies: bar');
		*/

