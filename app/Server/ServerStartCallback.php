<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Server;

use Hyperf\Framework\Bootstrap\ServerStartCallback as ServerStart;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Redis\RedisFactory;
use App\Constants\WebSocket;

/**
 * 重写ServerStart
 * Class ServerStartCallback
 * @package App\Server
 */
class ServerStartCallback extends ServerStart
{
    public function beforeStart()
    {
        parent::beforeStart();
        $redis = ApplicationContext::getContainer()
            ->get(RedisFactory::class)
            ->get(WebSocket::WEBSOCKET_CONNECTION_DATA_DRIVER_POOL);

        //互斥锁控制并发ws连接
        $redis->setex(WebSocket::WEBSOCKET_CONNECTION_LOCK);
    }
}
