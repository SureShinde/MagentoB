<?php

$installer = $this;
$installer->startSetup();

// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

try {
    // Insert status
    $installer->getConnection()->insertArray(
        $statusTable,
        array(
            'status',
            'label'
        ),
        array(
            array('status' => 'pending_va', 'label' => 'Pending VA'),
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
                'status' => 'pending_va',
                'state' => 'new',
                'is_default' => 0
            ),
        )
    );
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();