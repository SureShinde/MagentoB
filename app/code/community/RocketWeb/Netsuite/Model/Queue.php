<?php
class RocketWeb_Netsuite_Model_Queue extends Zend_Queue {
    public function send($message,$priority = 0)
    {
        return $this->getAdapter()->send($message,null,$priority);
    }


    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            if (null === ($adapterNamespace = $this->getOption('adapterNamespace'))) {
                $adapterNamespace = 'Zend_Queue_Adapter';
            }

            $adapterName = str_replace(
                ' ',
                '_',
                str_replace(
                    '_',
                    ' ',
                    $adapterNamespace . '_' . $adapter
                )
            );

            if (!class_exists($adapterName)) {
                #require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($adapterName);
            }

            /*
             * Create an instance of the adapter class.
             * Pass the configuration to the adapter class constructor.
             */
            $adapter = new $adapterName($this->getOptions(), $this);
        }

        if (!$adapter instanceof Zend_Queue_Adapter_AdapterInterface) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Adapter class '" . get_class($adapterName) . "' does not implement Zend_Queue_Adapter_AdapterInterface");
        }

        $this->_adapter = $adapter;

        $this->_adapter->setQueue($this);

        if (null !== ($name = $this->getOption(self::NAME))) {
            $this->_setName($name);
        }

        return $this;
    }
}