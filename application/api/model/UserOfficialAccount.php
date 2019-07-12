<?php
/**
 * Create by: yufei
 * Date: 2019/7/12
 * Time: 13:11
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class UserOfficialAccount extends Model
{
    /**
     * Notes:添加用户信息
     * @param $id
     * @param $param
     * @return bool
     * @throws \think\exception\PDOException
     * author: Fei
     * Time: 2019/7/12 13:23
     */
    public function addUser($id,$param)
    {
        $user = new UserOfficialAccount();
        try
        {
            $user->startTrans();
            $user->save(['id' => $id,
                'openid' => $param['openId'],
                'union_id' => $param['unionId'],
                'nick_name' => $param['nickName'],
                'head_portrait' => $param['avatarUrl'],
                'sex' => $param['gender'],
                'language' => $param['language'],
                'country_name' => $param['country'],
                'province_name' => $param['province'],
                'city_name' => $param['city'],
                'creator' => $id,
            ]);
            $objuser = $user::get($id);
            if($objuser)
            {
                $user->commit();
                return true;
            }
            else
            {
                $user->rollback();
                return false;
            }
        }
        catch (\Exception $e)
        {
            $user->rollback();
            return false;
        }
    }
}
