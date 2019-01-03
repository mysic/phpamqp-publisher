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
    protected $config = [];
    protected $tagName = '';
    public function  __construct(array $config)
    {
        $this->connection = MqConnector::instance($config['host'], $config['port'], $config['user'], $config['pass'], $config['vHost']);
        $this->config = $config;
        $this->channel = $this->channelDeclare($config['channel']['channel_id']);
        if(!empty($config['channel']['basic_qos'])) {
            $this->channel->basic_qos(
                $config['channel']['basic_qos']['prefetch_size'],
                $config['channel']['basic_qos']['prefetch_count'],
                $config['channel']['basic_qos']['global']
            );
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * @param array  $msg
     * @param array  $properties
     * @throws \Exception
     */
    public function publish(string $msg, array $properties = [])
    {
        if(empty($msg)) {
            throw new \Exception('消息体不能为空',1000);
        }
        $this->exchangeDeclare($this->config['exchange']);
        $message = new AMQPMessage($msg, $properties);
        $this->basicPublish($message, $this->config['publish']);
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