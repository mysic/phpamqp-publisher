<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019\1\2 0002
 * Time: 15:06
 */

namespace Publisher;

use Base\MqConnector;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Publisher
{
    protected $connection = null;
    protected $configs = [];
    public function  __construct(string $host,int $port,string $user,string $pass,string $vHost = '/')
    {
        $this->connection = MqConnector::instance($host, $port, $user, $pass, $vHost);
        $this->configs = require_once 'Base' . DIRECTORY_SEPARATOR . 'config.php';
    }

    /**
     * @param array  $msg
     * @param string $tagName
     * @param array $properties
     * [
     *  'content_type' => 'shortstr',
     *  'content_encoding' => 'shortstr',
     *  'application_headers' => 'table_object',
     *  'delivery_mode' => 'octet',
     *  'priority' => 'octet',
     *  'correlation_id' => 'shortstr',
     *  'reply_to' => 'shortstr',
     *  'expiration' => 'shortstr',
     *  'message_id' => 'shortstr',
     *  'timestamp' => 'timestamp',
     *  'type' => 'shortstr',
     *  'user_id' => 'shortstr',
     *  'app_id' => 'shortstr',
     *  'cluster_id' => 'shortstr',
     *  ]

     */
    public function publish(array $msg, string $tagName = 'default', array $properties = [])
    {
        if (empty($msg['op'])) {
            new \Exception('消息缺少操作类型 [op => add|update|delete|search]');
        }
        if(empty($msg['body'])) {
            new \Exception('消息体不能为空 [body]');
        }
        $channel = $this->channelDeclare($this->configs['channels'][$tagName]);
        $this->exchangeDeclare($channel, $this->configs['exchanges'][$tagName]);
        $this->queueDeclare($channel, $this->configs['queues'][$tagName]);
        $message = new AMQPMessage($msg, $properties);
        $this->basicPublish($channel, $message, $this->configs['publish'][$tagName]);
    }

    protected function channelDeclare($channelId): AMQPChannel
    {
        return $this->connection->channel($channelId);
    }

    protected function exchangeDeclare(AMQPChannel $channel, array $config)
    {
        return $channel->exchange_declare(
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

    protected function queueDeclare(AMQPChannel $channel, array $config)
    {
        if (empty($config)) {
            return $channel->queue_declare();
        }
        return $channel->queue_declare(
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

    protected function basicPublish(AMQPChannel $channel, AMQPMessage $msg, array $config = [])
    {
        if (empty($config)) {
            return $channel->basic_publish($msg);
        }
        return $channel->basic_publish(
            $msg,
            $config['exchange'],
            $config['routing_key'],
            $config['mandatory'],
            $config['immediate'],
            $config['ticket']
        );
    }
}