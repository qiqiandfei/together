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

class Family extends Model
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
            $family = new Family();
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
                    'family_name' => $_REQUEST['family_name'],
                    'contact_number' => $_REQUEST['contact_number'],
                    'member_count' => $_REQUEST['member_count'],
                    'creator' => $user['id']
                    ]);
            if($resval)
            {
                $obj = $family::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '创建家庭成功！');
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
}
