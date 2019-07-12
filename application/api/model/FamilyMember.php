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
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $member = new FamilyMember();
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
                    'user_id' => $user['data']['id'],
                    'member_title' => $_REQUEST['membertitle'],
                    'is_head' => $_REQUEST['ishead'],
                    'is_delete' => 0,
                    'creator' => $user['data']['id']
                ]);
            if($resval)
            {
                $obj = $member::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '创建家庭成员成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> '创建家庭失败，请稍后再试！');
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
}
