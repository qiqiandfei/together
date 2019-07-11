<?php
///**
// * Created by PhpStorm.
// * User: Fei
// * Date: 2019/3/8
// * Time: 16:15
// */
//
//namespace app\api\common;
//use think\Config;
//use think\Cache;
//
//class common
//{
//    /**
//     * 检验数据的真实性，并且获取解密后的明文.
//     * @param $encryptedData string 加密的用户数据
//     * @param $iv string 与用户数据一同返回的初始向量
//     * @param $data string 解密后的原文
//     *
//     * @return int 成功0，失败返回对应的错误码
//     * <li>-41001: encodingAesKey 非法</li>
//     * <li>-41003: aes 解密失败</li>
//     * <li>-41004: 解密后得到的buffer非法</li>
//     * <li>-41005: base64加密失败</li>
//     * <li>-41016: base64解密失败</li>
//     */
//    public static function decryptData($encryptedData, $iv, &$data )
//    {
//        $thrdsession = Cache::store('redis')->get('3rdsession');
//        $sessionKey = $thrdsession['sessionkey'];
//
//        $appid = Config::get('wechat')['XCX_AppID'];
//
//        if (strlen($sessionKey) != 24) {
//            return "-41001:encodingAesKey非法";
//        }
//        $aesKey=base64_decode($sessionKey);
//
//
//        if (strlen($iv) != 24) {
//            return "-41002:初始向量非法";
//        }
//        $aesIV=base64_decode($iv);
//
//        $aesCipher=base64_decode($encryptedData);
//
//        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
//
//        $dataObj=json_decode( $result );
//        if( $dataObj  == NULL )
//        {
//            return "-41003:aes 解密失败";
//        }
//        if( $dataObj->watermark->appid != $appid )
//        {
//            return "-41003:aes 解密失败";
//        }
//        $data = (array)json_decode($result);
//        return "OK";
//    }
//
//
//    /**
//    发送请求到指定URL
//     */
//    public static function http_curl($url){
//        $curl = curl_init();
//        curl_setopt($curl,CURLOPT_URL,$url);
//        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,30);
//        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
//        $response=curl_exec($curl);
//        curl_close($curl);
//        return $response;
//    }
//}
