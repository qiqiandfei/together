<?php
/**
 * Create by: yufei
 * Date: 2019/7/16
 * Time: 16:47
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\controller;

use think\Controller;

class ActivityAttach extends Controller
{
    /**
     * Notes:添加附件
     * author: Fei
     * Time: 2019/7/16 17:10
     */
    public function addAttach()
    {
        //加密前参数
        $ranstr = $_REQUEST['ranStr'];
        //加密后参数
        $reqtoken = $_REQUEST['reqToken'];
        $logintoken = $_REQUEST['token'];

        $checkres = check_req_login($reqtoken,$ranstr,$logintoken);
        //验证请求是否合法
        if($checkres['code'] == 1000)
        {
            //实例化模型
            $model = new \app\api\model\ActivityAttach();
            //添加标签
            $res = $model->addAttach();
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }
}
