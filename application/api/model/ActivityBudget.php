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
    public function addBudget()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $budget = new ActivityBudget();
            $id = Snowflake::getsnowId();
            $resval = $budget->validate(
                [
                    'activity_id'=>'require',
                    'budget_type_id'=>'require'
                ],
                [
                    'activity_id.require'=>'活动编号不能为空！',
                    'budget_type_id.require'=>'预算类型不能为空！'
                ]
            )->save(['id'=>$id,
                    'activity_id'  => $_REQUEST['activityId'],
                    'budget_type_id' => $_REQUEST['budgetTypeId'],
                    'budget_purpose' => $_REQUEST['budgetPurpose'],
                    'budget_amount' => $_REQUEST['budgetAmount'],
                    'actual_amount' => $_REQUEST['actualAmount'],
                    'creator' => $user['data']['id']
                    ]);
            if($resval)
            {
                $obj = $budget::get($id);
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
                        'message'=> $budget->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $budget->error);
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
     * Notes:修改活动花费
     * @param $param
     * @return array
     * author: Fei
     * Time: 2019/7/16 17:19
     */
    public function editBudget()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $budget = new ActivityBudget();
            $resval = $budget->validate(
                [
                    'activity_id'=>'require',
                    'budget_type_id'=>'require'
                ],
                [
                    'activity_id.require'=>'活动编号不能为空！',
                    'budget_type_id.require'=>'预算类型不能为空！'
                ]
            )->isUpdate(true)->save(['id'=>$_REQUEST['id'],
                'budget_type_id' => $_REQUEST['budgetTypeId'],
                'budget_purpose' => $_REQUEST['budgetPurpose'],
                'budget_amount' => $_REQUEST['budgetAmount'],
                'actual_amount' => $_REQUEST['actualAmount'],
                'operator' => $user['data']['id'],
                'operator_time' => date('Y-m-d H:i:s', time())
            ]);
            if($resval)
            {
                $obj = $budget::get($_REQUEST['id']);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '修改活动花费成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $budget->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $budget->error);
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
     * Notes:删除活动花费
     * @return array
     * author: Fei
     * Time: 2019/7/16 17:23
     */
    public function delBudget()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $budget = new ActivityBudget();
            $budget->isUpdate(true)->save([
                'id'=>$_REQUEST['id'],
                'budget_state'=>1,
                'operator' => $user['data']['id'],
                'operator_time' => date('Y-m-d H:i:s', time())
            ]);
            $obj = $budget::get($_REQUEST['id']);
            if(empty($budget->error))
            {
                return array('code' => 1000,
                    'data' => $obj->data,
                    'message'=> '删除活动花费成功！');
            }
            else
            {
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> $budget->error);
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
     * Notes:获取活动花费
     * @return array
     * author: Fei
     * Time: 2019/7/16 17:27
     */
    public function getBudget()
    {
        try
        {
            $res = [];
            $budget = new ActivityBudget();
            $budgets = $budget->where([
                'activity_id'=>$_REQUEST['activity_id'],
                'budget_state'=>0
            ])->order('create_time','desc')->select();
            foreach ($budgets as $item)
            {
                array_push($res,$item->data);
            }
            if(empty($budget->error))
            {
                return array('code' => 1000,
                    'data' => $res,
                    'message'=> '获取活动花费成功！');
            }
            else
            {
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> $budget->error);
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
