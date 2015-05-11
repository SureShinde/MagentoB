<?php
/**
 * Description of Mass_Generate_Voucher
 *
 * @author Bilna Development Team <development@bilna.com>
 */
require_once dirname(__FILE__) . '/../abstract.php';

class Mass_Generate_Voucher extends Mage_Shell_Abstract {
  public function run() {
    /* FAST GENERATED CODE START */
    $generator = Mage::getModel('salesrule/coupon_massgenerator');
    
    $data = array(
      'max_probability'   => 1,
      'max_attempts'      => 10,
      'uses_per_customer' => 1,
      'uses_per_coupon'   => 1,
      'qty'               => 70000, //number of coupons to generate
      'length'            => 8, //length of coupon string
      'format'            => Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHABETICAL,
      'rule_id'           => 865, //the id of the rule you will use as a template,
      'prefix'            => 'BPDO',
      'sufix'             => ''
    );
    
    $generator->validateData($data);
    $generator->setData($data);
    
    $generator->generatePool();
    
    /* FAST GENERATED CODE END */
  }
}

$shell = new Mass_Generate_Voucher();
$shell->run();
