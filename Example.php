<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019/9/12
 * Time: 17:47
 */

use MysicAMQP;

class Example
{
    public function publish()
    {
        $conn = [
            'host' => '',
            'port' => '',
            'user' => '',
            'pass' => '',
            'vHost' => '/'
        ];
        $config = require 'config.php';
        $publiser = new MysicAMQP\Publisher($conn,$config);
        $msg = 'hello,world';
        $properties = ['content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT];
        try{
            $publiser->publish($msg, $properties);
        } catch(\Exception $e)
        {
            //todo something
        }
    }
}
