<?php
/**
 * Create by: yufei
 * Date: 2019/7/15
 * Time: 9:42
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityBrowse extends Model
{
    /**
     * Notes:新增活动浏览记录
     * @param $param
     * @return array
     * author: Fei
     * Time: 2019/7/15 9:43
     */
    public function addActivityBrowse()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $activitybrowse = new ActivityBrowse();

            if(empty($_REQUEST['user_id']))
                $userid = 0;
            else
                $userid = $_REQUEST['user_id'];
            $id = Snowflake::getsnowId();
            $activitybrowse->save(['id'=>$id,
                'activity_id'  => $_REQUEST['activityId'],
                'user_id' => $userid,
                'creator' => $user['data']['id']
            ]);

            $obj = $activitybrowse::get($id);
            if($obj)
            {
                return array('code' => 1000,
                    'data' => $obj->data,
                    'message'=> '新增活动浏览记录成功！');
            }
            else
            {
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> $activitybrowse->error);
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
     * Notes:获取活动浏览记录
     * @return array
     * author: Fei
     * Time: 2019/7/15 11:01
     */
    public function getActivityBrowse()
    {
        try
        {
            $activitybrowse = new ActivityBrowse();
            $objs = $activitybrowse->where('activity_id',$_REQUEST['activityId'])->select();
            if($objs)
            {
                $users = [];
                foreach ($objs as $item)
                {
                    $userid = $item->data['user_id'];
                    $user = User::get($userid);
                    array_push($users,$user->data);
                }
                return array('code' => 1000,
                    'data' => $users,
                    'message'=> '获取浏览记录成功！');
            }
            else
            {
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> $activitybrowse->error);
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
