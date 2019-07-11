<?php
/**
 * Create by: yufei
 * Date: 2019/7/10
 * Time: 14:19
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\controller;


use think\Controller;

class ActivitySchedule extends Controller
{
    /**
     * Notes:添加行程
     * author: Fei
     * Time: 2019/7/10 14:21
     */
    public function addSchedule()
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
            $model = new \app\api\model\ActivitySchedule();
            //添加行程
            $res = $model->addSchedule();
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }

    /**
     * Notes:获取活动行程
     * author: Fei
     * Time: 2019/7/11 14:17
     */
    public function getSchedule()
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
            $model = new \app\api\model\ActivitySchedule();
            //添加行程
            $res = $model->getSchedule();
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }

    /**
     * Notes:编辑活动行程
     * author: Fei
     * Time: 2019/7/11 14:42
     */
    public function editSchedule()
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
            $model = new \app\api\model\ActivitySchedule();
            //添加行程
            $res = $model->editSchedule();
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }
}
