<?php
/**
 * Create by: yufei
 * Date: 2019/7/15
 * Time: 9:41
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\controller;


use think\Controller;

class ActivityBrowse extends Controller
{
    /**
     * Notes:新增活动浏览记录
     * author: Fei
     * Time: 2019/7/15 9:42
     */
    public function addActivityBrowse()
    {
        $param = $_REQUEST;
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
            $model = new \app\api\model\ActivityBrowse();
            //新增活动预算
            $res = $model->addActivityBrowse($param);
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }

    /**
     * Notes:获取活动浏览记录
     * @return array
     * author: Fei
     * Time: 2019/7/15 11:01
     */
    public function getActivityBrowse()
    {
        $param = $_REQUEST;
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
            $model = new \app\api\model\ActivityBrowse();
            //新增活动预算
            $res = $model->getActivityBrowse($param);
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }
}
