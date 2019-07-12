<?php
/**
 * Created by PhpStorm.
 * User: Fei
 * Date: 2019/3/27
 * Time: 8:58
 */

namespace app\api\controller;
use think\Cache;
use think\Controller;
use think\View;

class Test extends  Controller
{
    public function test()
    {
        $view = new View();
        return $view->fetch();

    }

    public function getReqtoken()
    {
        $randstr = $_REQUEST['randStr'];
        $cryptstr = aesencrypt($randstr);
        Cache::store('redis')->set($cryptstr,$randstr,3600*8);
        json(1000,array('token'=>$cryptstr),'token请求成功，token有效期为8小时！');
    }

    public function sendSmsCode()
    {
        $mobileNumber = $_REQUEST['mobileNumber'];
        $reqToken = $_REQUEST['reqToken'];
        $ranStr = $_REQUEST['ranStr'];
        $chkres = check_request($reqToken,$ranStr);
        //验证请求是否合法
        if($chkres['code'] == 1000)
        {
            $res = send_smscode($mobileNumber);
            json($res['code'],$res['data'],$res['message']);
        }
        else
        {
            json($chkres['code'],$chkres['data'],$chkres['message']);
        }

    }

    public function crtActivity()
    {
        $param = $_REQUEST;
        $encryparam = $param['encryparam'];
        $encrystr = $param['encrystr'];
        //验证请求是否合法
        if(check_request($encryparam, $encrystr)) {
            //实例化对象
            $activity = new \app\api\model\Activity();
            $res = $activity->crtActivity($param);
            json($res['code'], $res['data'], $res['message']);
        } else {
            json(5000, array(), '非法请求！');
        }
    }

    public function pagetest()
    {
        $openId = $_REQUEST['openid'];
        $time = (string)time();
        //生成token
        $token = aesencrypt($openId.$time);
        Cache::store('redis')->set($token,$time);

        //$token = $_REQUEST['token'];
        try
        {
            $value = Cache::store('redis')->get($token);
        }
        catch (\think\exception $e)
        {
            $err = $e->getMessage();
        }

        $curtime = time();
        //token过期
        if($curtime - (int)$value < 3600 * 8)
        {
            $decstr = aesdecrypt($token);
            $openid = substr($decstr,0,strlen($decstr) - strlen($value));
            $newtoken = aesencrypt($openid.$time);
            //删除过期token
            Cache::store('redis')->rm($token);
            $value = Cache::store('redis')->get($token);
            //缓存新token
            Cache::store('redis')->set($newtoken,$time);
            return array('token' => $newtoken, 'openId' => $openid);
        }
        else
            return array();
    }

    public function getinfo()
    {
        $model = new \app\api\model\User();
        //返回用户信息
        $res = $model->getinfo();
        json($res['code'],$res['data'],$res['message']);
    }

    public function addUser()
    {
        $encryptedData = $_REQUEST['encryptedData'];
        $iv = $_REQUEST['iv'];
        $openid = $_REQUEST['openid'];
        $decryptres = decryptData($encryptedData, $iv, $openid,$data);
        if ($decryptres == "OK")
        {
            $user = new \app\api\model\User();
            $res = $user->addUser($data,"18797093035");
            json($res['code'],$res['data'],$res['message']);

        }
        else
        {
            json(7000,array(),$decryptres);
        }
    }
}
