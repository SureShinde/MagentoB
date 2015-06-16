<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('blog/blog')} ADD `meta_title` TEXT NOT NULL AFTER `update_user`;
");
$installer->endSetup();
