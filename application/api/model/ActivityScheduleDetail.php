<?php
/**
 * Create by: yufei
 * Date: 2019/7/10
 * Time: 14:51
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityScheduleDetail extends Model
{
    /**
     * Notes:添加行程明细
     * @param $activityid 活动编号
     * @param $scheduleid 行程编号
     * @param $schedulecontent 行程内容
     * @param $creator 创建人
     * @return array|bool
     * @throws \think\exception\PDOException
     * author: Fei
     * Time: 2019/7/10 14:59
     */
    public function addScheduleDetail($activityid,$scheduleid,$schedulecontent,$creator)
    {
        $asd = new ActivityScheduleDetail();
        $asd->startTrans();
        $id = Snowflake::getsnowId();
        try
        {
            $asd->save([
                'id'=>$id,
                'activity_id'=>$activityid,
                'schedule_id'=>$scheduleid,
                'schedule_content'=>$schedulecontent,
                'creator'=>$creator
            ]);
            $obj = $asd::get($id);
            if($obj)
            {
                $asd->commit();
                return $obj;
            }
            else
            {
                $asd->rollback();
                return null;
            }
        }
        catch(\Exception $e)
        {
            $asd->rollback();
            return null;
        }
    }

    /**
     * Notes:编辑行程明细
     * @param $activityid
     * @param $scheduleid
     * @param $schedulecontent
     * @param $operator
     * @return array|object|null
     * @throws \think\exception\PDOException
     * author: Fei
     * Time: 2019/7/11 14:53
     */
    public function editScheduleDetail($activityid,$scheduleid,$schedulecontent,$operator)
    {
        $asd = new ActivityScheduleDetail();
        $asd->startTrans();
        try
        {
            $asd->where(['activity_id'=>$activityid,'schedule_id'=>$scheduleid])
                ->update([
                'schedule_content'=>$schedulecontent,
                'operator'=>$operator,
                    'operator_time'=>date('Y-m-d H:i:s', time())
            ]);
            if(empty($asd->error))
            {
                $asd->commit();
                return $asd->data;
            }
            else
            {
                $asd->rollback();
                return null;
            }
        }
        catch(\Exception $e)
        {
            $asd->rollback();
            return null;
        }
    }

    /**
     * Notes:删除行程
     * @param $activityid
     * @param $scheduleid
     * @return string
     * @throws \think\exception\PDOException
     * author: Fei
     * Time: 2019/7/11 17:28
     */
    public function delScheduleDetail($activityid,$scheduleid)
    {
        $asd = new ActivityScheduleDetail();
        $asd->startTrans();
        try
        {
            $asd->where(['activity_id'=>$activityid,'schedule_id'=>$scheduleid])
                ->delete();
            if(empty($asd->error))
            {
                $asd->commit();
                return $asd->error;
            }
            else
            {
                $asd->rollback();
                return 'error';
            }
        }
        catch(\Exception $e)
        {
            $asd->rollback();
            return 'error';
        }
    }
}
