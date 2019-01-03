<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019\1\2 0002
 * Time: 15:06
 */

namespace MysicAMQP;

use MysicAMQP\Base\MqConnector;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Publisher
{
    protected $connection = null;
    protected $channel = null;
    protected $tagName = '';
    protected $configs = [];
    public function  __construct(string $host,int $port,string $user,string $pass,string $vHost = '/', $tagName = 'default')
    {
        $this->connection = MqConnector::instance($host, $port, $user, $pass, $vHost);
        $this->configs = require_once 'config.php';
        $this->tagName = $tagName;
        $this->channel = $this->channelDeclare($this->configs['channels'][$tagName]);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * @param array  $msg
     * @param array  $properties
            [
                'content_type' => 'shortstr',
                'content_encoding' => 'shortstr',
                'application_headers' => 'table_object',
                'delivery_mode' => 'octet',
                'priority' => 'octet',
                'correlation_id' => 'shortstr',
                'reply_to' => 'shortstr',
                'expiration' => 'shortstr',
                'message_id' => 'shortstr',
                'timestamp' => 'timestamp',
                'type' => 'shortstr',
                'user_id' => 'shortstr',
                'app_id' => 'shortstr',
                'cluster_id' => 'shortstr',
            ]
     * @throws \Exception
     */
    public function publish(array $msg, array $properties = [])
    {
        if(empty($msg)) {
            throw new \Exception('消息体不能为空',1000);
        }
        $this->exchangeDeclare($this->configs['exchanges'][$this->tagName]);
        $this->queueDeclare($this->configs['queues'][$this->tagName]);
        $message = new AMQPMessage($msg, $properties);
        $this->basicPublish($message, $this->configs['publish'][$this->tagName]);
    }

    protected function channelDeclare($channelId): AMQPChannel
    {
        return $this->connection->channel($channelId);
    }

    protected function exchangeDeclare(array $config)
    {
        return $this->channel->exchange_declare(
            $config['exchange'],
            $config['type'],
            $config['passive'],
            $config['durable'],
            $config['auto_delete'],
            $config['internal'],
            $config['nowait'],
            $config['arguments'],
            $config['ticket']
        );
    }

    protected function queueDeclare(array $config)
    {
        if (empty($config)) {
            return $this->channel->queue_declare();
        }
        return $this->channel->queue_declare(
            $config['queue'],
            $config['passive'],
            $config['durable'],
            $config['exclusive'],
            $config['auto_delete'],
            $config['nowait'],
            $config['arguments'],
            $config['ticket']
        );
    }

    protected function basicPublish(AMQPMessage $msg, array $config = [])
    {
        if (empty($config)) {
            return $this->channel->basic_publish($msg);
        }
        return $this->channel->basic_publish(
            $msg,
            $config['exchange'],
            $config['routing_key'],
            $config['mandatory'],
            $config['immediate'],
            $config['ticket']
        );
    }
}