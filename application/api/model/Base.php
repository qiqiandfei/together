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

        //如果用户存在则更新登录token；否则新增用户
        if($user->isUserexists($openid))
        {
            $userinfo = $user->getUserInfo_openid($openid);
            $logintoken = aesencrypt($userinfo['id']);

            //更新缓存中的数据，添加登录token
            Cache::store('redis')->set($logintoken,$userinfo,7200);

            //更新最后登录信息
            $user->updLastLoginInfo($logintoken);

            return array('code'=>1000,'data'=> array('logintoken'=>$logintoken,'userdata'=>$userinfo),'message'=>'登录成功！');
        }
        else
        {
            return array('code'=>2001,
                'data'=>array('openid'=>$openid),
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
