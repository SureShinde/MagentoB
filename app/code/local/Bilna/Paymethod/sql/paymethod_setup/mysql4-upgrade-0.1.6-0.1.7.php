<?php

$installer = $this;
$installer->startSetup();

// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

// Add va_number column to sales_flat_order_payment
/*$installer->run("
    ALTER TABLE sales_flat_order_payment ADD COLUMN va_number VARCHAR(20);
");*/

// Insert statuses
$installer->getConnection()->insertArray(
    $statusTable,
    array(
        'status',
        'label'
    ),
    array(
        array('status' => 'bca_va_pending', 'label' => 'BCA VA Pending'),
    )
);

// Insert states and mapping of statuses to states
$installer->getConnection()->insertArray(
    $statusStateTable,
    array(
        'status',
        'state',
        'is_default'
    ),
    array(
        array(
            'status' => 'bca_va_pending',
            'state' => 'new',
            'is_default' => 0
        ),
    )
);

$installer->endSetup();
