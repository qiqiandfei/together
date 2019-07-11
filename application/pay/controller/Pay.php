<?php


namespace app\api\controller;
use think\Controller;

class Pay extends Controller
{

    /**
     * Notes:微信JSPAI支付
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:36
     */
    public function jsapipay()
    {
        $pay = $_REQUEST;
        $respay = model('pay')->jsapipay($pay);
        echo $respay;
        exit();
    }
}
