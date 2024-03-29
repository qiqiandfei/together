<?php
/**
 * Create by: yufei
 * Date: 2019/7/12
 * Time: 9:29
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\controller;


use think\Controller;

class ActivityBudgetPayment extends Controller
{
    /**
     * Notes:新增花费明细
     * author: Fei
     * Time: 2019/7/12 9:31
     */
    public function abpAdd()
    {
        $param = $_REQUEST;
        //加密前参数
        $ranstr = $_REQUEST['ranStr'];
        //加密后参数
        $reqtoken = $_REQUEST['reqToken'];
        $logintoken = $_REQUEST['token'];
        $checkres = check_req_login($reqtoken,$ranstr,$logintoken);
        //验证请求是否合法
        if($checkres['code'] == 1000)
        {
            //实例化模型
            $model = new \app\api\model\ActivityBudget();
            //新增活动预算
            $res = $model->abpAdd();
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }
}
