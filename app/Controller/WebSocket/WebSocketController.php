<?php
declare(strict_types=1);

namespace App\Controller\WebSocket;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    const WEBSOCKET_CONNECTION_LIST = 'websocket_connection_list';

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        foreach ($server->getClientList() as $fd) {
            $server->push($fd, sprintf("sendUser: %s sendData %s", $frame->fd, $frame->data));
        }
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        var_dump(sprintf("fd: %s is closed", $fd));
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        $params = $request->get;
        $uid = $this->getUid($params['token'] ?? '');
        if (!$uid) {
            //token无效 关闭连接
            $server->close($request->fd);
        }

        //注册连接中心
        if (! $this->registerConnectionList($uid, $request->fd)) {
            $server->close($request->fd);
        }
        $server->push($request->fd, 'success connect');
    }

    /**
     * 根据Token获取用户uid
     * @param $token
     * @return int
     */
    public function getUid(string $token): int
    {
        if (!$token) return 0;
        $uid = 10;
        return $uid;
    }

    private function registerConnectionList(int $uid, int $fd): bool
    {
        $container = ApplicationContext::getContainer();
        // 通过 DI 容器获取或直接注入 RedisFactory 类
        $redis = $container->get(RedisFactory::class)->get('default');
        $data = json_encode([
            'ip' => swoole_get_local_ip(),
            'fd' => $fd,
            'ts' => time(),
        ]);
        if (! $redis->hSet(self::WEBSOCKET_CONNECTION_LIST, $uid, $data)) {

        }

    }
}
