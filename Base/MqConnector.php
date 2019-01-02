<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2018\12\17 0017
 * Time: 13:48
 */

namespace Base;

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class MqConnector MQ 连接器
 * @package Task
 */
class MqConnector
{
    private static $instance = [];
    private function __clone(){}
    private function __construct(){}

    public static function instance(string $host,int $port,string $user,string $pass,string $vHost = '/'): AMQPStreamConnection
    {
        if (\key_exists($vHost, self::$instance)) {
            return self::$instance[$vHost];
        }
        self::$instance[$vHost] = new AMQPStreamConnection($host, $port, $user, $pass, $vHost);
        return self::$instance[$vHost];
    }
}