<?php
/**
 * Description of mysql4-upgrade-0.1.8-0.1.9
 * 
 * @path    app/code/local/Bilna/Paymethod/sql/paymethod_setup/mysql4-upgrade-0.1.7-0.1.8.php
 * @author  Bilna Development Team <development@bilna.com>
 */

$installer = $this;
$installer->startSetup();

$orderStatusTable = $installer->getTable('sales/order_status');
$orderStateTable = $installer->getTable('sales/order_status_state');

try {
    //- Insert status
    $installer->getConnection()->insertArray(
        $orderStatusTable,
        [
            'status',
            'label',
        ],
        [
            [
                'status' => 'pending_invoice',
                'label' => 'Pending Invoice',
            ],
        ]
    );

    //- Insert state and mapping of status to state
    $installer->getConnection()->insertArray(
        $orderStateTable,
        [
            'status',
            'state',
            'is_default',
        ],
        [
            [
                'status' => 'pending_invoice',
                'state' => Mage_Sales_Model_Order::STATE_NEW,
                'is_default' => 0,
            ],
        ]
    );
}
catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();
