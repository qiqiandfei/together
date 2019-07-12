<?php
/**
 * Create by: yufei
 * Date: 2019/6/18
 * Time: 11:30
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;
use think\Model;
use Snowflake;

class Activity extends Model
{
    /**
     * Notes:创建活动
     * @param $param
     * @return array
     * author: Fei
     * Time: 2019/6/18 15:35
     */
    public function crtActivity($param)
    {
        try
        {
            //计算活动天数
            $daycount = $this->diffBetweenTwoDays($param['beginDate'],$param['endDate']);
            //获取用户信息
            $user = model('user')->getUserInfo_token($param['token']);
            //创建活动
            $id = Snowflake::getsnowId();
            $activity = new Activity();
            $resval = $activity->validate(
                [
                    'activity_name'  => 'require',
                    'conver_img'   => 'require',
                    'target_address' => 'require',
                    'begin_date' => 'require',
                    'end_date' => 'require|after:'.$param['beginDate']
                ],
                [
                    'activity_name.require' => '活动名称不能为空！',
                    'conver_img.require' => '封面图片不能为空！',
                    'target_address.require' => '目的地不能为空！',
                    'begin_date.require' => '开始日期不能为空！',
                    'end_date.require' => '结束日期不能为空！',
                    'end_date.after' => '开始日期不能大于结束日期！',
                ])->save(['id' => $id,
                    'template_id' => $param['templateId'],
                    'activity_name' => $param['activityName'],
                    'activity_type' => $param['activityType'],
                    'activity_source' => $param['activity_source'],
                    'begin_date' => $param['beginDate'],
                    'end_date' => $param['endDate'],
                    'day_count' => $daycount,
                    'departure_time' => $param['departureTime'],
                    'departure_address'=>$param['departureAddress'],
                    'average_budget' => $param['averageBudget'],
                    'need_pay' => $param['needPay'],
                    'registration_fee' => $param['registrationFee'],
                    'conver_img' => $param['converImg'],
                    'target_address' => $param['targetAddress'],
                    'brief_introduction' => $param['briefIntroduction'],
                    'remark' => $param['remark'],
                    'allow_edit' => $param['allowEdit'],
                    'activity_state' => 0,
                    'is_delete' => 0,
                    'creator' => $user['data']['id']
                    ]);

            if($resval)
            {
                $objactivity = $activity::get($id);
                if($objactivity)
                {
                    return array('code' => 1000,
                        'data' => $objactivity->data,
                        'message'=> '创建活动成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $activity->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $activity->error);
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
     * Notes:获取活动
     * @param $type
     * @param $userid
     * @return array
     * author: Fei
     * Time: 2019/7/9 18:36
     */
    public function getActivitys($type,$userid)
    {
        try
        {
            //创建的
            if($type == 1)
            {
                if((string)$userid == '')
                {
                    return array('code' => 4001,
                        'data' => array(),
                        'message'=> 'userid不能为空！');
                }
                else
                {
                    $rescrt = [];
                    $crtactivitys = Activity::where(['creator'=>$userid,'is_delete'=>0])->select();
                    foreach ($rescrt as $item)
                        array_push($rescrt,$item->data);
                    if($crtactivitys)
                        return array('code'=>1000,'data'=>$rescrt,'message'=>'获取活动成功！');
                    else
                        return array('code'=>1000,'data'=>array(),'message'=>'没有相关活动！');
                }
            }
            //参与的
            elseif($type == 2)
            {
                if((string)$userid == '')
                {
                    return array('code' => 4001,
                        'data' => array(),
                        'message'=> 'userid不能为空！');
                }
                else
                {
                    $join = [];
                    $joinactivitys = ActivityAttender::where(['family_member_id'=>$userid,'is_delete'=>0])
                        ->where('attend_state','=',0)
                        ->select();
                    foreach($joinactivitys as $item)
                        array_push($join,$item->data);

                    if($joinactivitys)
                        return array('code'=>1000,'data'=>$join,'message'=>'获取活动成功！');
                    else
                        return array('code'=>1000,'data'=>array(),'message'=>'没有相关活动！');
                }

            }
            //有关的
            elseif($type == 3)
            {
                if((string)$userid == '')
                    return array('code' => 4001,
                        'data' => array(),
                        'message'=> 'userid不能为空！');
                else
                {
                    $about = array();
                    //创建的
                    $crtactivitys = Activity::where(['creator'=>$userid,'is_delete'=>0])->select();
                    foreach($crtactivitys as $item)
                    {
                        array_push($about,$item);
                    }
                    //参与的
                    $joinactivitys = ActivityAttender::where(['family_member_id'=>$userid,'is_delete'=>0])
                        ->where('attend_state','=',0)
                        ->select();
                    foreach($joinactivitys as $item)
                    {
                        array_push($about,$item);
                    }
                    if($about)
                        return array('code'=>1000,'data'=>$about,'message'=>'获取活动成功！');
                    else
                        return array('code'=>1000,'data'=>array(),'message'=>'没有相关活动！');
                }
            }
            //所有的
            elseif($type == 4)
            {
                $res = [];
                $allactivitys = Activity::where('is_delete',0)->select();
                foreach ($allactivitys as $item)
                    array_push($res,$item->data);
                if($allactivitys)
                    return array('code'=>1000,'data'=>$res,'message'=>'获取活动成功！');
                else
                    return array('code'=>1000,'data'=>array(),'message'=>'没有相关活动！');
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
     * Notes:获取活动详情
     * @return array
     * author: Fei
     * Time: 2019/7/11 15:03
     */
    public function getActivity()
    {
        try
        {
            $activity = Activity::where(['id'=>$_REQUEST['id'],'is_delete'=>0])->find();
            if($activity)
            {
                return array('code' => 1000,
                'data' => $activity->data,
                'message'=> '获取活动成功！');
            }
            else
            {
                return array('code' => 1000,
                    'data' => array(),
                    'message'=> '活动不存在！');
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
     * Notes:编辑活动
     * @return array
     * author: Fei
     * Time: 2019/7/11 14:38
     */
    public function editActivity()
    {
        try
        {
            //获取用户信息
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $activity = new Activity();
            $resval = $activity->validate(
                [
                    'activity_name'  => 'require',
                    'conver_img'   => 'require',
                    'target_address' => 'require',
                    'begin_date' => 'require',
                    'end_date' => 'require|after:'.$_REQUEST['beginDate']
                ],
                [
                    'activity_name.require' => '活动名称不能为空！',
                    'conver_img.require' => '封面图片不能为空！',
                    'target_address.require' => '目的地不能为空！',
                    'begin_date.require' => '开始日期不能为空！',
                    'end_date.require' => '结束日期不能为空！',
                    'end_date.after' => '开始日期不能大于结束日期！'
                ]
            )->where('id',$_REQUEST['id'])
             ->update(['activity_name' => $_REQUEST['activity_name'],
                        'activity_type' => $_REQUEST['activity_type'],
                        'begin_date' => $_REQUEST['begin_date'],
                        'end_date' => $_REQUEST['end_date'],
                        'day_count' => $this->diffBetweenTwoDays($_REQUEST['begin_date'],$_REQUEST['end_date']),
                        'departure_time' => $_REQUEST['departure_time'],
                        'departure_address'=>$_REQUEST['departure_address'],
                        'average_budget' => $_REQUEST['average_budget'],
                        'need_pay' => $_REQUEST['need_pay'],
                        'registration_fee' => $_REQUEST['registration_fee'],
                        'conver_img' => $_REQUEST['conver_img'],
                        'target_address' => $_REQUEST['target_address'],
                        'brief_introduction' => $_REQUEST['brief_introduction'],
                        'remark' => $_REQUEST['remark'],
                        'allow_edit' => $_REQUEST['allow_edit'],
                        'operator' => $user['data']['id'],
                        'operate_time'=> date('Y-m-d H:i:s', time())
                        ]);
            if($resval)
            {
                $objactivity = $activity::get($_REQUEST['id']);
                if(empty($activity->error))
                {
                    return array('code' => 1000,
                        'data' => $objactivity->data,
                        'message'=> '编辑活动成功！');
                }
                else
                {
                    return array('code' => 4001,
                        'data' => array(),
                        'message'=> $activity->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $activity->error);
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
     * Notes:删除活动
     * @return array
     * author: Fei
     * Time: 2019/7/11 17:12
     */
    public function delActivity()
    {
        try
        {
            $activity = new Activity();
            $activity->where('id',$_REQUEST['id'])->update(['is_delete'=>1]);
            if(empty($activity->error))
            {
                $objactivity = $activity::get($_REQUEST['id']);
                return array('code' => 1000,
                    'data' => $objactivity->data,
                    'message'=> '删除活动成功！');
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $activity->error);
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
     * Notes:计算两个日期相差多少天
     * @param $day1
     * @param $day2
     * @return float|int
     * author: Fei
     * Time: 2019/7/9 16:35
     */
    private function diffBetweenTwoDays ($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }
}
