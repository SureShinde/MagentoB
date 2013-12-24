<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

	$installer = $this;
	$installer->startSetup();
    $setup = new Mage_Eav_Model_Entity_Setup('core_setup');


  if (!function_exists('updateSourceModel')) {
            function updateSourceModel(Mage_Eav_Model_Entity_Setup $setup, $entityTypeId, $code, $key, $value)
            {
                $id = $setup->getAttribute($entityTypeId, $code, 'attribute_id');
                $setup->updateAttribute($entityTypeId, $id, $key, $value);
            }
    }
    updateSourceModel($setup, 'catalog_product', 'aw_os_product_text', 'source_model' ,'');
    updateSourceModel($setup, 'catalog_product', 'aw_os_category_text', 'source_model' ,'');
    updateSourceModel($setup, 'catalog_product', 'aw_os_product_image_path', 'source_model' ,'');
    updateSourceModel($setup, 'catalog_product', 'aw_os_category_image_path', 'source_model' ,'');



    /* Update for front labels */

    updateSourceModel($setup, 'catalog_product', 'aw_os_product_display', 'frontend_label' ,'Display label on product page');
    updateSourceModel($setup, 'catalog_product', 'aw_os_product_image', 'frontend_label' ,'Product page label');
    updateSourceModel($setup, 'catalog_product', 'aw_os_product_image_path', 'frontend_label' ,'Product label path');
    updateSourceModel($setup, 'catalog_product', 'aw_os_product_position', 'frontend_label' ,'Product label position');
    updateSourceModel($setup, 'catalog_product', 'aw_os_product_text', 'frontend_label' ,'Product label text');


    updateSourceModel($setup, 'catalog_product', 'aw_os_category_display', 'frontend_label' ,'Display label on category page');
    updateSourceModel($setup, 'catalog_product', 'aw_os_category_image', 'frontend_label' ,'Category page label');
    updateSourceModel($setup, 'catalog_product', 'aw_os_category_image_path', 'frontend_label' ,'Category label path');
    updateSourceModel($setup, 'catalog_product', 'aw_os_category_position', 'frontend_label' ,'Category label position');
    updateSourceModel($setup, 'catalog_product', 'aw_os_category_text', 'frontend_label' ,'Category label text');

    $installer->endSetup();
