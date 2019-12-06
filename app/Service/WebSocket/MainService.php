<?php
declare(strict_types=1);

namespace App\Service\WebSocket;

use App\Constants\WebSocket;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;

class MainService
{
    /**
     * 根据host:port获取推送订阅者
     * @param string $uri
     * @return string
     */
    public function getPushChannelByUri(string $uri): string
    {
        return WebSocket::WEBSOCKET_PUSH_CHANNEL_PREFIX.$uri;
    }

    /**
     * 根据TOKEN获取用户UID
     * @param $token
     * @return int
     */
    public function getUidByToken(string $token): string
    {
        if (!$token) return 0;
        return $token;
    }

    /**
     * 获取uri
     * @return string
     */
    public function getLocalUri(): string
    {
        return (string)(swoole_get_local_ip()['eth0'] ?? '').':'.env('WS_SERVER_PORT', '9501');
    }

    /**
     * 获取FD映射的定域
     * @param int $fd
     * @return string
     */
    public function getFdHashField(int $fd): string
    {
        return (string)($this->getLocalUri().'-'.$fd);
    }

    /**
     * 注册连接中心
     * @param string $uid
     * @param int $fd
     * @return bool
     */
    public function registerConnectionList(string $uid, int $fd): bool
    {
        switch (WebSocket::WEBSOCKET_CONNECTION_DATA_DRIVER) {
            case "redis":
                $redis = ApplicationContext::getContainer()
                    ->get(RedisFactory::class)
                    ->get(WebSocket::WEBSOCKET_CONNECTION_DATA_DRIVER_POOL);
                $ret = $redis
                    ->multi()
                    ->hSet(WebSocket::WEBSOCKET_CONNECTION_UID_HASH, $uid, json_encode([
                        'uri'           => $this->getLocalUri(),
                        'fd'            => $fd,
                        'connectionTs'  => time(),
                    ]))
                    ->hSet(WebSocket::WEBSOCKET_CONNECTION_FD_HASH, $this->getFdHashField($fd), $uid)
                    ->exec();
                return in_array(false, $ret) ? false : true;
        }
        return false;
    }

    /**
     * 删除连接中心
     * @param int $fd
     * @return bool
     */
    public function removeConnectionList(int $fd): bool
    {
        switch (WebSocket::WEBSOCKET_CONNECTION_DATA_DRIVER) {
            case "redis":
                $redis = ApplicationContext::getContainer()
                    ->get(RedisFactory::class)
                    ->get(WebSocket::WEBSOCKET_CONNECTION_DATA_DRIVER_POOL);
                $field = $this->getFdHashField($fd);
                $uid = $redis->hGet(WebSocket::WEBSOCKET_CONNECTION_FD_HASH, $field);
                $ret = $redis
                    ->multi()
                    ->hDel(WebSocket::WEBSOCKET_CONNECTION_UID_HASH, $uid)
                    ->hDel(WebSocket::WEBSOCKET_CONNECTION_FD_HASH, $field)
                    ->exec();
                return in_array(false, $ret) ? false : true;
        }
        return false;
    }

    public function radio(int $fd, string $msg): void
    {
        $redis = ApplicationContext::getContainer()
            ->get(RedisFactory::class)
            ->get(WebSocket::WEBSOCKET_CONNECTION_DATA_DRIVER_POOL);
        $field = $this->getFdHashField($fd);
        $uid = $redis->hGet(WebSocket::WEBSOCKET_CONNECTION_FD_HASH, $field);

        $connectList = $redis->hKeys(WebSocket::WEBSOCKET_CONNECTION_FD_HASH);
        foreach ($connectList as $item) {
            $connect = explode('-', $item);
            $uri = $connect[0] ?? '';
            $fd = $connect[1] ?? 0;
            $channel = $this->getPushChannelByUri($uri);
            $redis->publish($channel, json_encode([
                'uri'   => $uri,
                'fd'    => $fd,
                'uid'   => $uid,
                'msg'   => $msg,
            ]));
        }
    }
}