<?php
/**
 * Description of Bilna_Queue_Model_System_Config_Source_Service
 *
 * @author Bilna Development Team <development@bilna.com>
 * @date 03-Nov-2015
 */

class Bilna_Queue_Model_System_Config_Source_Service {
    public function toOptionArray() {
        return array (
            array (
                'value' => 'rabbitmq',
                'label' => Mage::helper('bilna_queue')->__('RabbitMQ'),
            ),
            //array (
            //    'value' => 'Beanstalkd',
            //    'label' => Mage::helper('bilna_queue')->__('Beanstalkd'),
            //),
        );
    }
}
