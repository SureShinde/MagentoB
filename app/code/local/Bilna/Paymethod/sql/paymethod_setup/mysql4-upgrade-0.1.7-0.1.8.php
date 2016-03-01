<?php

$installer = $this;
$installer->startSetup();

// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

// Insert status
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

// Insert state and mapping of status to state
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
