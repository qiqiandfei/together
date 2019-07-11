<?php
/**
 * Create by: yufei
 * Date: 2019/7/10
 * Time: 13:33
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityLabel extends Model
{
    /**
     * Notes:添加活动标签
     * @return array
     * author: Fei
     * Time: 2019/7/10 13:36
     */
    public function addLabel()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $family = new Family();
            $id = Snowflake::getsnowId();
            $resval = $family->validate(
                [
                    'label_name' => 'require',
                    'activity_id'=>'require',
                ],
                [
                    'label_name.require' => '活动标签不能为空!',
                    'activity_id.require'=>'活动编号不能为空!',
                ]
            )->save(['id' => $id,
                    'activity_id' => $_REQUEST['activityId'],
                    'label_name' => $_REQUEST['labelName'],
                    'creator' => $user['id']
                    ]);
            if($resval)
            {
                $obj = $family::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '添加活动标签成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> '添加活动标签失败，请稍后再试！');
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
