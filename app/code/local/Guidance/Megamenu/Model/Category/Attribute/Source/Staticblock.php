<?php

/**
 * Script to create staticblock option attribute for category
 * @author     Ferdian Robianto < robianto.ferdian@gmail.com >
 * @category   Icube
 * @package    Megamenu
 * @copyright  Copyright 2013 Ferdian Robianto (ferlands.com)
 */

class Guidance_Megamenu_Model_Category_Attribute_Source_Staticblock
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get list of all available products for category
     *
     * @return mixed
     */
    public function getAllOptions()
    {

      $category = Mage::registry('category');

      $cmsModel = Mage::getModel('cms/block')->getCollection()->load();

      if (!$category->getId()){
            return array(
                array(
                    'value' =>  '',
                    'label' =>  'Assign products to category first',
                ));
        }

      $options = array();

      foreach ($cmsModel as $block) {
          /* @var $product Mage_Catalog_Model_Product */
          $options[] = array(
              'value' => $block->getData('identifier'),
              'label' => $block->getData('title'),
          );
      }
      $blankrow = array(
                      array(
                          'value' =>  '',
                          'label' =>  'Please Select Static Block',
                      ));

      if (count($options) < 1) {
          return array(
              array(
                  'value' =>  '',
                  'label' =>  'Assign products to category first',
              ));
      } else {
          $options_merge = array_merge($blankrow, $options);
      }
      return $options_merge;
    }
}
