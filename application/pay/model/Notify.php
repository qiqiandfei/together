<?php


namespace app\pay\model;


use think\Log;
use WxPayApi;
use WxPayConfig;
use WxPayNotify;

class Notify extends WxPayNotify
{

    /**
     * Notes:微信支付回调函数
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:35
     * @param \WxPayNotifyResults $objData
     * @param \WxPayConfigInterface $config
     * @param string $msg
     * @return bool|\true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
     * @throws \WxPayException
     */
    public function NotifyProcess($objData, $config, &$msg)
    {
        $data = $objData->GetValues();
        //TODO 1、进行参数校验
        if(!array_key_exists("return_code", $data)
            ||(array_key_exists("return_code", $data) && $data['return_code'] != "SUCCESS")) {
            //TODO失败,不是支付成功的通知
            $msg = "异常";
            Log::error("微信支付回调：异常！");
            return false;
        }
        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            Log::error("微信支付回调：输入参数不正确！");
            return false;
        }

        //TODO 2、进行签名验证
        try {
            $checkResult = $objData->CheckSign($config);
            if($checkResult == false){
                //签名错误
                $msg = "签名错误...";
                Log::error("微信支付回调：签名错误...");
                return false;
            }
        } catch(Exception $e) {
            Log::error("微信支付回调：".$e->getMessage());
            $msg = $e->getMessage();
        }

        //TODO 3、处理业务逻辑
        Log::info("call back:" . json_encode($data));

        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            Log::error("微信支付回调：订单查询失败！");
            return false;
        }
        else
            model('order')->updorderstate($data['out_trade_no'],1,$data['openid'],round($data['total_fee']/100,2));

        return true;
    }


    /**
     * Notes:查询订单
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:35
     * @param $transaction_id
     * @return bool
     * @throws \WxPayException
     */
    private function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);

        $config = new WxPayConfig();
        $result = WxPayApi::orderQuery($config, $input);
        Log::DEBUG("query:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }
}
