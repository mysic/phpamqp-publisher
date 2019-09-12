<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019/9/12
 * Time: 17:53
 */

return [
    'channel' => array(
        'channel_id' => 1,
        'basic_qos' => [
            'prefetch_size' => null,
            'prefetch_count' => 1,
            'global' => null
        ]
    ),
    'exchange' => array(
        'exchange' => 'exchange name',
        'type' => 'direct',
        'passive' => false,
        'durable' => true,
        'auto_delete' => false,
        'internal' => false, 'nowait' => false,
        'arguments' => [],
        'ticket' => null
    ),
    'publish' => array(
        'exchange' => 'exchange name',
        'routing_key' => '',
        'mandatory' => false,
        'immediate' => false,
        'ticket' => null
    )
];