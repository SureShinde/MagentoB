<?php
/**
 * Description of orderCheck
 *
 * @path    shell/bilna/orderCheck.php
 * @author  Bilna Development Team <development@bilna.com>
 */
require_once dirname(__FILE__) . '/commonShellScripts.php';

class OrderCheck extends commonShellScripts {

    public function run() {
        if ($this->_isLocked()) {
            $this->logProgress(sprintf("Another '%s' process is running! Abort", $this->get_process_id()));
            exit;
        }

        $this->logProgress('OrderCheck is starting');
        $this->init();

        $this->checkOrdersMissingInNetsuite();

        $this->logProgress('OrderCheck is completed successfully');
        $this->_unlock();
    }

    protected function checkOrdersMissingInNetsuite() {
        $sql = "
            SELECT
                sfo.increment_id, sfo.state, sfo.status, sfo.entity_id as order_id, sfi.entity_id as invoice_id, sfo.netsuite_internal_id, sfo.created_at, sfo.updated_at
            FROM sales_flat_order sfo
            JOIN sales_flat_invoice sfi ON sfo.entity_id = sfi.order_id
            WHERE
                sfo.netsuite_internal_id = ''
                AND sfo.status NOT IN ('canceled', 'pending')
                AND sfo.updated_at BETWEEN (now() - interval 60 day) and (now() - interval 1 hour);
        ";
        $rows = $this->read->fetchAll($sql);

        print 'Found ' . count($rows) . " orders with no netsuite internal id\n";
        $incrementIds = [];
        foreach ($rows as $row) {
            print $row['increment_id'] . ',' . $row['status'] . ',' . $row['updated_at'] . ',' . $row['order_id'] . ',' . $row['invoice_id'] . "\n";
            $incrementIds[] = $row['increment_id'];
        }

        $netsuiteOrders = $this->netsuiteGetOrders($incrementIds);
        print 'Found ' . count($netsuiteOrders) . " of those orders in netsuite\n";
        $netsuiteIncrementIds = [];
        foreach ($netsuiteOrders as $order) {
            print $order->increment_id . ',' . $order->netsuite_internal_id . "\n";

            $netsuiteIncrementIds[] = $order->increment_id;
        }

        foreach ($rows as $row) {
            $incrementId = $row['increment_id'];
            print "Processing $incrementId\n";

            if (in_array($incrementId, $netsuiteIncrementIds)) {
                print "Ignoring $incrementId, since it's already in Netsuite\n";
                continue;
            }

            if ($this->triggerNetsuiteOrderPlace($row['order_id'])) {
                $this->triggerNetsuiteInvoiceSave($row['order_id'], $row['invoice_id']);
            }
        }
    }

    function netsuiteGetOrders($incrementIds) {
        $vars = array('ids' => $incrementIds);

        $ch = curl_init();
        #curl_setopt($ch, CURLOPT_URL,"https://rest.sandbox.netsuite.com/app/site/hosting/restlet.nl?script=513&deploy=1");
        curl_setopt($ch, CURLOPT_URL,"https://rest.netsuite.com/app/site/hosting/restlet.nl?script=484&deploy=1");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($vars));  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        #$headers[] = 'Authorization: NLAuth nlauth_account=3704883_SB2,nlauth_email=willy@bilna.com,nlauth_signature=Netsuite123';
        $headers[] = 'Authorization: NLAuth nlauth_account=3704883,nlauth_email=andi@bilna.com,nlauth_signature=MagentoNetsuite321';
        $headers[] = 'Content-Type: application/json;';
        $headers[] = 'User-Agent-x: SuiteScript-Call';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec ($ch);
        curl_close ($ch);

        $res = json_decode($server_output);
        return $res->orders;
    }

    protected function triggerNetsuiteInvoiceSave($orderId, $invoiceId) {
        $this->logProgress('Start trigger netsuite as invoice save #' . $orderId . "," . $invoiceId);

        $sql = sprintf("
            INSERT INTO `message`(`message_id`, `queue_id`, `handle`, `body`, `md5`, `timeout`, `created`, `priority`)
            VALUES(NULL, 1, NULL, 'invoice_save|%d', md5('invoice_save|%d'), NULL, unix_timestamp(), 0);
        ", $invoiceId, $invoiceId);

        if ($this->write->query($sql)) {
            $this->logProgress('Success trigger netsuite as invoice save #' . $orderId);

            return true;
        }

        return false;
    }

    protected function triggerNetsuiteOrderPlace($orderId) {
        $this->logProgress('Start trigger netsuite as order place #' . $orderId);

        //- check message
        if ($this->isExistNetsuiteOrderPlace($orderId)) {
            $this->logProgress('netsuite as order place #' . $orderId . ' already exist');

            return false;
        }

        $sql = sprintf("
            INSERT INTO `message`(`message_id`, `queue_id`, `handle`, `body`, `md5`, `timeout`, `created`, `priority`)
            VALUES(NULL, 1, NULL, 'order_place|%d', md5('order_place|%d'), NULL, unix_timestamp(), 0);
        ", $orderId, $orderId);

        if ($this->write->query($sql)) {
            $this->logProgress('Success trigger netsuite as order place #' . $orderId);

            return true;
        }

        return false;
    }

    protected function isExistNetsuiteOrderPlace($orderId) {
        $sql = sprintf("SELECT `message_id` FROM `message` WHERE `body` = 'order_place|%d' LIMIT 1", $orderId);
        $messageId = $this->read->fetchOne($sql);

        if (!$messageId) {
            return false;
        }

        return true;
    }
}

$shell = new OrderCheck();
$shell->set_logfile('orderCheck.log');
$shell->set_lockfile_timelimit(59 * 60);
$shell->set_process_id('ORDER_CHECK');
$shell->run();

