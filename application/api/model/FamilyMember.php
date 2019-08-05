<?php
/**
 * Create by: yufei
 * Date: 2019/7/9
 * Time: 19:51
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class FamilyMember extends Model
{
    /**
     * Notes:新增家庭成员
     * @return array
     * author: Fei
     * Time: 2019/7/9 20:09
     */
    public function addFamilymember()
    {
        try
        {
            $activityid = 0;
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            if($_REQUEST['userid'] == 0)
            {
                $userid = $user['data']['id'];
            }
            else
            {
                $userid = $_REQUEST['userid'];
                $activityid = $_REQUEST['activityid'];
            }

            $member = new FamilyMember();
            $member->startTrans();
            $id = Snowflake::getsnowId();
            $resval = $member->validate(
                [
                    'member_title' => 'require',
                ],
                [
                    'member_title.require' => '称谓不能为空！',
                ]
            )->save(['id' => $id,
                    'family_id' => $_REQUEST['familyid'],
                    'user_id' => $userid,
                    'member_title' => $_REQUEST['membertitle'],
                    'is_head' => $_REQUEST['ishead'],
                    'is_delete' => 0,
                    'creator' => $user['data']['id']
                ]);
            if($resval)
            {
                //活动不为零的时候更新活动成员表的家庭id
                if($activityid != 0)
                {
                    $attender = new ActivityAttender();
                    $attender->startTrans();
                    $attender->where(['family_member_id'=>$userid,
                        'activity_id'=>$activityid])
                        ->update(['family_id'=>$_REQUEST['familyid']]);
                    if(empty($attender->error))
                    {
                        $obj = FamilyMember::get($id);
                        if($obj)
                        {
                            $attender->commit();
                            $member->commit();
                            return array('code' => 1000,
                                'data' => $obj->data,
                                'message'=> '创建家庭成员成功！');
                        }
                        else
                        {
                            $attender->rollback();
                            $member->rollback();
                            return array('code' => 3000,
                                'data' => array(),
                                'message'=> $member->error);
                        }
                    }
                    else
                    {
                        $attender->rollback();
                        $member->rollback();
                        return array('code' => 3000,
                            'data' => array(),
                            'message'=> $member->error);
                    }
                }
                else
                {
                    $obj = FamilyMember::get($id);
                    if($obj)
                    {
                        $member->commit();
                        return array('code' => 1000,
                            'data' => $obj->data,
                            'message'=> '创建家庭成员成功！');
                    }
                    else
                    {
                        $member->rollback();
                        return array('code' => 3000,
                            'data' => array(),
                            'message'=> $member->error);
                    }
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $member->error);
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
     * Notes:删除家庭成员
     * @return array
     * author: Fei
     * Time: 2019/7/18 10:29
     */
    public function delFamilyMember()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $member = new FamilyMember();
            $member->where('id',$_REQUEST['id'])
                ->update(['is_delete'=>1,
                    'operator' => $user['data']['id'],
                    'operate_time'=>date('Y-m-d H:i:s', time())
                    ]);
            if(empty($member->error))
            {
                return array('code' => 1000,
                    'data' => $member->getChangedData(),
                    'message'=> '删除家庭成员成功！');
            }
            else
            {
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> $member->error);
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
     * Notes:编辑家庭成员
     * @return array
     * author: Fei
     * Time: 2019/7/18 10:49
     */
    public function editFamilyMember()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $member = new FamilyMember();
            $familymember = FamilyMember::get($_REQUEST['id']);
            if($familymember->data['user_id'] == $user['data']['id'])
            {
                $member->where('id',$_REQUEST['id'])
                       ->where('is_delete',0)
                       ->update(['member_title'=>$_REQUEST['member_title'],
                        'is_head'=>$_REQUEST['is_head'],
                        'operator' => $user['data']['id'],
                        'operate_time'=>date('Y-m-d H:i:s', time())]);
                if(empty($member->error))
                {
                    return array('code' => 1000,
                        'data' => $member->getChangedData(),
                        'message'=> '编辑家庭成员成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $member->error);
                }
            }
            else
            {
                return array('code' => 4002,
                    'data' => array(),
                    'message'=> '只有一家之主才能编辑家庭成员！');
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
     * Notes:获取家庭成员
     * @return array
     * author: Fei
     * Time: 2019/8/5 13:30
     */
    public function getFamilyMember()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $familymember = FamilyMember::where(['user_id'=>$user['data']['id'],
            'is_delete'=>0])->order('create_time','desc')->select();

            if($familymember)
            {
                $familys = [];
                foreach ($familymember as $item)
                {
                    $family = [];
                    $members = FamilyMember::where(['family_id'=>$item->data['family_id'],
                        'is_delete'=>0])->where('user_id','<>',$user['data']['id'])
                        ->order('create_time','desc')->select();

                    if($members)
                    {
                        foreach ($members as $member)
                            array_push($family,$member->data);
                    }
                    else
                    {
                        return array('code' => 1200,
                            'data' => array(),
                            'message'=> '家庭暂无其他成员!');
                    }
                    if(!$family)
                        array_push($familys,$family);
                }

                if($familys)
                {
                    return array('code' => 1000,
                        'data' => array('familymember'=>$familys),
                        'message'=> '获取家庭成员成功！');
                }
            }
            else
            {
                return array('code' => 1200,
                    'data' => array(),
                    'message'=> '请先创建家庭!');
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
