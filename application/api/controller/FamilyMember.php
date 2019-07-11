<?php
/**
 * Create by: yufei
 * Date: 2019/7/9
 * Time: 20:09
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\controller;


use think\Controller;

class FamilyMember extends Controller
{
    /**
     * Notes:添加家庭成员
     * author: Fei
     * Time: 2019/7/9 20:10
     */
    public function addFamilymember()
    {
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
            $model = new \app\api\model\FamilyMember();
            //创建活动
            $res = $model->addFamilymember();
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($checkres['code'],$checkres['data'],$checkres['message']);
        }
    }
}
