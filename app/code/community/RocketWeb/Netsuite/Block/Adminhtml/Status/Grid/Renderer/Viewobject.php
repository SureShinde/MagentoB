<?php
class RocketWeb_Netsuite_Block_Adminhtml_Status_Grid_Renderer_Viewobject extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        Mage::helper('rocketweb_netsuite')->loadNetsuiteNamespace();

        $serializedObject = $this->extractSerializedObject($row->getBody());
        $object = unserialize($serializedObject);
        $identifier = 'object_'.$row->getMessageId();

        $viewLink = "<a href='javascript:void(0)' onclick=\"if($('{$identifier}').visible()) $('{$identifier}').hide();else $('{$identifier}').show(); \">View</a>";
        $objectDump = "<div style=\"display:none\" id=\"{$identifier}\"><pre>".var_export($object,true).'</pre></div>';

        return $viewLink.$objectDump;
    }

    protected function extractSerializedObject($string) {
        //give a string like action_name|ID|SERIALIZED_OBJECT, this will return SERIALIZED_OBJECT
        return substr($string,strpos($string, '|', strpos($string, '|')+1)+1);
    }
}