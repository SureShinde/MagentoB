<?php
/**
 * Description of Klikbca_confirmation
 *
 * @author dhanyalvian
 */

require_once '../app/Mage.php';
Mage::app();

$baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('bilna_module/paymethod/log_path'));
$confirmLogPath = Mage::getStoreConfig('payment/klikbca/confirm_log_path');
$logPath = $baseLogPath . $confirmLogPath;
$result = array ();

/**
 * read directory
 */
if ($handle = opendir($logPath)) {
        while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $result[] = $entry;
        }
    }

    closedir($handle);
}

echo json_encode($result) . "\n";
exit;

//Mage::getStoreConfig(sprintf("payment/klikbca/%s_lock_path", $status))
