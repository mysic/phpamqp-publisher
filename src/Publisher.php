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
    public function  __construct(array $conn, array $config)
    {
        $this->connection = MqConnector::instance($conn['host'], $conn['port'], $conn['user'], $conn['pass'], $conn['vHost']);
        $this->config = $config;
    }

    public function __destruct()
    {
        if($this->channel instanceof AMQPChannel) {
            $this->channel->close();
        }
        $this->connection->close();
    }

    /**
     * @param string $msg
     * @param array  $properties
     * @throws \Exception
     */
    public function publish(string $msg, array $properties = [])
    {
        if(empty($msg)) {
            throw new \Exception('消息体不能为空',1000);
        }
        $this->channel = $this->channelDeclare($this->config['channel']['channel_id']);
        if(!empty($this->config['channel']['basic_qos'])) {
            $this->channel->basic_qos(
                $this->config['channel']['basic_qos']['prefetch_size'],
                $this->config['channel']['basic_qos']['prefetch_count'],
                $this->config['channel']['basic_qos']['global']
            );
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