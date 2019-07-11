<?php
/**
 * Create by: yufei
 * Date: 2019/6/21
 * Time: 17:12
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use think\Cache;

class Base
{
    /**
     * Notes:手机号登录
     * @param $code
     * @return array
     * author: Fei
     * Time: 2019/6/21 19:19
     */
    public function mobileLogin($code)
    {
        $openid = get_openid($code);
        $user = model('User');
        $usertoken = Cache::store('redis')->get($openid);
        if($user->isUserexists($openid))
        {
            $userinfo = model('user')->getUserInfo_openid($openid);
            $logintoken = aesencrypt($userinfo['id']);
            Cache::store('redis')->set($openid,array('logintoken'=>$logintoken,'sessionkey'=>$usertoken['sessionkey']),7200);
            $usertoken = Cache::store('redis')->get($openid);
            return array('code'=>1000,'data'=> array('logintoken'=>$usertoken['logintoken'],'userdata'=>$userinfo),'message'=>'登录成功！');
        }
        else
        {
            return array('code'=>2001,
                'data'=>array('openid'=>$openid,
                              'sessionkey'=>$usertoken['sessionkey']),
                'message'=>'用户不存在，请调用/api/User/addUser接口！');
        }

    }

    /**
     * Notes:微信登录
     * @param $code
     * author: Fei
     * Time: 2019/6/21 19:32
     */
//    public function wxxLogin($code)
//    {
//        $openid = get_openid($code);
//        $time = string(time());
//        if(!Cache::store('redis')->has($openid))
//        {
//            //生成token
//            $token = aesencrypt($openid.$time);
//
//            //缓存token
//            Cache::store('redis')->set($openid,$token,3600*8);
//            return array('token' => $token, 'openId' => $openid);
//        }
//    }

}
