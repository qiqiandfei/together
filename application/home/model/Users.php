<?php
/**
 * Created by PhpStorm.
 * User: Fei
 * Date: 2019/3/5
 * Time: 15:16
 */

namespace app\home\model;
use think\Model;
use think\Db;

class Users extends Model
{
    /**
     判断用户是否存在
     */
    public function checkuserexist($openid)
    {
        $res = Db::name("users")->where('openid',$openid)->find();
        if($res)
            return true;
        else
            return false;
    }

    /**
     创建用户
     */
    public function adduser($resinfos,$sex)
    {
        Db::startTrans();
        try
        {
            $insert_data = [
                'openid' => $resinfos['openid'],
                'sex'=> $sex,
                'nickname' => $resinfos['nickname'],
                'country' => $resinfos['country'],
                'province' => $resinfos['province'],
                'city' => $resinfos['city'],
                'headimgurl' => $resinfos['headimgurl'],
                'crttime' => time()

            ];
            Db::name("users")->insert($insert_data);
            Db::commit();
        }
        catch (\PDOException $e)
        {
            Db::rollback();
        }

    }

    /**
     根据openid获取用户
     */
    public function getuser($openid)
    {
        $res = Db::name('users')->where("openid",$openid)->find();
        if($res)
            return $res;
        else
            return false;
    }

    /**
     获取用户性别
     */
    public function getsex($sex)
    {
        if($sex == 0)
            return '未知';
        else if($sex == 1)
            return '男';
        else if($sex == 2)
            return '女';
    }
}
