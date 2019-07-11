<?php


namespace app\pay\controller;
use think\Controller;

class Notify extends Controller
{
    /**
     * Notes:微信支付回调，该接口由微信调用，返回true或false
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:36
     * @param $objData
     * @param $config
     * @param $msg
     * @return mixed
     */
    public function NotifyProcess($objData, $config, &$msg)
    {
        $respay = model('pay')->NotifyProcess($objData, $config, $msg);
        return $respay;
    }
}
