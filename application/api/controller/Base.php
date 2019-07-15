<?php
/**
 * Create by: yufei
 * Date: 2019/6/21
 * Time: 16:27
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\controller;

use think\Cache;

class Base
{
    /**
     * Notes:获取请求token
     * author: Fei
     * Time: 2019/6/21 16:48
     */
    public function getReqtoken()
    {
        $randstr = $_REQUEST['randStr'];
        $cryptstr = aesencrypt($randstr);
        Cache::store('redis')->set($cryptstr,$randstr,3600*8);
        json(1000,array('token'=>$cryptstr),'token请求成功，token有效期为8小时！');
    }

    /**
     * Notes:获取短信验证码
     * author: Fei
     * Time: 2019/6/21 13:19
     */
    public function sendSmsCode()
    {
        $mobileNumber = $_REQUEST['mobileNumber'];
        $reqToken = $_REQUEST['reqToken'];
        $ranStr = $_REQUEST['ranStr'];
        $chkres = check_request($reqToken,$ranStr);
        //验证请求是否合法
        if($chkres['code'] == 1000)
        {
            $res = send_smscode($mobileNumber);
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($chkres['code'],$chkres['data'],$chkres['message']);
        }
    }

    /**
     * Notes:手机号登录
     * author: Fei
     * Time: 2019/6/21 17:04
     */
    public function mobileLogin()
    {

        $mobileNumber = $_REQUEST['mobileNumber'];
        $verCode = $_REQUEST['verCode'];
        $reqToken = $_REQUEST['reqToken'];
        $ranStr = $_REQUEST['ranStr'];
        $minaCode = $_REQUEST['minaCode'];
        $chkres = check_request($reqToken,$ranStr);
        //验证请求是否合法
        if($chkres['code'] == 1000)
        {
//            if(Cache::store('redis')->has($mobileNumber))
//            {
                $code = "8888";
                //$code = Cache::store('redis')->get($mobileNumber);
                if($code == $verCode)
                {
                    $res = model('Base')->mobileLogin($minaCode);
                    json($res['code'],$res['data'],$res['message']);
                }
                else
                {
                    json(5004,array(),'验证码输入有误，请核对后重新输入！');
                }
//            }
//            else
//                json(5003,array(),'验证码，已经失效，请重新获取！');
        }
        else
        {
            json($chkres['code'],$chkres['data'],$chkres['message']);
        }
    }

    public function wxxLogin()
    {
        $verCode = $_REQUEST['verCode'];
        $reqToken = $_REQUEST['reqToken'];
        $ranStr = $_REQUEST['ranStr'];
        $chkres = check_request($reqToken,$ranStr);
        //验证请求是否合法
        if($chkres['code'] == 1000)
        {
            $res = model('Base')->wxxLogin($verCode);
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($chkres['code'],$chkres['data'],$chkres['message']);
        }
    }

    /**
     * Notes:上传图片
     * author: Fei
     * Time: 2019/6/21 19:50
     */
    public function picUpload()
    {
        $ranStr = $_REQUEST['ranStr'];
        $reqToken = $_REQUEST['reqToken'];
        $logintoken = $_REQUEST['token'];
        $chkres = check_req_login($reqToken,$ranStr,$logintoken);
        //验证请求是否合法
        if($chkres['code'] == 1000)
        {
            $res = picupload();
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($chkres['code'],$chkres['data'],$chkres['message']);
        }
    }
}
