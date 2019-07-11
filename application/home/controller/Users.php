<?php
/**
 * Created by PhpStorm.
 * User: Fei
 * Date: 2019/3/5
 * Time: 14:45
 */

namespace app\home\controller;
use think\Controller;
use think\Session;
use think\Config;

class Users extends Controller
{

    /**
     主页
     */
    public function index()
    {
//        $userinfo = isset(Session::get('userinfo')['openid']) ? : '';
//        if($userinfo == 'oqE0D1f4qOUi699_Cv35jsqMEHZM' or $userinfo == 'oqE0D1RDsGfttHLnl49kr-UfuLzg')
//        {
//            return $this->fetch();
//        }
//        else
//        {
//            $this->redirect('home/users/build');
//        }

        $this->redirect('home/users/build');
    }

    /**
    微信登录授权
     */
    public function login()
    {
        // 访问域名会优先执行index方法，用以获取到code
        $appid = Config::get('wechat.GZH_AppID');
        $redirect_uri = "https://www.comeonkids.cn/home/users/getUserOpenId";
        $url ="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        header("Location:".$url);exit;
    }


    /**
    请求api_url并返回结果
     */
    public function http_curl($url,$type='get',$res='json',$arr='')
    {
        //1.初始化curl
        $ch = curl_init();
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        //3.采集
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
        if ($res == 'json') {
            return json_decode($output,true);
        }
    }


    /**
     * 微信回调函数
     * 获取用户openid
     * */
    public function getUserOpenId()
    {
        //回调地址会传回一个code，则我们根据code去获取openid和授权获取到的access_token
        $code = $_GET['code'];
        $appid = Config::get('wechat.GZH_AppID');
        $secret = Config::get('wechat.GZH_AppSecret');

        //code换取token
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
        $res = $this->http_curl($url);
        $access_token = $res['access_token'];
        $getopenid = $res['openid'];

        //获取用户授权信息
        $urltoc = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$getopenid."&lang=zh_CN";
        $resinfos = $this->http_curl($urltoc);

        //获取用户性别
        $sex = model("users")->getsex($resinfos['sex']);

        $openid = $resinfos['openid'];

        //判断用户是否存在
        $isexist = model("users")->checkuserexist($openid);
        if(!$isexist)
        {
            //首次进入，则获取用户信息，插入数据库
            model("users")->adduser($resinfos,$sex);
            Session::set('userinfo', $openid);
            $this->redirect('home/users/index');
        }
        else
        {
            //说明是已经是公众号成员，则调用用户信息存到session即可
            $userinfo = model("users")->getuser($openid);
            Session::set('userinfo', $userinfo);
            $this->redirect('home/users/index');
        }
    }

    /**
    网站建设提示
     */
    public function build()
    {
        return $this->fetch();
    }


}
