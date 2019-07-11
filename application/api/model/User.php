<?php
/**
 * Create by: yufei
 * Date: 2019/6/21
 * Time: 17:36
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Cache;
use think\Model;

class User extends Model
{
    public function addUser($param,$mobileNumber)
    {
        try
        {
            $user = new User();
            $id = Snowflake::getsnowId();
            $user->save(['id' => $id,
                        'mobile' => $mobileNumber,
                        'nick_name' => $param['nickName'],
                        'password' => $param['123456'],
                        'mina_openid' => $param['openid'],
                        'union_id' => "",
                        'user_type' => 0,
                        'sex' => $param['sex'],
                        'real_name' => $param[''],
                        'last_login_time' => date('Y-m-d H:i:s', time()),
                        'last_login_ip' => $param[''],
                        'head_url' => $param['headimgurl'],
                        'creator' => $id,
                        'operator' => $id
            ]);
            $objuser = $user::get($id);
            if($objuser)
            {
                $usertoken = Cache::store('redis')->get($param['openid']);
                $logintoken = aesencrypt($id);
                Cache::store('redis')->set($param['openid'],array('logintoken'=>$logintoken,'sessionkey'=>$usertoken['sessionkey']),7200);
                return array('code' => 1000,
                    'data' => array('logintoken'=>$logintoken,'userdata'=>$user->data),
                    'message'=> '用户注册成功！');
            }
            else
            {
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> '用户注册失败，请稍后再试！');
            }

        }
        catch(\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }

    /**
     * Notes:判断用户是否存在
     * @param $mina_openid
     * @return bool
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/9 16:54
     */
    public function isUserexists($mina_openid)
    {
        $user = model('User');
        $res = $user::get($mina_openid);
        if($res)
            return true;
        else
            return false;
    }

    /**
     * Notes:获取userid
     * @param $openid
     * @return int
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/9 16:54
     */
    public function getUserId($openid)
    {
        $user = model('User');
        $res = $user::get($openid);
        if($res)
            return $res['id'];
        else
            return 0;
    }

    /**
     * Notes:获取用户信息
     * @param $userid
     * @return Model|null
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/9 20:45
     */
    public function getUserInfo_userid($userid)
    {
        $user = model('User');
        $res = $user::get($userid);
        if($res)
            return $res;
        else
            return null;
    }

    /**
     * Notes:获取用户信息
     * @param $userid
     * @return Model|null
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/9 20:45
     */
    public function getUserInfo_openid($openid)
    {
        $user = model('User');
        $res = $user::get($openid);
        if($res)
            return $res;
        else
            return null;
    }

    /**
     * Notes:获取用户信息
     * @param $logintoken
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/9 21:09
     */
    public function getUserInfo_token($logintoken)
    {
        $userid = aesdecrypt($logintoken);
        $res = $this->getUserInfo_openid($userid);
        if($res)
            return array('code'=>1000,'data'=>$res->data,'message'=>'获取用户信息成功！');
        else
            return array('code'=>5000,'data'=>array(),'message'=>'获取用户信息失败！');
    }

    public function getinfo()
    {
        try {
            $res = $this->where('id','>',0)->select();
            $users = [];
//            //$res = User::all();
//            foreach($res as $item)
//                array_push($users,$item->data);
            $users = $res->toArray();
            return array('code' => 1000,
                'data' => $res,
                'message' => '用户注册失败，请稍后再试！');
        }
        catch(\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }

    }
}
