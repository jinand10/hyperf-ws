<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class WebSocket extends AbstractConstants
{
    /**
     * {get} /get/user 获取用户信息
     * apiheader uid string 用户ID
     * apiparams data.userList.uid string 用户ID
     * apiparams data.userList.uid string 用户ID
     * apiparams data.userList.uid string 用户ID
     * apiparams data.userList.uid string 用户ID
     * apiparams data.userList.uid string 用户ID
     * apiparams data.userList.uid string 用户ID
     * apiresponse
     *  {
            "code": "1",
     *      "msg": "错误信息"
     *      "data": {}
     *  }
     */
    const WEBSOCKET_CONNECTION_DATA_DRIVER = 'redis';

    const WEBSOCKET_CONNECTION_DATA_DRIVER_POOL = 'default';

    const WEBSOCKET_CONNECTION_UID_HASH = 'websocket_connection_uid_hash';

    const WEBSOCKET_CONNECTION_FD_HASH = 'websocket_connection_fd_hash';

    const WEBSOCKET_CONNECTION_LOCK = 'websocket_connection_lock';

    const WEBSOCKET_PUSH_CHANNEL_PREFIX = 'websocket_push_channel_';
}
