<?php

class Bilna_Pricevalidation_Model_Config extends Mage_Core_Model_Config_Base
{
    const CACHE_TAG = 'config_pricevalidation';

    protected $_rowTypeColumns = array();

    public function __construct($sourceData=null)
    {
        $this->setCacheId('config_pricevalidation');
        $this->setCacheTags(array(self::CACHE_TAG));
        $this->setCacheChecksum(null);

        parent::__construct($sourceData);
        $this->_construct();
    }
    
    public function getCache()
    {
        return Mage::app()->getCache();
    }

    protected function _construct()
    {
        if (Mage::app()->useCache('config_pricevalidation')) {
            if ($this->loadCache()) {
                return $this;
            }
        }

        if (is_callable(array(Mage::getConfig(), 'loadModulesConfiguration'))) {

            $config = Mage::getConfig()->loadModulesConfiguration('pricevalidation.xml');

        } else {

            $mergeConfig = Mage::getModel('core/config_base');

            $config = Mage::getConfig();
            $modules = $config->getNode('modules')->children();

            // check if local modules are disabled
            $disableLocalModules = (string)$config->getNode('global/disable_local_modules');
            $disableLocalModules = !empty($disableLocalModules) && (('true' === $disableLocalModules) || ('1' === $disableLocalModules));

            $configFile = $config->getModuleDir('etc', 'Pricevalidation').DS.'pricevalidation.xml';


            if ($mergeConfig->loadFile($configFile)) {
                $config->extend($mergeConfig, true);
            }

            foreach ($modules as $modName=>$module) {
                if ($module->is('active')) {
                    if (($disableLocalModules && ('local' === (string)$module->codePool)) || $modName=='Bilna_Pricevalidation') {
                        continue;
                    }

                    $configFile = $config->getModuleDir('etc', $modName).DS.'pricevalidation.xml';

                    if ($mergeConfig->loadFile($configFile)) {
                        $config->extend($mergeConfig, true);
                    }
                }
            }
        }

        $this->setXml($config->getNode('bilna_pricevalidation'));

        if (Mage::app()->useCache('config_pricevalidation')) {
            $this->saveCache();
        }
        return $this;
    }

    public function getDataTypes()
    {
        return $this->getNode('data_types')->children();
    }

    public function getRowTypes($dataType=null)
    {
        $nodes = $this->getNode('row_types')->children();
        if (!$dataType) {
            return $nodes;
        }
        $rowTypes = array();
        foreach ($nodes as $k=>$node) {
            $restrictMagentoVersion = $node->descend('restrictions/magento_version');
            if ($restrictMagentoVersion && version_compare(Mage::getVersion(), (string)$restrictMagentoVersion, '<')) {
                continue;
            }
            if ($dataType!=(string)$node->data_type) {
                continue;
            }
            $rowTypes[$k] = $node;
        }
        return $rowTypes;
    }

    public function getProfileTabs($profileType, $dataType)
    {
        $tabs = $this->getNode("data_types/$dataType/profile/$profileType/tabs");
        if (!$tabs) {
            Mage::throwException(Mage::helper('bilna_pricevalidation')->__("Invalid data type '%s' or profile type '%s'", $dataType, $profileType));
        }
        return $tabs->children();
    }

    /**
    * Maintain file columns cache
    *
    * @param string $rowType
    * @return array
    */
    public function getRowTypeColumns($rowType)
    {
        if (empty($this->_rowTypeColumns[$rowType])) {
            $node = $this->getNode("row_types/$rowType/columns");
            if (!$node) {
                var_dump($rowType); exit;
            }
            $columnsConfig = $node->asArray();
            if (!$columnsConfig) {
                return false;
            }
            uasort($columnsConfig, array($this, '_sortColumnsCb'));
            $this->_rowTypeColumns[$rowType] = $columnsConfig;
        }
        return $this->_rowTypeColumns[$rowType];
    }

    /**
    * Sort columns by 'col' member
    *
    * @param array $a
    * @param array $b
    */
    protected function _sortColumnsCb($a, $b)
    {
        return empty($a['col']) || empty($b['col']) || $a['col']==$b['col'] ? 0 : ($a['col']<$b['col'] ? -1 : 1);
    }
}
