<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019\1\2 0002
 * Time: 15:29
 */
return [
    'channels' => array(
        'default' => [
            'channel_id' => null,
            'basic_qos' => [
                'prefetch_size' => null,
                'prefetch_count' => 1,
                'global' => null
            ]
        ],
        'tagName1' => array(),
        'tagName2' => [],
    ),
    'exchanges' => array(
        'default' => [
            'exchange' => '',
            'type' => '',
            'passive' => false,
            'durable' => true,
            'auto_delete' => false,
            'internal' => false,
            'nowait' => false,
            'arguments' => [],
            'ticket' => null
        ],
        'tagName1' => array(),
        'tagName2' => [],
    ),
    'queues' => array(
        'default' => [
            'queue' => '',
            'passive' => false,
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
            'nowait' => false,
            'arguments' => [],
            'ticket' => null
        ],
        'tagName1' => array(),
        'tagName2' => [],
    ),
    'publish' => array(
        'default' => [
            'exchange' => '',
            'routing_key' => '',
            'mandatory' => false,
            'immediate' => false,
            'ticket' => null
        ],
        'tagName1' => [],
        'tagName2' => []
    )
];