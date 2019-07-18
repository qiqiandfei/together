<?php
/**
 * Create by: yufei
 * Date: 2019/7/10
 * Time: 14:21
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivitySchedule extends Model
{
    /**
     * Notes:新增行程
     * @return array
     * @throws \think\exception\PDOException
     * author: Fei
     * Time: 2019/7/10 15:03
     */
    public function addSchedule()
    {
        $as = new ActivitySchedule();
        $as->startTrans();
        try
        {

            $user = model('user')->getUserInfo_token($_REQUEST['token']);

            if(empty($_REQUEST['leaderId']))
                $leaderId = $user['data']['id'];
            else
                $leaderId = $_REQUEST['leaderId'];

            if(empty($_REQUEST['dayIndex']))
                $dayIndex = 1;
            else
                $dayIndex = $_REQUEST['dayIndex'];


            $id = Snowflake::getsnowId();
            $resval = $as->validate(
                [
                    'begin_time'=>'开始时间不能为空！',
                    'end_time'=>'结束时间不能为空！',
                    'schedule_summary'=>'行程摘要不能为空！',
                    'activity_id'=>'活动编号不能为空！',
                    'schedule_date'=>'行程日期不能为空！',
                    'end_time'=>'after:'.$_REQUEST['beginTime']
                ],
                [
                    'begin_time.require'=>'require',
                    'end_time.require'=>'require',
                    'schedule_summary.require'=>'require',
                    'activity_id.require'=>'require',
                    'schedule_date.require'=>'require',
                    'end_time.after'=>'开始时间不能大于结束时间！'
                ]
            )->save(['id' => $id,
                    'activity_id' => $_REQUEST['activityId'],
                    'day_index' => $dayIndex,
                    'schedule_date' => $_REQUEST['scheduleDate'],
                    'begin_time' => $_REQUEST['beginTime'],
                    'end_time' => $_REQUEST['endTime'],
                    'schedule_summary' => $_REQUEST['scheduleSummary'],
                    'leader_id' => $leaderId,
                    'depart_longitude' => $_REQUEST['departLongitude'],
                    'depart_latitude' => $_REQUEST['departLatitude'],
                    'destination_longitude' => $_REQUEST['destinationLongitude'],
                    'destination_latitude' => $_REQUEST['destinationLatitude'],
                    'creator'=>$user['data']['id']
                    ]);
            if($resval)
            {
                $obj = ActivitySchedule::get($id);
                if($obj)
                {
                    //新增明细
                    $detial = model('ActivityScheduleDetail')->addScheduleDetail($_REQUEST['activityId'],$id,$_REQUEST['scheduleContent'],$user['data']['id']);
                    if($detial)
                    {
                        $as->commit();
                        return array('code' => 1000,
                            'data' => array('ActivitySchedule'=>$obj->data,'ActivityScheduleDetail'=>$detial->data),
                            'message'=> '新增行程成功！');
                    }
                    else
                    {
                        $as->rollback();
                        return array('code' => 3000,
                            'data' => array(),
                            'message'=> '新增行程失败，请稍后再试！');
                    }
                }
                else
                {
                    $as->rollback();
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $as->error);
                }
            }
            else
            {
                $as->rollback();
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $as->error);
            }

        }
        catch(\Exception $e)
        {
            $as->rollback();
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }

    /**
     * Notes:获取活动行程
     * @return array
     * author: Fei
     * Time: 2019/7/11 14:16
     */
    public function getSchedule()
    {
        try
        {
            $Schedules = $this->hasOne('ActivityScheduleDetail','schedule_id','id')
                ->where('activity_id',$_REQUEST['activity_id'])
                ->order('day_index')
                ->select();

            $res = [];
            foreach ($Schedules as $item)
                array_push($res,$item->data);

            if(count($res) > 0)
            {
                return array('code' => 1000,
                    'data' => $res,
                    'message'=> '获取活动行程成功！');
            }
            else
            {
                return array('code' => 1000,
                    'data' => array(),
                    'message'=> '活动下暂无行程！');
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
     * Notes:编辑行程信息
     * @return array
     * @throws \think\exception\PDOException
     * author: Fei
     * Time: 2019/7/11 14:54
     */
    public function editSchedule()
    {
        $as = new ActivitySchedule();
        $as->startTrans();
        try
        {

            $user = model('user')->getUserInfo_token($_REQUEST['token']);

            if(empty($_REQUEST['leaderId']))
                $leaderId = $user['data']['id'];
            else
                $leaderId = $_REQUEST['leaderId'];

            if(empty($_REQUEST['dayIndex']))
                $dayIndex = 1;
            else
                $dayIndex = $_REQUEST['dayIndex'];


            $id = Snowflake::getsnowId();
            $resval = $as->validate(
                [
                    'begin_time'=>'开始时间不能为空！',
                    'end_time'=>'结束时间不能为空！',
                    'schedule_summary'=>'行程摘要不能为空！',
                    'activity_id'=>'活动编号不能为空！',
                    'schedule_date'=>'行程日期不能为空！',
                    'end_time'=>'after:'.$_REQUEST['beginTime']
                ],
                [
                    'begin_time.require'=>'require',
                    'end_time.require'=>'require',
                    'schedule_summary.require'=>'require',
                    'activity_id.require'=>'require',
                    'schedule_date.require'=>'require',
                    'end_time.after'=>'开始时间不能大于结束时间！'
                ]
            )->where('id',$_REQUEST['id'])
                ->update(['activity_id' => $_REQUEST['activityId'],
                        'day_index' => $dayIndex,
                        'schedule_date' => $_REQUEST['scheduleDate'],
                        'begin_time' => $_REQUEST['beginTime'],
                        'end_time' => $_REQUEST['endTime'],
                        'schedule_summary' => $_REQUEST['scheduleSummary'],
                        'leader_id' => $leaderId,
                        'depart_longitude' => $_REQUEST['departLongitude'],
                        'depart_latitude' => $_REQUEST['departLatitude'],
                        'destination_longitude' => $_REQUEST['destinationLongitude'],
                        'destination_latitude' => $_REQUEST['destinationLatitude'],
                        'operator'=> $user['data']['id'],
                        'operator_time'=> date('Y-m-d H:i:s', time())
            ]);
            if($resval)
            {
                $obj = ActivitySchedule::get($_REQUEST['id']);
                if($obj)
                {
                    //编辑明细
                    $detial = model('ActivityScheduleDetail')->editScheduleDetail($_REQUEST['activityId'],$id,$_REQUEST['scheduleContent'],$user['id']);
                    if($detial)
                    {
                        $as->commit();
                        return array('code' => 1000,
                            'data' => array('ActivitySchedule'=>$obj->data,'ActivityScheduleDetail'=>$detial),
                            'message'=> '编辑行程成功！');
                    }
                    else
                    {
                        $as->rollback();
                        return array('code' => 3000,
                            'data' => array(),
                            'message'=> '编辑行程失败，请稍后再试！');
                    }
                }
                else
                {
                    $as->rollback();
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> '编辑行程失败，请稍后再试！');
                }
            }
            else
            {
                $as->rollback();
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $as->error);
            }

        }
        catch(\Exception $e)
        {
            $as->rollback();
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }


    public function delSchedule()
    {
        $as = new ActivitySchedule();
        $as->startTrans();
        try
        {

            $obj = ActivitySchedule::get($_REQUEST['id']);
            //删除明细
            $error = model('ActivityScheduleDetail')->delScheduleDetail($obj->data['activity_id'],$_REQUEST['id']);
            if(empty($error))
            {
                $as->where('id',$_REQUEST['id'])->delete();
                if(empty($as->error))
                {
                    $as->commit();
                    return array('code' => 1000,
                        'data' => array(),
                        'message'=> '删除行程成功！');
                }
                else
                {
                    $as->rollback();
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $as->error);
                }
            }
            else
            {
                $as->rollback();
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> '编辑行程失败，请稍后再试！');
            }
        }
        catch(\Exception $e)
        {
            $as->rollback();
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }
}
