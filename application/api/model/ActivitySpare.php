<?php
/**
 * Create by: yufei
 * Date: 2019/7/17
 * Time: 9:59
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivitySpare extends Model
{

    /**
     * Notes:新增活动备品
     * @return array
     * author: Fei
     * Time: 2019/7/17 10:11
     */
    public function addSpare()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $spare = new ActivitySpare();
            $id = Snowflake::getsnowId();
            $resval = $spare->validate(
                [
                    'goods_name' => 'require',
                    'carry_count'=>'>:0',
                ],
                [
                    'goods_name.require' => '名称不能为空!',
                    'carry_count.>'=>'数量不能为0!',
                ]
            )->save(['id' => $id,
                'activity_id' => $_REQUEST['activityId'],
                'type_id' => $_REQUEST['typeId'],
                'brand_name'=>$_REQUEST['brand_name'],
                'goods_name'=>$_REQUEST['goods_name'],
                'carry_count'=>$_REQUEST['carry_count'],
                'goods_weight'=>$_REQUEST['goods_weight'],
                'goods_volume'=>$_REQUEST['goods_volume'],
                'creator' => $user['data']['id']
            ]);
            if($resval)
            {
                $obj = ActivitySpare::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '添加活动备品成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $spare->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $spare->error);
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
     * Notes:获取活动备品
     * @return array
     * author: Fei
     * Time: 2019/7/17 10:15
     */
    public function getSpare()
    {
        try
        {
            $spare = new ActivitySpare();
            $spares = $spare->where('activity_id' , $_REQUEST['activityId'])
                ->order('create_time')->select();
            if(empty($spare->error))
            {
                $res = [];
                foreach ($spares as $item)
                    array_push($res,$item->data);
                if(count($res) > 0)
                {
                    return array('code' => 1000,
                        'data' => $res,
                        'message'=> '获取活动备品成功！');
                }
                else
                {
                    return array('code' => 1000,
                        'data' => array(),
                        'message'=> '活动暂无备品记录！');
                }

            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $spare->error);
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
