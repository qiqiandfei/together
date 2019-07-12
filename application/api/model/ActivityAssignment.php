<?php
/**
 * Create by: yufei
 * Date: 2019/7/11
 * Time: 15:59
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityAssignment extends Model
{
    /**
     * Notes:创建活动任务
     * @return array
     * author: Fei
     * Time: 2019/7/11 16:08
     */
    public function crtActivityTask()
    {
        try
        {
            if(empty($_REQUEST['receiveUserId']))
                $assignment_state = 1;
            else
                $assignment_state = 2;
            //获取用户信息
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            //创建活动任务
            $id = Snowflake::getsnowId();
            $task = new ActivityAssignment();
            $resval = $task->validate(
                [
                    'assignment_name'  => 'require',
                    'assignment_goal'   => 'require',
                    'activity_id' => 'require',

                ],
                [
                    'assignment_name.require' => '任务名称不能为空！',
                    'assignment_goal.require' => '任务目标不能为空！',
                    'activity_id.require' => '活动编号不能为空！',

                ])->save(['id' => $id,
                'activity_id' => $_REQUEST['activityId'],
                'assignment_name' => $_REQUEST['assignmentName'],
                'assignment_goal' => $_REQUEST['assignmentGoal'],
                'receive_user_id' => $_REQUEST['receiveUserId'],
                'assignment_state' => $assignment_state,
                'creator' => $user['data']['id']
            ]);

            if($resval)
            {
                $obj = $task::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '创建活动任务成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> '创建活动任务失败，请稍后再试！');
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $task->error);
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
     * Notes:编辑活动任务
     * @return array
     * author: Fei
     * Time: 2019/7/11 16:26
     */
    public function editActivityTask()
    {
        try
        {
            if(!empty($_REQUEST['assignment_state']))
                $assignment_state = $_REQUEST['assignment_state'];
            else
                $assignment_state = 1;

            //获取用户信息
            $user = model('user')->getUserInfo_token($_REQUEST['token']);

            $task = new ActivityAssignment();
            $resval = $task->validate(
                [
                    'assignment_name'  => 'require',
                    'assignment_goal'   => 'require'

                ],
                [
                    'assignment_name.require' => '任务名称不能为空！',
                    'assignment_goal.require' => '任务目标不能为空！'

                ])->where('id',$_REQUEST['id'])
                ->update([
                        'assignment_name' => $_REQUEST['assignment_name'],
                        'assignment_goal' => $_REQUEST['assignment_goal'],
                        'receive_user_id' => $_REQUEST['receive_user_id'],
                        'assignment_state' => $assignment_state,
                        'operator' => $user['data']['id'],
                        'operator_time'=> date('Y-m-d H:i:s', time())
                        ]);

            if($resval)
            {
                $obj = $task::get($_REQUEST['id']);
                if($task->error)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '编辑活动任务成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> '编辑活动任务失败，请稍后再试！');
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $task->error);
            }
        }
        catch (\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }


    public function delActivityTask()
    {
        try
        {
            $task = new ActivityAssignment();
            $task->where('id',$_REQUEST['id'])
                ->update([
                    'is_delete' => 1
                ]);

            if(empty($task->error))
            {
                $obj = $task::get($_REQUEST['id']);
                return array('code' => 1000,
                    'data' => $obj->data,
                    'message'=> '删除活动任务成功！');

            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $task->error);
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
