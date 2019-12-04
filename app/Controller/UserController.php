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

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * @Controller()
 */
class UserController extends AbstractController
{
    /**
     * @RequestMapping(path="info", methods="get")
     */
    public function info()
    {
        $id = $this->request->input('id', '0');
        $data = new \stdClass();
        $data->name = '1';
        $data->phone = '15019335465';
        return $this->response->json([
            'code'  => '10000',
            'msg'   => '提示信息',
            'data'  => $data,
        ]);
    }
}
