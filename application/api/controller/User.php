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
            $openid = $_REQUEST['openid'];
            $decryptres = decryptData($encryptedData, $iv, $openid,$data);
            if ($decryptres == "OK")
            {
                $user = new \app\api\model\User();
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
     * Notes:修改用户信息
     * author: Fei
     * Time: 2019/7/15 11:19
     */
    public function editUser()
    {
        //加密前参数
        $ranstr = $_REQUEST['ranStr'];
        //加密后参数
        $reqtoken = $_REQUEST['reqToken'];
        $logintoken = $_REQUEST['accessToken'];

        $checkres = check_req_login($reqtoken,$ranstr,$logintoken);
        //验证请求是否合法
        if($checkres['code'] == 1000)
        {
            //实例化模型
            $model = new \app\api\model\User();
            //返回用户信息
            $res = $model->editUser($logintoken);
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }

    }

    /**
     * Notes:获取登录用户信息
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/9 21:10
     */
    public function getLoginUserInfo()
    {
        //加密前参数
        $ranstr = $_REQUEST['ranStr'];
        //加密后参数
        $reqtoken = $_REQUEST['reqToken'];
        $logintoken = $_REQUEST['accessToken'];

        $checkres = check_req_login($reqtoken,$ranstr,$logintoken);
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

    /**
     * Notes:获取任意用户信息
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/17 17:16
     */
    public function getUserInfo()
    {
        //加密前参数
        $ranstr = $_REQUEST['ranStr'];
        //加密后参数
        $reqtoken = $_REQUEST['reqToken'];
        $logintoken = $_REQUEST['accessToken'];

        $checkres = check_req_login($reqtoken,$ranstr,$logintoken);
        //验证请求是否合法
        if($checkres['code'] == 1000)
        {
            //实例化模型
            $model = new \app\api\model\User();
            //返回用户信息
            $res = $model->getUserInfo_userid($_REQUEST['userid']);
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
