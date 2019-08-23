<?php
/**
 * Create by: yufei
 * Date: 2019/7/9
 * Time: 19:41
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Cache;
use think\Model;
use think\Validate;

class ActivityFamily extends Model
{
    /**
     * Notes:创建家庭
     * @return array
     * author: Fei
     * Time: 2019/7/9 19:46
     */
    public function crtFamily()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $family = new ActivityFamily();
            $family->startTrans();
            $familydata = $family->where(['activity_id'=>$_REQUEST['activity_id'],'creator'=>$user['data']['id']])->find();
            if($familydata)
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> '已经在本活动中创建过家庭！');
            }
            $id = Snowflake::getsnowId();
            $resval = $family->validate(
                [
                    'family_name' => 'require',
                    'contact_number'=>'require|regex:/1[3456789]\d{9}/',

                ],
                [
                    'family_name.require' => '家庭名称不能为空！',
                    'contact_number.require' => '手机号码不能为空！',
                    'contact_number.regex' => '手机号码输入有误！',
                ]
            )->save(['id' => $id,
                    'activity_id'=>$_REQUEST['activity_id'],
                    'family_name' => $_REQUEST['family_name'],
                    'contact_number' => $_REQUEST['contact_number'],
                    'member_count' => $_REQUEST['member_count'],
                    'creator' => $user['data']['id']
                    ]);

            $member = new ActivityFamilyMember();
            $member->startTrans();
            $memberid = Snowflake::getsnowId();
            $member->save([
                'id'=>$memberid,
                'activity_id'=>$_REQUEST['activity_id'],
                'family_id'=>$id,
                'user_id'=>$user['data']['id'],
                'user_name'=>$user['data']['nick_name'],
                'member_title'=>'',
                'is_head'=>1,
                'creator'=>$user['data']['id']]);
            if($resval)
            {
                $obj = ActivityFamily::get($id);
                if(empty($family->error) && empty($member->error))
                {
                    $family->commit();
                    $member->commit();
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '创建家庭成功！');
                }
                else
                {
                    $family->rollback();
                    $member->rollback();
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $family->error);
                }
            }
            else
            {
                $family->rollback();
                $member->rollback();
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $family->error);
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
     * Notes:编辑家庭信息
     * @return array
     * author: Fei
     * Time: 2019/8/7 10:07
     */
    public function editFamily()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $family = new ActivityFamily();
            $id = Snowflake::getsnowId();
            $resval = $family->validate(
                [
                    'family_name' => 'require',
                    'contact_number'=>'require|regex:/1[3456789]\d{9}/',
                    'member_count'=>'>=:1'

                ],
                [
                    'family_name.require' => '家庭名称不能为空！',
                    'contact_number.require' => '手机号码不能为空！',
                    'contact_number.regex' => '手机号码输入有误！',
                    'member_count'=>'家庭成员数量不能小于1！'
                ]
            )->isUpdate(true)->save(['id' => $_REQUEST['familyId'],
                'activity_id'=>$_REQUEST['activityId'],
                'family_name' => $_REQUEST['family_name'],
                'contact_number' => $_REQUEST['contact_number'],
                'member_count' => $_REQUEST['member_count'],
                'operator'=> $user['data']['id']
            ]);
            if($resval)
            {
                $obj = ActivityFamily::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '修改家庭成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $family->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $family->error);
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
     * Notes:获取家庭信息
     * @return array
     * author: Fei
     * Time: 2019/8/7 10:29
     */
    public function getFamily()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $attender = ActivityAttender::where(['activity_id'=>$_REQUEST['activityId'],'family_member_id'=>$user['data']['id']])->find();
            if(!$attender)
            {
                return array(
                    'code' => 1200,
                    'data' => array(),
                    'message'=> '请先加入活动！'
                );
            }
            $familyid = $attender->data['family_id'];
            $family = new ActivityFamily();
            $familyinfo = $family->where(['activity_id'=>$_REQUEST['activityId'],'id'=>$familyid])->find();
            if(empty($family->error))
            {
                return array(
                    'code' => 1000,
                    'data' => $familyinfo->data,
                    'message'=> '获取家庭信息成功！'
                );
            }
            else
            {
                return array(
                    'code' => 1100,
                    'data' => array(),
                    'message'=> $family->error
                );
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
     * Notes:获取我的家庭信息
     * @return array
     * author: Fei
     * Time: 2019/8/22 8:57
     */
    public function getMyFamily()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $attender = ActivityAttender::where(['activity_id'=>$_REQUEST['activityId'],'family_member_id'=>$user['data']['id']])->find();
            if(!$attender)
            {
                return array(
                    'code' => 1200,
                    'data' => array(),
                    'message'=> '请先加入活动！'
                );
            }
            $familyid = $attender->data['family_id'];
            $family = new ActivityFamily();
            $familyinfo = $family->where(['activity_id'=>$_REQUEST['activityId'],'id'=>$familyid])->find();
            if(empty($family->error))
            {
                return array(
                    'code' => 1000,
                    'data' => $familyinfo->data,
                    'message'=> '获取家庭信息成功！'
                );
            }
            else
            {
                return array(
                    'code' => 1100,
                    'data' => array(),
                    'message'=> $family->error
                );
            }
        }
        catch(\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }
}
