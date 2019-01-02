<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2018\12\17 0017
 * Time: 14:41
 */

namespace Base;

use PhpAmqpLib\Channel\AMQPChannel;


/**
 * Class Task 任务调度器
 * @package Task\BA
 */
class Dispatcher
{
    protected $model = '';
    protected $connConfig = [];
    protected $connection = null;

    public function __construct(string $model, array $connConfig)
    {
        $this->connConfig = $connConfig;
        $this->model = \ucfirst($model);
        $this->connection = MqConnector::connect($connConfig);
    }

    public function run(array $config = [])
    {
        if (!\class_exists($this->model)) {
            throw new \Exception('business model not exist');
        }
        $model = new $this->model();
        if (!$model instanceof Processor) {
            throw new \Exception('processor not instance of Processor Class');
        }
        $channelId = null;
        if(\key_exists('channel', $config)) {
            if(\key_exists('channel_id', $config['channel']) && !empty($config['channel']['channel_id'])) {
                $channelId = $config['channel']['channel_id'];
            }
        }
        $channel = $this->channelDeclare($channelId);
        if(\key_exists('channel', $config) && \key_exists('basic_qos', $config['channel'])) {
            $channel->basic_qos(
                $config['channel']['basic_qos']['prefetch_size'],
                $config['channel']['basic_qos']['prefetch_count'],
                $config['channel']['basic_qos']['global']
            );
        }
        if (!$channel || !$channel instanceof AMQPChannel) {
            throw new \Exception('channel declare fail');
        }
        if (!empty($config['exchange_declare']['exchange'])) {
            if (empty($config['exchange_declare']['type'])) {
                $config['exchange_declare']['type'] = 'direct';
            }
            $this->exchangeDeclare($channel, $config['exchange_declare']);
        }

        $this->queueDeclare($channel, $config['queue_declare']);
        if(
            ( \key_exists('exchange_declare', $config) && !empty($config['exchange_declare']['exchange']) )
            &&
            ( \key_exists('queue_declare', $config) && !empty($config['queue_declare']['queue']) )
        ) {

            $channel->queue_bind($config['queue_declare']['queue'], $config['exchange_declare']['exchange']);
        }

        $this->basicConsume($channel, $config['basic_consume'], $model->callback($config['basic_consume']['no_ack']));

        register_shutdown_function(function($channel, $connection){
            $channel->close();
            $connection->close();
        }, $channel, $this->connection);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    protected function basicConsume(AMQPChannel $channel, array $config, callable $callback): string
    {
        if (empty($config)) {
            return $channel->basic_consume();
        }
        return $channel->basic_consume(
            $config['queue'],
            $config['consumer_tag'],
            $config['no_local'],
            $config['no_ack'],
            $config['exclusive'],
            $config['nowait'],
            $callback,
            $config['ticket'],
            $config['arguments']
        );
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
}

