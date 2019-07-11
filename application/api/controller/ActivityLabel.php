<?php
/**
 * Create by: yufei
 * Date: 2019/7/10
 * Time: 13:31
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\controller;


use think\Controller;

class ActivityLabel extends Controller
{
    /**
     * Notes:添加活动标签
     * author: Fei
     * Time: 2019/7/10 13:32
     */
    public function addLabel()
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
            $model = new \app\api\model\ActivityLabel();
            //添加标签
            $res = $model->addLabel();
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }
}
