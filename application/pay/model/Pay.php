<?php


namespace app\api\model;
use JsApiPay;
use WxPayApi;
use WxPayConfig;
use WxPayUnifiedOrder;

class Pay
{

    /**
     * Notes:微信JSPAI支付
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:35
     * @param $pay
     * @return \json数据，可直接填入js函数作为参数
     * @throws \WxPayException
     */
    public function jsapipay($pay)
    {
        //获取传入openid
        $openid = $pay['openid'];
        $input = new WxPayUnifiedOrder();
        $tools = new JsApiPay();
        //商品描述
        $input->SetBody('支付充值');
        //附加信息
        $input->SetAttach('一起吧孩子们');
        //商户订单号
        $input->SetOut_trade_no($pay['orderid']);
        $input->SetTotal_fee($pay['fee'] * 100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetNotify_url("https://comeonkids.cn/pay/notify/NotifyProcess");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid);
        $config = new WxPayConfig();
        $order = WxPayApi::unifiedOrder($config, $input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        return $jsApiParameters;
    }
}
