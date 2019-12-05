<?php
declare(strict_types=1);

namespace App\Controller\WebSocket;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Di\Annotation\Inject;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
use App\Service\WebSocket\MainService;

class MainController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     *
     * @Inject
     * @var MainService
     */
    private $mainService;

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        //广播
        $this->mainService->radio($frame->fd, $frame->data);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        $this->mainService->removeConnectionList($fd);
        var_dump(sprintf("fd: %s is closed", $fd));
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        $params = $request->get;
        $uid = $this->mainService->getUidByToken($params['token'] ?? '');
        if (!$uid) {
            //token无效 关闭连接
            $server->close($request->fd);
        }

        //注册连接中心
        if (! $this->mainService->registerConnectionList($uid, $request->fd)) {
            $server->close($request->fd);
        }
        $server->push($request->fd, 'success connect');
    }
}
