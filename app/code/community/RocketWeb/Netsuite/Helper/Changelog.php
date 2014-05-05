<?php

class RocketWeb_Netsuite_Helper_Changelog extends Mage_Core_Helper_Data {
    public function isChangeLogEnabled() {
        return Mage::getStoreConfig('rocketweb_netsuite/developer/changelog_enabled');
    }

    public function logChange($action,$id,$comment) {
        $change = Mage::getModel('rocketweb_netsuite/changelog');
        $change->setAction($action);
        $change->setInternalId($id);
        $change->setComment($comment);
        $change->setCreatedDate(date('Y-m-d H:i:s',gmdate('U')));
        $change->save();
    }

    public function createLogCommentFromDiffArray($diffArray) {
        if(!is_array($diffArray) || !count($diffArray)) {
            return '';
        }

        $message = '';
        foreach($diffArray as $diffItem) {
            $message.="{$diffItem['key']}: {$diffItem['old']} => {$diffItem['new']},";
        }

        return $message;
    }

}