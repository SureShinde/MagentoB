<?php
/**
 * Description of FUE Cron Daily
 * Run everyday
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once '../app/Mage.php';
Mage::app();

//First we load the model
$model = Mage::getModel('followupemail/crondaily');
 
//Then execute the task
$model->cronJobs();
