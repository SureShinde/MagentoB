<?php
namespace Bilna\Libraries;

use Phalcon\Validation,
    Phalcon\Validation\Validator\PresenceOf,
    Phalcon\Validation\Validator\Identical,
    Phalcon\Validation\Validator\Email,
    Phalcon\Validation\Validator\ExclusionIn,
    Phalcon\Validation\Validator\InclusionIn,
    Phalcon\Validation\Validator\Regex,
    Phalcon\Validation\Validator\StringLength,
    Phalcon\Validation\Validator\Between,
    Phalcon\Validation\Validator\Confirmation;
/**
 * Description of BackendValidator
 *
 */
class BackendValidator extends Validation{
    
    private function setFields($req)
    {
    	foreach($req as $k => $v){
    		$fields[$k]	= $k;
    	}
    	return $fields;
    }
    
    /**
     * login validation.
     */
    public function loginValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['username'], new PresenceOf(['message' => 'Username is required']));
        $this->add($fields['pwd'], 		new PresenceOf(['message' => 'Password is required']));
        $this->add($fields['pwd'], 		new StringLength(['min' => 8, 'messageMinimum' => 'Password must be at least 8 characters long ']));
    }
    
    /**
     * brands validation.
     */
    public function brandValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['name'], 		new PresenceOf(['message' => 'Brand name is required']));
        $this->add($fields['description'], 	new PresenceOf(['message' => 'Description is required']));
        $this->add($fields['displayMode'], 	new PresenceOf(['message' => 'Display Mode is required']));
        $this->add($fields['pageLayout'], 	new PresenceOf(['message' => 'Page Layout is required']));
        //$this->add($fields['staticAreaId'], new PresenceOf(['message' => 'Static Area is required']));
        $this->add($fields['key'], 			new PresenceOf(['message' => 'Key is required']));
        //$this->add($fields['path'], 		new PresenceOf(['message' => 'Path is required']));
        $this->add($fields['status'], 		new PresenceOf(['message' => 'Status is required']));
    }
    
    /**
     * vendors validation.
     */
    public function vendorValidation($req){
    	$fields	= $this->setFields($req);
    	$this->add($fields['partnershipType'], 		new PresenceOf(['message' => 'partnership type is required']));
        $this->add($fields['name'], 		new PresenceOf(['message' => 'Vendor name is required']));
        $this->add($fields['description'], 	new PresenceOf(['message' => 'Description is required']));
        $this->add($fields['displayMode'], 	new PresenceOf(['message' => 'Display Mode is required']));
        $this->add($fields['pageLayout'], 	new PresenceOf(['message' => 'Page Layout is required']));
        //$this->add($fields['staticAreaId'], new PresenceOf(['message' => 'Static Area is required']));
        $this->add($fields['key'], 			new PresenceOf(['message' => 'Key is required']));
        //$this->add($fields['path'], 		new PresenceOf(['message' => 'Path is required']));
        $this->add($fields['status'], 		new PresenceOf(['message' => 'Status is required']));
    }
    
    /**
     * customer validation.
     */
    public function customerValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['firstName'], 	new PresenceOf(['message' => 'First Name is required']));
        $this->add($fields['email'], 		new PresenceOf(['message' => 'Email is required']));
        //$this->add($fields['password'], 	new PresenceOf(['message' => 'Password is required']));
    }
    
    /**
     * staticArea validation.
     */
    public function staticAreaValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['type'], 		new PresenceOf(['message' => 'Type is required']));
        $this->add($fields['identifier'], 	new PresenceOf(['message' => 'Identifier is required']));
        $this->add($fields['name'], 		new PresenceOf(['message' => 'Name is required']));
        $this->add($fields['status'], 		new PresenceOf(['message' => 'Status is required']));
    }
    
    /**
     * staticPage validation.
     */
    public function staticPageValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['identifier'], 	new PresenceOf(['message' => 'Identifier is required']));
        $this->add($fields['name'], 		new PresenceOf(['message' => 'Name is required']));
        $this->add($fields['content'], 		new PresenceOf(['message' => 'Content is required']));
        $this->add($fields['name'], 		new PresenceOf(['message' => 'Name is required']));
        $this->add($fields['key'], 			new PresenceOf(['message' => 'Key is required']));
        //$this->add($fields['path'], 		new PresenceOf(['message' => 'Path is required']));
        $this->add($fields['status'], 		new PresenceOf(['message' => 'Status is required']));
    }
    
    /**
     * user validation.
     */
    public function userValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['firstname'], 	new PresenceOf(['message' => 'First Name is required']));
        $this->add($fields['lastname'], 	new PresenceOf(['message' => 'Last Name is required']));
        $this->add($fields['username'], 	new PresenceOf(['message' => 'Username is required']));
        $this->add($fields['email'], 		new PresenceOf(['message' => 'Email is required']));
        $this->add($fields['password1'], 	new PresenceOf(['message' => 'Password is required']));
        $this->add($fields['admin_role_id'], new PresenceOf(['message' => 'Admin Role is required']));
        $this->add($fields['status'], 		new PresenceOf(['message' => 'Status is required']));
    }
    
    /**
     * user validation on update.
     */
    public function userValidationOnUpdate($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['firstname'], 	new PresenceOf(['message' => 'First Name is required']));
        $this->add($fields['lastname'], 	new PresenceOf(['message' => 'Last Name is required']));
        $this->add($fields['username'], 	new PresenceOf(['message' => 'Username is required']));
        $this->add($fields['email'], 		new PresenceOf(['message' => 'Email is required']));
        $this->add($fields['admin_role_id'], new PresenceOf(['message' => 'Admin Role is required']));
        $this->add($fields['status'], 		new PresenceOf(['message' => 'Status is required']));
    }
    
    /**
     * user role
     */
    public function userRoleValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['role_name'], 	new PresenceOf(['message' => 'Role Name is required']));
    }
    
    /**
     * user rule
     */
    public function userRuleValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['resource_id'], 	new PresenceOf(['message' => 'Resource Name is required']));
        $this->add($fields['access'], 		new PresenceOf(['message' => 'Access  is required']));
    }

    /**
     * product attributes
     */
    public function productAttributesValidation($req){
    	$fields	= $this->setFields($req);
        //$this->add($fields['code'], 	new PresenceOf(['message' => 'Code is required']));
        $this->add($fields['name'], 	new PresenceOf(['message' => 'Name  is required']));
        $this->add($fields['fieldName'], new PresenceOf(['message' => 'Field Name  is required']));
        //$this->add($fields['type'], 	new PresenceOf(['message' => 'Type  is required']));
        //$this->add($fields['status'], 	new PresenceOf(['message' => 'Status  is required']));
    }
    
    /**
     * product attributes set
     */
    public function productAttributessetValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['code'], 		new PresenceOf(['message' => 'Code is required']));
        $this->add($fields['name'], 		new PresenceOf(['message' => 'Name  is required']));
        $this->add($fields['attributes'], 	new PresenceOf(['message' => 'Attributes  is required']));
    }
    
    /**
     * product  set
     */
    public function productsetValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['attributeSetId'], 		new PresenceOf(['message' => 'Attributes Set Name is required']));
        $this->add($fields['type'], 				new PresenceOf(['message' => 'Type  is required']));
    }
    
    /**
     * product  
     */
    public function productValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['attributeSetId'], 		new PresenceOf(['message' => 'Attributes Set Name is required']));
        $this->add($fields['type'], 				new PresenceOf(['message' => 'Type  is required']));
    }
    
    /**
     * paymentgateway  
     */
    public function paymentGatewayValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['name'], 			new PresenceOf(['message' => 'Name is required']));
        $this->add($fields['merchantId'], 		new PresenceOf(['message' => 'Merchant ID  is required']));
        #$this->add($fields['validationTime'], 	new PresenceOf(['message' => 'Validation Time is required']));
        $this->add($fields['autoCancelOrder'], 	new PresenceOf(['message' => 'Auto Cancel Order is required']));
        #$this->add($fields['timeout'], 			new PresenceOf(['message' => 'Timeout is required']));
    }
    
    /**
     * paymentmethod  
     */
    public function paymentMethodValidation($req){
    	$fields	= $this->setFields($req);
    	$this->add($fields['paymentGatewayId'], new PresenceOf(['message' => 'Payment Gateway Name is required']));
        $this->add($fields['name'], 			new PresenceOf(['message' => 'Name is required']));
        $this->add($fields['identifier'], 		new PresenceOf(['message' => 'Identifier  is required']));
        $this->add($fields['multipleUsage'], 	new PresenceOf(['message' => 'Multiple Usage is required']));
        $this->add($fields['partialPayment'], 	new PresenceOf(['message' => 'partial Payment is required']));
        $this->add($fields['useWithVoucher'], 	new PresenceOf(['message' => 'use With Voucher is required']));
        $this->add($fields['minOrderTotal'], 	new PresenceOf(['message' => 'min Order Total  is required']));
        $this->add($fields['maxOrderTotal'], 	new PresenceOf(['message' => 'max Order Total  is required']));
        $this->add($fields['sortOrder'], 		new PresenceOf(['message' => 'sort Order  is required']));
        $this->add($fields['status'], 			new PresenceOf(['message' => 'Status  is required']));        
    }
    
    /**
     * item attributes
     */
    public function itemAttributesValidation($req){
    	$fields	= $this->setFields($req);
        //$this->add($fields['code'], 	new PresenceOf(['message' => 'Code is required']));
        $this->add($fields['name'], 	new PresenceOf(['message' => 'Name  is required']));
        $this->add($fields['fieldName'], new PresenceOf(['message' => 'Field Name  is required']));
        //$this->add($fields['type'], 	new PresenceOf(['message' => 'Type  is required']));
        //$this->add($fields['status'], 	new PresenceOf(['message' => 'Status  is required']));
    }

	/**
     * item  
     */
    public function itemValidation($req){
    	$fields	= $this->setFields($req);
        $this->add($fields['productId'], 		new PresenceOf(['message' => 'Product is required']));
    }

    
    
}