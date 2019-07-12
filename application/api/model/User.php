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
use think\Request;

class User extends Model
{
    /**
     * Notes:创建用户
     * @param $param
     * @param $mobileNumber
     * @return array
     * @throws \think\exception\PDOException
     * author: Fei
     * Time: 2019/7/12 14:21
     */
    public function addUser($param,$mobileNumber)
    {
        $user = new User();
        $user->startTrans();
        try
        {
            $info = Cache::store('redis')->get($param['openId']);
            $request = Request::instance();
            $id = Snowflake::getsnowId();
            $user->save(['id' => $id,
                        'mobile' => $mobileNumber,
                        'nick_name' => $param['nickName'],
                        'password' => "",
                        'mina_openid' => $param['openId'],
                        'union_id' => $info['unionid'],
                        'user_type' => 0,
                        'sex' => $param['gender'],
                        'real_name' => "",
                        'last_login_time' => date('Y-m-d H:i:s', time()),
                        'last_login_ip' => $request->ip(),
                        'creator' => $id,

            ]);
            $objuser = $user::get($id);
            if($objuser)
            {
                //新增UserOfficialAccount表
                $errormsg = model('UserOfficialAccount')->addUser($id,$param,$info['unionid']);
                if(empty($errormsg))
                {
                    $user->commit();
                    $logintoken = aesencrypt($id);
                    //缓存用户登录token
                    Cache::store('redis')->set($logintoken,$objuser->data,7200);
                    return array('code' => 1000,
                        'data' => array('logintoken'=>$logintoken,'userdata'=>$user->data),
                        'message'=> '用户注册成功！');
                }
                else
                {
                    $user->rollback();
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $errormsg);
                }
            }
            else
            {
                $user->rollback();
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> $user->error);
            }
        }
        catch(\Exception $e)
        {
            $user->rollback();
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
        $res = User::get($mina_openid);
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
        $res = User::get($openid);
        if($res)
            return $res->data['id'];
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
        $res = User::get($userid);
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
        $res = User::get($openid);
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
        if(Cache::store('redis')->has($logintoken))
        {
            $user = Cache::store('redis')->get($logintoken);
            if($user)
                return array('code'=>1000,'data'=>$user->data,'message'=>'获取用户信息成功！');
            else
                return array('code'=>5000,'data'=>array(),'message'=>'获取用户信息失败！');
        }
        else
        {
            return array('code'=>5000,'data'=>array(),'message'=>'获取用户信息失败！');
        }

    }

    /**
     * Notes:更新最后登录信息
     * @param $token
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/12 13:05
     */
    public function updLastLoginInfo($token)
    {
        $request = Request::instance();
        $userid = aesdecrypt($token);
        $res = $this->getUserInfo_userid($userid);
        $res->where('id',$res->data['id'])->update(
            [
                'last_login_time'=> date('Y-m-d H:i:s', time()),
                'last_login_ip' => $request->ip()
            ]
        );
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
