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
            $images = json_decode($param['converImg'],true);
            $activity = new Activity();
            $activity->startTrans();
            $resval = $activity->validate(
                [
                    'activity_name'  => 'require',
                    'target_address' => 'require',
                    'begin_date' => 'require',
                    'end_date' => 'require'
                ],
                [
                    'activity_name.require' => '活动名称不能为空！',
                    'target_address.require' => '目的地不能为空！',
                    'begin_date.require' => '开始日期不能为空！',
                    'end_date.require' => '结束日期不能为空！',

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
                    'conver_img' => $images['conver_img'],
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
                $objactivity = Activity::get($id);
                if($objactivity)
                {
                    $attachflg = true;
                    //循环写入附件表
                    $attach = new ActivityAttach();
                    $attach->startTrans();
                    $imagecount = 0;
                    foreach($images as $key=>$value)
                    {
                        $imagecount++;
                        $attachid = Snowflake::getsnowId();
                        if($key == 'conver_img')
                        {
                            $attach->isUpdate(false)->save(['id'=>$attachid,
                                'activity_id'  => $id,
                                'schedule_id' => 0,
                                'attach_type' => 1,
                                'attach_explain' => '活动封面',
                                'file_url' => $value,
                                'creator' => $user['data']['id']
                            ]);
                        }
                        else
                        {
                            $attach->isUpdate(false)->save(['id'=>$attachid,
                                'activity_id'  => $id,
                                'schedule_id' => 0,
                                'attach_type' => 1,
                                'attach_explain' => '活动介绍',
                                'file_url' => $value,
                                'creator' => $user['data']['id']
                            ]);
                        }
                        if(!empty($attach->error))
                        {

                            $attachflg = false;
                            break;
                        }
                    }
                    if($attachflg and $imagecount > 0)
                    {
                        $attach->commit();
                        $activity->commit();
                        return array('code' => 1000,
                            'data' => $objactivity->data,
                            'message'=> '创建活动成功！');
                    }
                    else
                    {
                        if(empty($attach->error))
                        {
                            $activity->rollback();
                            $attach->rollback();
                            return array('code' => 4005,
                                'data' => array(),
                                'message'=> '请选择一张图片封面！');
                        }
                        else
                        {
                            $activity->rollback();
                            $attach->rollback();
                            return array('code' => 4000,
                                'data' => array(),
                                'message'=> $attach->error);
                        }
                    }
                }
                else
                {
                    $activity->rollback();
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $activity->error);
                }
            }
            else
            {
                $activity->rollback();
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
                    $attach = [];
                    $activitypics = [];
                    $favorcount = [];
                    $favoritecount = [];
                    $crtactivitys = Activity::where(['creator'=>$userid,'is_delete'=>0])->select();
                    foreach ($crtactivitys as $item)
                    {
                        array_push($rescrt,$item->data);
                        $pics = ActivityAttach::where('activity_id',$item->data['id'])->select();
                        if(count($pics) > 0)
                        {
                            foreach($pics as $pic)
                                array_push($attach,$pic->data);
                            array_push($activitypics,$attach);
                        }
                        else
                        {
                            array_push($activitypics,$item->data['id'].'该活动暂无图片');
                        }
                        //获取点赞数量
                        array_push($favorcount,ActivityFavor::where('activity_id',$item->data['id'])->count());
                        //获取收藏数量
                        array_push($favoritecount,ActivityFavorite::where('activity_id',$item->data['id'])->count());
                    }

                    if($crtactivitys)
                        return array('code'=>1000,'data'=>array('activitys'=>$rescrt,'pics'=>$activitypics,'favorcount'=>$favorcount,'favoritecount'=>$favoritecount),'message'=>'获取活动成功！');
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
                    $attach = [];
                    $activitypics = [];
                    $favorcount = [];
                    $favoritecount = [];
                    $joinactivitys = ActivityAttender::where(['family_member_id'=>$userid,'is_delete'=>0])
                        ->where('attend_state','=',0)
                        ->select();
                    foreach($joinactivitys as $item)
                    {
                        array_push($join,$item->data);
                        $pics = ActivityAttach::where('activity_id',$item->data['id'])->select();
                        if(count($pics) > 0)
                        {
                            foreach($pics as $pic)
                                array_push($attach,$pic->data);
                            array_push($activitypics,$attach);
                        }
                        else
                        {
                            array_push($activitypics,$item->data['id'].'该活动暂无图片');
                        }
                        //获取点赞数量
                        array_push($favorcount,ActivityFavor::where('activity_id',$item->data['id'])->count());
                        //获取收藏数量
                        array_push($favoritecount,ActivityFavorite::where('activity_id',$item->data['id'])->count());
                    }

                    if($joinactivitys)
                        return array('code'=>1000,'data'=>array('activitys'=>$join,'pics'=>$activitypics,'favorcount'=>$favorcount,'favoritecount'=>$favoritecount),'message'=>'获取活动成功！');
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
                    $attach = [];
                    $activitypics = [];
                    $favorcount = [];
                    $favoritecount = [];
                    //有关的
                    $aboutactivitys = Activity::WhereOr([
                        'creator'=>$userid,
                        'family_member_id'=>$userid])
                        ->where('is_delete',0)->select();
                    foreach($aboutactivitys as $item)
                    {
                        array_push($about,$item);
                        $pics = ActivityAttach::where('activity_id',$item->data['id'])->select();
                        if(count($pics) > 0)
                        {
                            foreach($pics as $pic)
                                array_push($attach,$pic->data);
                            array_push($activitypics,$attach);
                        }
                        else
                        {
                            array_push($activitypics,$item->data['id'].'该活动暂无图片');
                        }
                        //获取点赞数量
                        array_push($favorcount,ActivityFavor::where('activity_id',$item->data['id'])->count());
                        //获取收藏数量
                        array_push($favoritecount,ActivityFavorite::where('activity_id',$item->data['id'])->count());
                    }
                    if($about)
                        return array('code'=>1000,'data'=>array('activity'=>$about,'pics'=>$activitypics,'favorcount'=>$favorcount,'favoritecount'=>$favoritecount),'message'=>'获取活动成功！');
                    else
                        return array('code'=>1000,'data'=>array(),'message'=>'没有相关活动！');
                }
            }
            //所有的
            elseif($type == 4)
            {
                $res = [];
                $activitypics = [];
                $favorcount = [];
                $favoritecount = [];
                $allactivitys = Activity::where('is_delete',0)->select();
                foreach ($allactivitys as $item)
                {
                    $attach = [];
                    array_push($res,$item->data);
                    $pics = ActivityAttach::where('activity_id',$item->data['id'])->select();
                    if(count($pics) > 0)
                    {
                        foreach($pics as $pic)
                            array_push($attach,$pic->data);
                        array_push($activitypics,$attach);
                    }
                    else
                    {
                        array_push($activitypics,$item->data['id'].'该活动暂无图片');
                    }
                    //获取点赞数量
                    array_push($favorcount,ActivityFavor::where('activity_id',$item->data['id'])->count());
                    //获取收藏数量
                    array_push($favoritecount,ActivityFavorite::where('activity_id',$item->data['id'])->count());
                }

                if($allactivitys)
                    return array('code'=>1000,'data'=>array('activity'=>$res,'pics'=>$activitypics,'favorcount'=>$favorcount,'favoritecount'=>$favoritecount),'message'=>'获取活动成功！');
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
            $activitypics = [];
            $attach = [];
            $activity = Activity::where(['id'=>$_REQUEST['id'],'is_delete'=>0])->find();
            $pics = ActivityAttach::where('activity_id',$_REQUEST['id'])->select();
            if(count($pics) > 0)
            {
                foreach($pics as $pic)
                    array_push($attach,$pic->data);
                array_push($activitypics,$attach);
            }
            else
            {
                array_push($activitypics,'该活动暂无图片');
            }
            if($activity)
            {
                return array('code' => 1000,
                'data' => array('activity'=>$activity->data,'pics'=>$activitypics),
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
                $objactivity = Activity::get($_REQUEST['id']);
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
                $objactivity = Activity::get($_REQUEST['id']);
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
