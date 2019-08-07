<?php
/**
 * Create by: yufei
 * Date: 2019/7/12
 * Time: 9:30
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityBudgetPayment extends Model
{
    /**
     * Notes:添加花费明细,按全部花费分摊
     * @return array
     * author: Fei
     * Time: 2019/7/12 10:07
     */
    public function abpAdd_allcost()
    {
        try
        {
            $abp = new ActivityBudgetPayment();
            $abp->startTrans();

            //删除活动所有花费分摊记录
            $abp->where('activity_id',$_REQUEST['activity_id'])->delete();
            $abp->commit();

            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            //获取活动全部花费
            $allcost = 0.0;
            $obj = ActivityBudget::where(['activity_id'=>$_REQUEST['activity_id'],'budget_state'=>0])->select();
            foreach ($obj as $item)
                $allcost += $item->data['actual_amount'];


            //1：按人头均摊；2：按家庭均摊
            $budgettype = $_REQUEST['budget_type_id'];
            if($budgettype == 1)
            {
                $alluser = 0;
                //获取活动中的所有用户（包括没有在系统中体现的）
                $activityfamilymember = ActivityFamilyMember::where(['activity_id'=>$_REQUEST['activity_id'],'is_delete'=>0])->select();
                foreach($activityfamilymember as $item)
                    $alluser += 1;

                $avgcost = $allcost / $alluser;

                $commitflg = true;
                foreach($activityfamilymember as $item)
                {
                    if($item->data['user_id'] == 0)
                    {
                        $userid = 0;
                        $username = $item->data['user_name'];
                    }
                    else
                    {
                        $userid = $item->data['user_id'];
                        $userinfo = User::get($userid);
                        $username = $userinfo->data['nick_name'];
                    }
                    $id = Snowflake::getsnowId();
                    $abp->save(['id'=>$id,
                        'budget_id'=>0,
                        'activity_id'=>$_REQUEST['activity_id'],
                        'user_id'=>$userid,
                        'user_name'=>$username,
                        'payment_channel'=>2,
                        'payment_amount'=>$avgcost,
                        'creator'=>$user['data']['id']]);
                    if(!empty($abp->error))
                    {
                        $commitflg = false;
                        break;
                    }
                }
                if($commitflg)
                {
                    $abp->commit();
                    $costlist = [];
                    $Payment = ActivityBudgetPayment::where('activity_id',$_REQUEST['activity_id'])->select();
                    foreach ($Payment as $item)
                        array_push($costlist,$item->data);

                    return array(
                        'code' => 1000,
                        'data' => array('ActivityBudgetPayment'=>$costlist),
                        'message'=> '活动花费分摊计算完成！'
                    );
                }
                else
                {
                    $abp->rollback();
                    return array(
                        'code' => 2000,
                        'data' => array(),
                        'message'=> $abp->error
                    );
                }
            }

            if($budgettype == 2)
            {
                $commitflg = true;
                //获取活动所有家庭
                $activityfamily = ActivityFamily::where('activity_id',$_REQUEST['activity_id'])->select();
                $avgcost = $allcost / count($activityfamily);
                foreach($activityfamily as $item)
                {
                    $member = ActivityFamilyMember::where(['activity_id'=>$_REQUEST['activity_id'],
                        'family_id'=>$item->data['id'],
                        'is_head'=>1])->find();
                    if($member)
                    {
                        $id = Snowflake::getsnowId();
                        $abp->save(['id'=>$id,
                            'budget_id'=>0,
                            'activity_id'=>$_REQUEST['activity_id'],
                            'user_id'=>$member->data['user_id'],
                            'user_name'=>$member->data['user_name'],
                            'payment_channel'=>2,
                            'payment_amount'=>$avgcost,
                            'creator'=>$user['data']['id']]);
                        if(!empty($abp->error))
                        {
                            $commitflg = false;
                            break;
                        }
                    }
                }
                if($commitflg)
                {
                    $abp->commit();
                    $costlist = [];
                    $Payment = ActivityBudgetPayment::where('activity_id',$_REQUEST['activity_id'])->select();
                    foreach ($Payment as $item)
                        array_push($costlist,$item->data);

                    return array(
                        'code' => 1000,
                        'data' => array('ActivityBudgetPayment'=>$costlist),
                        'message'=> '活动花费分摊计算完成！'
                    );
                }
                else
                {
                    $abp->rollback();
                    return array(
                        'code' => 2000,
                        'data' => array(),
                        'message'=> $abp->error
                    );
                }

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
     * Notes:添加花费明细,按每项花费分摊
     * @return array
     * author: Fei
     * Time: 2019/8/7 13:22
     */
    public function abpAdd_everycost()
    {
        try
        {
            $abp = new ActivityBudgetPayment();
            $abp->startTrans();

            //删除活动所有花费分摊记录
            $abp->where('activity_id',$_REQUEST['activity_id'])->delete();
            $abp->commit();

            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $bugets = ActivityBudget::where(['activity_id'=>$_REQUEST['activity_id'],'budget_state'=>0])->select();
            foreach($bugets as $buget)
            {
                //1：按人头均摊；2：按家庭均摊
                $budgettype = $buget->data['budget_type_id'];
                if($budgettype == 1)
                {
                    $alluser = 0;
                    //获取活动中的所有用户（包括没有在系统中体现的）
                    $activityfamilymember = ActivityFamilyMember::where(['activity_id'=>$_REQUEST['activity_id'],'is_delete'=>0])->select();
                    foreach($activityfamilymember as $item)
                        $alluser += 1;

                    $avgcost = $buget->data['actual_amount'] / $alluser;

                    $commitflg = true;
                    foreach($activityfamilymember as $item)
                    {
                        if($item->data['user_id'] == 0)
                        {
                            $userid = 0;
                            $username = $item->data['user_name'];
                        }
                        else
                        {
                            $userid = $item->data['user_id'];
                            $userinfo = User::get($userid);
                            $username = $userinfo->data['nick_name'];
                        }
                        $id = Snowflake::getsnowId();
                        $abp->save(['id'=>$id,
                            'budget_id'=>$buget->data['id'],
                            'activity_id'=>$_REQUEST['activity_id'],
                            'user_id'=>$userid,
                            'user_name'=>$username,
                            'payment_channel'=>2,
                            'payment_amount'=>$avgcost,
                            'creator'=>$user['data']['id']]);
                        if(!empty($abp->error))
                        {
                            $commitflg = false;
                            break;
                        }
                    }
                    if($commitflg)
                    {
                        $abp->commit();
                        $costlist = [];
                        $Payment = ActivityBudgetPayment::where('activity_id',$_REQUEST['activity_id'])->select();
                        foreach ($Payment as $item)
                            array_push($costlist,$item->data);

                        return array(
                            'code' => 1000,
                            'data' => array('ActivityBudgetPayment'=>$costlist),
                            'message'=> '活动花费分摊计算完成！'
                        );
                    }
                    else
                    {
                        $abp->rollback();
                        return array(
                            'code' => 2000,
                            'data' => array(),
                            'message'=> $abp->error
                        );
                    }
                }

                if($budgettype == 2)
                {
                    $commitflg = true;
                    //获取活动所有家庭
                    $activityfamily = ActivityFamily::where('activity_id',$_REQUEST['activity_id'])->select();
                    $avgcost = $buget->data['actual_amount'] / count($activityfamily);
                    foreach($activityfamily as $item)
                    {
                        $member = ActivityFamilyMember::where(['activity_id'=>$_REQUEST['activity_id'],
                            'family_id'=>$item->data['id'],
                            'is_head'=>1])->find();
                        if($member)
                        {
                            $id = Snowflake::getsnowId();
                            $abp->save(['id'=>$id,
                                'budget_id'=>$buget->data['id'],
                                'activity_id'=>$_REQUEST['activity_id'],
                                'user_id'=>$member->data['user_id'],
                                'user_name'=>$member->data['user_name'],
                                'payment_channel'=>2,
                                'payment_amount'=>$avgcost,
                                'creator'=>$user['data']['id']]);
                            if(!empty($abp->error))
                            {
                                $commitflg = false;
                                break;
                            }
                        }
                    }
                    if($commitflg)
                    {
                        $abp->commit();
                        $costlist = [];
                        $Payment = ActivityBudgetPayment::where('activity_id',$_REQUEST['activity_id'])->select();
                        foreach ($Payment as $item)
                            array_push($costlist,$item->data);

                        return array(
                            'code' => 1000,
                            'data' => array('ActivityBudgetPayment'=>$costlist),
                            'message'=> '活动花费分摊计算完成！'
                        );
                    }
                    else
                    {
                        $abp->rollback();
                        return array(
                            'code' => 2000,
                            'data' => array(),
                            'message'=> $abp->error
                        );
                    }

                }
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
