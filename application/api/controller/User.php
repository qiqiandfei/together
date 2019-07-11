<?php
/**
 * Create by: yufei
 * Date: 2019/6/21
 * Time: 12:56
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\controller;

use think\Controller;

class User extends Controller
{

    /**
     * Notes:创建用户
     * author: Fei
     * Time: 2019/7/9 21:05
     */
    public function addUser()
    {
        $ranStr = $_REQUEST['ranStr'];
        $reqToken = $_REQUEST['reqToken'];
        $mobileNumber = $_REQUEST['mobileNumber'];
        $chkres = check_request($reqToken,$ranStr);
        //验证请求是否合法
        if($chkres['code'] == 1000)
        {
            $encryptedData = $_REQUEST['encryptedData'];
            $iv = $_REQUEST['iv'];
            $decryptres = decryptData($encryptedData, $iv, $data );
            if ($decryptres == "OK")
            {
                $user = model('User');
                $res = $user->addUser($data,$mobileNumber);
                json($res['code'],$res['data'],$res['message']);

            }
            else
            {
                json(7000,array(),$decryptres);
            }
        }
        else
        {
            json($chkres['code'],$chkres['data'],$chkres['message']);
        }

    }

    /**
     * Notes:获取用户信息
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/9 21:10
     */
    public function getUserInfo()
    {
        //加密前参数
        $ranstr = $_REQUEST['ranStr'];
        //加密后参数
        $reqtoken = $_REQUEST['reqToken'];
        $logintoken = $_REQUEST['accessToken'];

        $checkres = check_request($reqtoken,$ranstr);
        //验证请求是否合法
        if($checkres['code'] == 1000)
        {
            //实例化模型
            $model = new \app\api\model\User();
            //返回用户信息
            $res = $model->getUserInfo_token($logintoken);
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }

    public function getinfo()
    {
        $model = new \app\api\model\User();
        //返回用户信息
        $res = $model->getinfo();
        json($res['code'],$res['data'],$res['message']);
    }

}