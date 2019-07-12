<?php
/**
 * Create by: yufei
 * Date: 2019/7/9
 * Time: 17:28
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityBudget extends Model
{
    /***
     * Notes:新增活动花费
     * @param $param
     * @return array
     * author: Fei
     * Time: 2019/7/9 17:44
     */
    public function abAdd($param)
    {
        try
        {
            $user = model('user')->getUserInfo_token($param['token']);
            $abp = new ActivityBudget();
            $id = Snowflake::getsnowId();
            $resval = $abp->validate(
                [
                    'activity_id'=>'require',
                    'budget_type_id'=>'require'
                ],
                [
                    'activity_id.require'=>'活动编号不能为空！',
                    'budget_type_id.require'=>'预算类型不能为空！'
                ]
            )->save(['id'=>$id,
                    'activity_id'  => $param['activityId'],
                    'budget_type_id' => $param['budgetTypeId'],
                    'budget_purpose' => $param['budgetPurpose'],
                    'budget_amount' => $param['budgetAmount'],
                    'actual_amount' => $param['actualAmount'],
                    'creator' => $user['data']['id']
                    ]);
            if($resval)
            {
                $obj = $abp::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '创建活动花费成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> '创建活动花费失败，请稍后再试！');
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $abp->error);
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
