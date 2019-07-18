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
     * Notes:添加花费明细
     * @return array
     * author: Fei
     * Time: 2019/7/12 10:07
     */
    public function abpAdd()
    {
        try
        {
            if(empty($_REQUEST['budget_id']))
                $budgetid = 0;
            else
                $budgetid = $_REQUEST['budget_id'];

            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $abp = new ActivityBudgetPayment();
            $id = Snowflake::getsnowId();
            $resval = $abp->validate(
                [
                    'activity_id'=>'require',
                    'user_id'=>'require',
                    'payment_amount'=>'require|eq:0'
                ],
                [
                    'activity_id.require'=>'活动编号不能为空！',
                    'user_id.require'=>'分摊用户编号不能为空！',
                    'payment_amount.require' => '支付金额不能为空！',
                    'payment_amount.eq' => '支付金额不能为0！'
                ]
            )->save(['id'=>$id,
                'activity_id'  => $_REQUEST['activity_id'],
                'budget_id' => $budgetid,
                'user_id' => $_REQUEST['user_id'],
                'payment_channel' => $_REQUEST['payment_channel'],
                'payment_amount' => $_REQUEST['payment_amount'],
                'creator' => $user['data']['id']
            ]);
            if($resval)
            {
                $obj = ActivityBudgetPayment::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '创建活动花费明细成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $abp->error);
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
