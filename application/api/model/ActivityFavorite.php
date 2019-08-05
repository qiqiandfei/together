<?php
/**
 * Create by: yufei
 * Date: 2019/8/5
 * Time: 9:14
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use think\Model;
use Snowflake;

class ActivityFavorite extends  Model
{
    /**
     * Notes:收藏活动
     * author: Fei
     * Time: 2019/7/16 15:41
     */
    public function addFavorite()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);

            $favorite = new ActivityFavorite();
            $id = Snowflake::getsnowId();
            $resval = $favorite->validate(
                [
                    'activity_id'=>'require'
                ],
                [
                    'activity_id.require'=>'活动编号不能为空！'
                ]
            )->save(['id'=>$id,
                'user_id'  => $user['data']['id'],
                'activity_id' => $_REQUEST['activityId'],
                'favorite_remark'=>$_REQUEST['favorite_remark'],
                'creator' => $user['data']['id']
            ]);
            if($resval)
            {
                $obj = ActivityFavorite::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '活动收藏成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $favorite->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $favorite->error);
            }

        }
        catch (\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }

    /**
     * Notes:获取收藏活动
     * author: Fei
     * Time: 2019/8/5 9:19
     */
    public function getFavorite()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $acitvitys = ActivityFavorite::where('user_id',$user['data']['id'])->select();
            if($acitvitys)
            {
                $favorit = [];
                foreach ($acitvitys as $item)
                    array_push($favorit,$item->data);
                return array('code' => 1000,
                'data' => array('favorit'=>$favorit),
                'message'=> '获取收藏活动成功！');
            }
            else
            {
                return array('code' => 4000,
                    'data' => array(),
                    'message'=> '尚未收藏任何活动！');
            }
        }
        catch (\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }
}
