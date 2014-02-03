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
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_customerattributes/attribute')}` (
          `attribute_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `code` VARCHAR(255) NOT NULL,
          `type` VARCHAR(255) NOT NULL,
          `default_value` TEXT NULL,
          `is_enabled` SMALLINT(5) UNSIGNED NOT NULL,
          `store_ids` VARCHAR(255) NULL,
          `customer_groups` VARCHAR(255) NULL,
          `display_on` VARCHAR(255) NULL,
          `is_editable_by_customer` SMALLINT(5) UNSIGNED NOT NULL,
          `is_display_in_grid` SMALLINT(5) UNSIGNED NOT NULL,
          `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
          `validation_rules` TEXT NOT NULL,
          PRIMARY KEY (`attribute_id`),
          UNIQUE INDEX `code_UNIQUE` (`code` ASC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute Table';

    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_customerattributes/value_int')}` (
        `value_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `attribute_id` INT UNSIGNED NOT NULL,
        `customer_id` INT UNSIGNED NOT NULL,
        `value` INT NULL,
        PRIMARY KEY (`value_id`),
        KEY `fk_aw_customerattributes_value_int_customer_id` (`customer_id`),
        INDEX `fk_aw_customerattributes_value_int_aw_customerattributes_at_idx` (`attribute_id` ASC),
        CONSTRAINT `fk_aw_customerattributes_value_int_customer_entity`
            FOREIGN KEY (`customer_id`)
            REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_aw_customerattributes_value_int_aw_customerattributes_attr1`
            FOREIGN KEY (`attribute_id`)
            REFERENCES `{$installer->getTable('aw_customerattributes/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute Value(INT) table';

    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_customerattributes/value_varchar')}` (
        `value_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `attribute_id` INT UNSIGNED NOT NULL,
        `customer_id` INT UNSIGNED NOT NULL,
        `value` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`value_id`),
        KEY `fk_aw_customerattributes_value_varchar_customer_id` (`customer_id`),
        INDEX `fk_aw_customerattributes_value_varchar_aw_customerattribute_idx` (`attribute_id` ASC),
        CONSTRAINT `fk_aw_customerattributes_value_varchar_customer_entity`
            FOREIGN KEY (`customer_id`)
            REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_aw_customerattributes_value_varchar_aw_customerattributes_1`
            FOREIGN KEY (`attribute_id`)
            REFERENCES `{$installer->getTable('aw_customerattributes/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute Value(VARCHAR) table';

    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_customerattributes/value_text')}` (
        `value_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `attribute_id` INT UNSIGNED NOT NULL,
        `customer_id` INT UNSIGNED NOT NULL,
        `value` TEXT NOT NULL,
        PRIMARY KEY (`value_id`),
        KEY `fk_aw_customerattributes_value_text_customer_id` (`customer_id`),
        INDEX `fk_aw_customerattributes_value_text_aw_customerattributes_a_idx` (`attribute_id` ASC),
        CONSTRAINT `fk_aw_customerattributes_value_text_customer_entity`
            FOREIGN KEY (`customer_id`)
            REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_aw_customerattributes_value_text_aw_customerattributes_att1`
            FOREIGN KEY (`attribute_id`)
            REFERENCES `{$installer->getTable('aw_customerattributes/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute Value(TEXT) table';

    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_customerattributes/value_date')}` (
        `value_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `attribute_id` INT UNSIGNED NOT NULL,
        `customer_id` INT UNSIGNED NOT NULL,
        `value` DATE NULL,
        PRIMARY KEY (`value_id`),
        KEY `fk_aw_customerattributes_value_date_customer_id` (`customer_id`),
        INDEX `fk_aw_customerattributes_value_date_aw_customerattribut_idx` (`attribute_id` ASC),
        CONSTRAINT `fk_aw_customerattributes_value_date_customer_entity`
            FOREIGN KEY (`customer_id`)
            REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_aw_customerattributes_value_date_aw_customerattributes1`
            FOREIGN KEY (`attribute_id`)
            REFERENCES `{$installer->getTable('aw_customerattributes/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute Value(DATE) table';

    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_customerattributes/option')}` (
        `option_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `attribute_id` INT UNSIGNED NOT NULL,
        `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (`option_id`),
        INDEX `fk_aw_customerattributes_option_aw_customerattributes_attri_idx` (`attribute_id` ASC),
        CONSTRAINT `fk_aw_customerattributes_option_aw_customerattributes_attribu1`
            FOREIGN KEY (`attribute_id`)
            REFERENCES `{$installer->getTable('aw_customerattributes/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute Option table';

    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_customerattributes/option_value')}` (
        `value_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `option_id` INT UNSIGNED NOT NULL,
        `store_id` SMALLINT(5) UNSIGNED NOT NULL,
        `value` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`value_id`),
        KEY `fk_aw_customerattributes_option_value_store_id` (`store_id`),
        INDEX `fk_aw_customerattributes_option_value_aw_customerattributes_idx` (`option_id` ASC),
        CONSTRAINT `fk_aw_customerattributes_option_value_store_entity`
            FOREIGN KEY (`store_id`)
            REFERENCES `{$installer->getTable('core_store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_aw_customerattributes_option_value_aw_customerattributes_o1`
            FOREIGN KEY (`option_id`)
            REFERENCES `{$installer->getTable('aw_customerattributes/option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute Option Values table';

    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_customerattributes/label')}` (
        `label_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `attribute_id` INT UNSIGNED NOT NULL,
        `store_id` SMALLINT(5) UNSIGNED NOT NULL,
        `value` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`label_id`),
        KEY `fk_aw_customerattributes_label_store_id` (`store_id`),
        INDEX `fk_aw_customerattributes_label_aw_customerattributes_attrib_idx` (`attribute_id` ASC),
        CONSTRAINT `fk_aw_customerattributes_label_store_entity`
            FOREIGN KEY (`store_id`)
            REFERENCES `{$installer->getTable('core_store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_aw_customerattributes_label_aw_customerattributes_attribute1`
            FOREIGN KEY (`attribute_id`)
            REFERENCES `{$installer->getTable('aw_customerattributes/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute Label table';
");
$installer->endSetup();