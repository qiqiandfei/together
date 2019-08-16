<?php
/**
 * Create by: yufei
 * Date: 2019/7/9
 * Time: 20:41
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;
use think\Validate;

class ActivityAttender extends Model
{
    /**
     * Notes:添加活动用户
     * @return array
     * author: Fei
     * Time: 2019/7/10 13:49
     */
    public function addUser()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            //验证活动id
            if(trim($_REQUEST['activity_id']) == '')
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> '活动编号不能为空！');
            }
            //验证昵称
            if(empty($_REQUEST['nick_name']))
            {
                $nickname = $user['data']['nick_name'];
            }
            else
                $nickname = $_REQUEST['nick_name'];

            //验证手机号码
            if(!empty($_REQUEST['contactNumber']))
            {
                if(!Validate::is($_REQUEST['contactNumber'],'/1[3456789]\d{9}/'))
                    return array('failed:手机号码输入有误！');
                else
                    $contactNumber = $_REQUEST['contactNumber'];
            }
            else
            {
                $contactNumber = $user['data']['mobile'];
            }


            $aa = new ActivityAttender();
            $id = Snowflake::getsnowId();
            $aa->save(['id' => $id,
                'activity_id' => $_REQUEST['activity_id'],
                'family_id' => $_REQUEST['family_id'],
                'family_member_id' => $user['data']['id'],
                'nick_name' => $nickname,
                'contact_number' => $contactNumber,
                'creator'=>$user['data']['id']
            ]);
            $obj = ActivityAttender::get($id);
            if($obj)
            {
                return array('code' => 1000,
                    'data' => $obj->data,
                    'message'=> '新增活动用户成功！');
            }
            else
            {
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> $aa->error);
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
     * Notes:删除活动用户
     * @return array
     * author: Fei
     * Time: 2019/7/10 13:39
     */
    public function delUser()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $aa = new ActivityAttender();
            $delflg = $aa->where('activity_id',$_REQUEST['activity_id'])
                         ->where('family_member_id',$_REQUEST['family_member_id'])
                         ->where('attend_state',1)
                         ->update(['attend_state'=>2,
                                   'operator' => $user['data']['id'],
                                   'operate_time' => date('Y-m-d H:i:s', time())
                                    ]);

            if($delflg)
            {
                return array('code' => 1000,
                    'data' => $aa->getChangedData(),
                    'message'=> '删除活动用户成功！');
            }
            else
            {
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> '删除活动用户失败，请稍后再试！');
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
     * Notes:修改活动用户信息
     * @return array
     * author: Fei
     * Time: 2019/7/10 14:01
     */
    public function editUser()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            //验证手机号码
            if(!empty($_REQUEST['contact_number']))
            {
                if(!Validate::is($_REQUEST['contact_number'],'/1[3456789]\d{9}/'))
                    return array('failed:手机号码输入有误！');
                else
                    $contactNumber = $_REQUEST['contactNumber'];
            }
            else
            {
                $contactNumber = $user['data']['mobile'];
            }
            //验证昵称
            if(empty($_REQUEST['nick_name']))
            {
                $nickname = $user['data']['nick_name'];
            }
            else
                $nickname = $_REQUEST['nick_name'];
            $attender = new ActivityAttender();
            $editflg = $attender->where('activity_id',$_REQUEST['activity_id'])
                          ->where('family_id',$_REQUEST['family_id'])
                          ->where('family_member_id',$_REQUEST['family_member_id'])
                          ->where('attend_state',1)
                          ->update(['nick_name' => $nickname,
                            'contact_number'=>$contactNumber,
                            'operator' => $user['data']['id'],
                            'operate_time' => date('Y-m-d H:i:s', time())
                        ]);

            if($editflg)
            {
                return array('code' => 1000,
                    'data' => $attender->getChangedData(),
                    'message'=> '编辑活动用户成功！');
            }
            else
            {
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> '编辑活动用户失败，请稍后再试！');
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
     * Notes:获取参与活动家庭
     * @return array
     * author: Fei
     * Time: 2019/8/8 13:16
     */
    public function getActivityFamily()
    {
        try
        {
            $activityfamily = ActivityAttender::where('activity_id',$_REQUEST['activityId'])
                ->where('attend_state',1)
                ->distinct('family_id')
                ->select();
            if(count($activityfamily) > 0)
            {
                $familys = [];
                foreach ($activityfamily as $item)
                {
                    array_push($familys,$item->data);
                }
                return array('code' => 1000,
                    'data' => array('familys'=>$familys),
                    'message'=> '获取活动家庭成功！');
            }
            else
            {
                return array('code' => 1000,
                    'data' => array(),
                    'message'=> '暂无家庭加入！');
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
     * Notes:获取参加活动用户
     * @return array
     * author: Fei
     * Time: 2019/7/11 9:37
     */
    public function getActivityUser()
    {
        try
        {
            $users = [];
            $attender = new ActivityAttender();
            $attenders = $attender->where(['activity_id'=>$_REQUEST['activity_id'],'attend_state'=>1])
                ->select();
            foreach($attenders as $item)
            {
                $user = User::get($item->data['family_member_id']);
                array_push($users,$user->data);
            }
            if(count($users) > 0)
                return array('code'=>1000,'data'=>$users,'message'=>'获取参与活动人员成功！');
            else
                return array('code'=>1000,'data'=>array(),'message'=>'没有找到活动或活动尚无人员参与！');
        }
        catch (\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }
}
