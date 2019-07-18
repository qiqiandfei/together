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
            $id = Snowflake::getsnowId();
            $user->save(['id' => $id,
                        'mobile' => $mobileNumber,
                        'nick_name' => $param['nickName'],
                        'password' => "",
                        'mina_openid' => $param['openId'],
                        'union_id' => "",
                        'user_type' => 0,
                        'sex' => $param['gender'],
                        'real_name' => "",
                        'last_login_time' => date('Y-m-d H:i:s', time()),
                        'last_login_ip' => get_cip(),
                        'creator' => $id,

            ]);
            $objuser = User::get($id);
            if($objuser)
            {
                //新增UserOfficialAccount表
                $errormsg = model('UserOfficialAccount')->addUser($id,$param);
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
     * Notes:用户信息编辑
     * @return array
     * author: Fei
     * Time: 2019/7/15 11:15
     */
    public function editUser()
    {
        try
        {
            $userdata = $this->getUserInfo_token($_REQUEST['accessToken']);
            $user = new User();
            $resval = $user->validate(
                    [
                        'real_name'  => 'require',
                        'nick_name'   => 'require',
                    ],
                    [
                        'real_name.require' => '真实姓名不能为空！',
                        'nick_name.require' => '昵称不能为空！',
                    ]
            )->where('id',$userdata['id'])->update([
                  'real_name' => $_REQUEST['relName'],
                  'nick_name' => $_REQUEST['nickName'],
                  'sex' => $_REQUEST['sex']
              ]);
            if($resval)
            {

                return array('code' => 1000,
                    'data' => $user['data'],
                    'message'=> '编辑用户信息成功！');
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $user->error);
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
        $user = new User();
        $res = $user->where('mina_openid',$mina_openid)->find();
        if($res)
            return true;
        else
            return false;
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
            return $res->getData();
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
        $res = User::get(['mina_openid'=>$openid]);
        if($res)
            return $res->getData();
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
                return array('code'=>1000,'data'=>$user,'message'=>'获取用户信息成功！');
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
        User::where('id',$res['id'])->update(
            [
                'last_login_time'=> date('Y-m-d H:i:s', time()),
                'last_login_ip' => $request->ip()
            ]
        );
    }

    public function getinfo()
    {
        try {
            //$res = User::get('4846219238449585');
            //$res = User::where('id','4846219238449585')->find();
            //$user = new User();
            $res = User::where('id','4846219238449585')->find();
            if($res)
                return $res->getData();
            else
                return false;
//            return array('code' => 2000,
//                'data' => $res->getData(),
//                'message'=> 'ok');
        }
        catch(\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }

    }
}
