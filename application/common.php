
<?php

header('content-type:text/json;charset=utf-8');
use think\Cache;
use think\Config;
use alidayu\Alidayusms as Alidayusms;
use think\Validate;

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

    /**
     * Notes:返回jason数据
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:33
     * @param $code
     * @param $message
     * @param array $data
     */
    function json($code,$data=array(),$message="")
    {
        $result=array(
            'code'=>$code,
            'message'=>$message,
            'data'=> $data,
            'timestamp' => date('Y-m-d H:i:s', time())
        );
        //输出json
        $json = str_replace("\\/", "/", json_encode($result,JSON_UNESCAPED_UNICODE));
        echo $json;
        exit;
    }


    /**
     * Notes:生成N位随机字符串
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:34
     * @param $length
     * @return bool|string
     */
    function getrandomstr($length)
    {
        $strs = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
        $ranstr = substr(str_shuffle($strs),mt_rand(0,strlen($strs)-11),$length);
        return $ranstr;
    }


    /**
     * Notes:生成N位随机数字
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:33
     * @param $length
     * @return bool|string
     */
    function getrandomnum($length)
    {
        $strs = "1234567890";
        $ranstr = substr(str_shuffle($strs),mt_rand(0,strlen($strs)-11),$length);
        return $ranstr;
    }

    /**
     * Notes:获取openid
     * @param $js_code
     * @return string
     * author: Fei
     * Time: 2019/6/21 17:15
     */
    function get_openid($js_code)
    {
        $appid = Config::get('wechat')['XCX_AppID'];
        $secret = Config::get('wechat')['XCX_AppSecret'];
        $grant_type = 'authorization_code';
        $objSession = http_curl("https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$js_code&grant_type=$grant_type");
        $res = (array)json_decode($objSession);
        if(isset($res['errcode']))
        {
            return json_decode($objSession)->errcode . ":" . json_decode($objSession)->errmsg;
        }
        else
        {
            $openid = json_decode($objSession)->openid;
            $sessionkey = json_decode($objSession)->session_key;
            $unionid = json_decode($objSession)->unionid;
            if(Cache::store('redis')->has($openid))
            {
                Cache::store('redis')->rm($openid);
                Cache::store('redis')->set($openid,array('sessionkey'=>$sessionkey,'unionid'=>$unionid),300);
            }
            else
                Cache::store('redis')->set($openid,array('sessionkey'=>$sessionkey,'unionid'=>$unionid),300);
            return $openid;
        }

    }



    /**
     * Notes:图片上传
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:33
     */
    function picupload()
    {
        //判断name为img是否选中文件上传
        if(($_FILES['img']['error']) == 0)
        {
            //判断文件类型是否是图片
            if($_FILES['img']['type']=="image/png" || $_FILES['img']['type']=="image/jpeg" || $_FILES['img']['type']=="image/jpg")
            {
                //检查文件大小是否小于10MB
                if($_FILES["img"]["size"]<=10240000)
                {
                    //获取文件类型 去掉字符“.”
                    $filetypes = explode(".", $_FILES["img"]["name"]);
                    $filetype = end($filetypes); //获取类型后缀 （jpg/png）

                    //uploads下创建当天日期文件夹
                    if(!file_exists("../public/uploads/". date('Y-m-d')))
                        mkdir("../public/uploads/". date('Y-m-d'), 0755, true);

                    //设置图片路径 5位随机字符+当前时间为图片名称
                    $ranstr = getrandomstr(5);
                    $localname = "uploads/". date('Y-m-d') . "/" . $ranstr . date("YmdHis") . "." . $filetype;

                    //压缩图片
                    ((new Imgcompress($_FILES["img"]["tmp_name"],1))->compressImg("../public/" . $localname));

                    //外网访问路径
                    $fullpath = "https://".$_SERVER['SERVER_NAME']."/".$localname;

                    //判断图片是否生成
                    if(file_exists("../public/" . $localname))
                        return array('code'=>1000,'data'=>array('fullpath' => $fullpath),'message'=>'图片上传成功！');
                }
                else
                {
                    return array('code'=>9001,'data'=>array(),'message'=>'上传图片超过限制大小，最大为10M！');
                }
            }
            else
            {
                return array('code'=>9002,'data'=>array(),'message'=>'上传图片格式有误，只支持png或jpg格式！');
            }
        }
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     * <li>-41001: encodingAesKey 非法</li>
     * <li>-41003: aes 解密失败</li>
     * <li>-41004: 解密后得到的buffer非法</li>
     * <li>-41005: base64加密失败</li>
     * <li>-41016: base64解密失败</li>
     */
    function decryptData($encryptedData, $iv, $openid, &$data)
    {
        $info = Cache::store('redis')->get($openid);
        $sessionKey = $info['sessionkey'];

        $appid = Config::get('wechat')['XCX_AppID'];

        if (strlen($sessionKey) != 24) {
            return "-41001:encodingAesKey非法";
        }
        $aesKey=base64_decode($sessionKey);


        if (strlen($iv) != 24) {
            return "-41002:初始向量非法";
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            return "-41003:aes 解密失败";
        }
        if( $dataObj->watermark->appid != $appid )
        {
            return "-41003:aes 解密失败";
        }
        $data = (array)json_decode($result);
        return "OK";
    }


    /**
     * Notes:发送请求到指定URL
     * User: Fei
     * Date: 2019/5/15
     * Time: 14:34
     * @param $url
     * @return bool|string
     */
    function http_curl($url)
    {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,30);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $response=curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * Notes:AES加密
     * User: Fei
     * Date: 2019/6/5
     * Time: 10:18
     * @param $str
     * @return string
     */
    function aesencrypt($string)
    {
        $key = Config::get('aeskey')['key'];
        //AES, 128 ECB模式加密数据
        $strEncode = openssl_encrypt($string, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $strEncode = strtolower(bin2hex($strEncode));
        return $strEncode;
    }

    /**
     * Notes:AES解密
     * User: Fei
     * Date: 2019/6/5
     * Time: 10:32
     * @param $string
     * @return string
     */
    function aesdecrypt($string)
    {
        $key = Config::get('aeskey')['key'];
        $decrypted = openssl_decrypt(hex2bin($string), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        return $decrypted;
    }

    /**
     * Notes:验证请求是否合法
     * @param $param 加密原始字符串
     * @param $string 加密后密文
     * @return bool
     * author: Fei
     * Time: 2019/6/18 13:31
     */
    function check_request($token,$param)
    {
        //判断token是否过期
        $isalive = Cache::store('redis')->has($token);
        if($isalive)
        {
            $encryptparam = aesencrypt($param);
            if($encryptparam == $token)
                return array('code'=>1000,'data'=>array(),'message'=>'验证通过！');

            else
                return array('code'=>5000,'data'=>array(),'message'=>'非法请求！');
        }
        else
            return array('code'=>5002,'data'=>array(),'message'=>'请求token过期，请重新申请！');

    }


    /**
     * Notes:判断请求token;登录token是否合法
     * @param $reqtoken
     * @param $ranstr
     * @param $logintoken
     * @return array
     * author: Fei
     * Time: 2019/7/9 14:17
     */
    function check_req_login($reqtoken,$ranstr,$logintoken)
    {
        //判断token是否过期
        $isalive = Cache::store('redis')->has($reqtoken);
        if($isalive)
        {
            $encryptparam = aesencrypt($ranstr);
            if($encryptparam == $reqtoken)
            {
                if(Cache::store('redis')->has($logintoken))
                    return array('code'=>1000,'data'=>array(),'message'=>'验证通过！');
                else
                    return array('code'=>5002,'data'=>array(),'message'=>'登录token过期，请重新登录！');
            }
            else
                return array('code'=>5000,'data'=>array(),'message'=>'非法请求！');
        }
        else
            return array('code'=>5002,'data'=>array(),'message'=>'请求token过期，请重新申请！');
    }

    /**
     * Notes:调用阿里云接口发送验证码
     * @param $phonenumbers
     * @return array
     * author: Fei
     * Time: 2019/6/21 14:48
     */
    function send_smscode($phonenumbers)
    {
        if(!empty($phonenumbers))
        {
            if(!Validate::is($phonenumbers,'/1[3456789]\d{9}/'))
                return array('code'=>4001,
                    'data'=>array(),
                    'message'=>'手机号码不合法！');
        }
        else
        {
            return array('code'=>4001,
                'data'=>array(),
                'message'=>'请输入正确的手机号码！');
        }
        $smsCode = rand(1000,9999);
        $smsParam = [
            // 必填，设置短信接收号码
            'phoneNumbers' => $phonenumbers,
            // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
            'signName' => '加油吧孩子们',
            // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
            'templateCode' => 'SMS_151549135',
            // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
            'templateParam' => [
                'code' => $smsCode,
            ],
            // 可选，设置流水号
            'outId' => 'yourId',
            // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
            'smsUpExtendCode' => '1234567',
            'TemplateParam' => '{"code":"' . $smsCode . '"}'
        ];

        $response = Alidayusms::sendSms($smsParam);
        if ($response->Code == 'OK')
        {
            Cache::store('redis')->set($phonenumbers,$smsCode,300);
            return array('code'=>1000,
                         'data'=>array(),
                         'message'=>'发送验证码成功，5分钟后过期！');
        }
        else
        {
             return array('code'=>7001,'data'=>array(),'message'=>$response->Message);
        }

    }






