<?php
/**
 * Create by: yufei
 * Date: 2019/7/16
 * Time: 15:42
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityFavor extends Model
{
    /**
     * Notes:活动点赞
     * author: Fei
     * Time: 2019/7/16 15:41
     */
    public function addFavor()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $favor = new ActivityFavor();
            $id = Snowflake::getsnowId();
            $resval = $favor->validate(
                [
                    'activity_id'=>'require'
                ],
                [
                    'activity_id.require'=>'活动编号不能为空！'
                ]
            )->save(['id'=>$id,
                'user_id'  => $user['data']['id'],
                'activity_id' => $_REQUEST['activityId'],
                'creator' => $user['data']['id']
            ]);
            if($resval)
            {
                $obj = ActivityFavor::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '活动点赞成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $favor->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $favor->error);
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
     * Notes:获取活动点赞个数
     * @return array
     * author: Fei
     * Time: 2019/7/16 15:49
     */
    public function getFavorCount()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $favor = new ActivityFavor();
            $count = $favor->where(['activity_id'=>$_REQUEST['activity_id'],
                    'user_id'=>$user['data']['id']])->count();

            if(empty($favor->error))
            {
                return array('code' => 1000,
                    'data' => array('Favorcount'=>$count),
                    'message'=> '获取活动点赞成功！');
            }
            else
            {
                return array('code' => 2000,
                    'data' => array(),
                    'message'=> $favor->error);
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
     * Notes:取消活动点赞
     * @return array
     * author: Fei
     * Time: 2019/7/16 16:01
     */
    public function delFavor()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $favor = new ActivityFavor();
            $favor->where(['activity_id'=>$_REQUEST['activity_id'],
                'user_id'=>$user['data']['id']])->delete();

            if(empty($favor->error))
            {
                $count = $favor->where(['activity_id'=>$_REQUEST['activity_id'],
                    'user_id'=>$user['data']['id']])->count();
                return array('code' => 1000,
                    'data' => array('Favorcount'=>$count),
                    'message'=> '取消活动点赞成功！');
            }
            else
            {
                return array('code' => 2000,
                    'data' => array(),
                    'message'=> $favor->error);
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
