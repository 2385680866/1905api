<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

class TestController extends Controller
{
    /**公钥签名
     * [resSign description]
     * @return [type] [description]
     */
    public function rsaSign()
    {
        $data = "fule";//发送的数据
        //计算签名
        $path = storage_path("keys/testpriv.key"); //私钥的路径
        $pkeyid = openssl_pkey_get_private("file://" . $path);
        //计算签名得到 signature
        openssl_sign($data, $signature, $pkeyid);
        openssl_free_key($pkeyid);
        //使用base64
        $sign_str = base64_encode($signature);
        echo "base64_encode 后的签名:" .$sign_str;
        $url="http://1905passport.com/test/rsaSign1?sign=".urlencode($sign_str);
        $response=file_get_contents($url);
        echo $response;
    }
    /**签名
     * [MD5 description]
     */
    public function md5SignGet()
    {
        $data = "Hello world";
        $key = "1905";
        $sign = md5($data . $key);
        $url="http://1905passport.com/test/md5SignGet?data=".$data.'&sign='.$sign;
        $response=file_get_contents($url);
        echo $response;
    }
    /**签名
     * [md5Sign2 description]
     * @return [type] [description]
     */
    public function md5SignPost()
    {
        $key="fule";     //签名key
        $order_info = [
            "order_id" => 'Fl_' . mt_rand(11111,99999),
            "order_amount" =>mt_rand(11111,99999),
            "uid" => 1111,
            "add_time" => time()
        ];
        $data_json=json_encode($order_info);
        //计算签名
        $sign = md5($data_json,$key);
        //post 发送数据
        $client = new Client;
        $url = 'http://1905passport.com/test/md5SignPost';
        $response = $client->request("POST",$url,[
                "form_params" => [
                    "data" => $data_json,
                    "sign" => $sign
                ]
            ]);
        //接收响应数据
        $response_data=$response->getBody();
        echo $response_data;
    }


   	public function alipay()
    {
        $ali_gateway = 'https://openapi.alipaydev.com/gateway.do';  //支付网关
        // 公共请求参数
        $appid = '2016100100643279';
        $method = 'alipay.trade.page.pay';
        $charset = 'utf-8';
        $signtype = 'RSA2';
        $sign = '';
        $timestamp = date('Y-m-d H:i:s');
        $version = '1.0';
        $return_url = 'http://1905api.com/test/alipay/return';       // 支付宝同步通知
        $notify_url = 'http://1905api.com/test/alipay/notify';        // 支付宝异步通知地址
        $biz_content = '';
        // 请求参数
        $out_trade_no = time() . rand(1111,9999);       //商户订单号
        $product_code = 'FAST_INSTANT_TRADE_PAY';
        $total_amount = 0.01;
        $subject = '测试订单' . $out_trade_no;
        $request_param = [
            'out_trade_no'  => $out_trade_no,
            'product_code'  => $product_code,
            'total_amount'  => $total_amount,
            'subject'       => $subject
        ];
        $param = [
            'app_id'        => $appid,
            'method'        => $method,
            'charset'       => $charset,
            'sign_type'     => $signtype,
            'timestamp'     => $timestamp,
            'version'       => $version,
            'notify_url'    => $notify_url,
            'return_url'    => $return_url,
            'biz_content'   => json_encode($request_param)
        ];
        // 字典序排序
        ksort($param);
        // 2 拼接 key1=value1&key2=value2...
        $str = "";
        foreach($param as $k=>$v)
        {
            $str .= $k . '=' . $v . '&';
        }
        $str = rtrim($str,'&');
        // 3 计算签名   https://docs.open.alipay.com/291/106118
        $key = storage_path('keys/app_priv');

        $priKey = file_get_contents($key);
        // dd($priKey);
        $res = openssl_get_privatekey($priKey);
        // var_dump($res);die;
        openssl_sign($str, $sign, $res, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        $param['sign'] = $sign;
        // 4 urlencode
        $param_str = '?';
        foreach($param as $k=>$v){
            $param_str .= $k.'='.urlencode($v) . '&';
        }
        $param_str = rtrim($param_str,'&');
        $url = $ali_gateway . $param_str;
        //发送GET请求
        //echo $url;die;
        header("Location:".$url);
    }
    /**
     * 自动上线函数
     * @return [type] [description]
     */
    public function gitPull()
    {
        $cmd="cd /wwwroot/1905/1905api git pull";
        shell_exec($cmd);
    }
    /**
     * 防刷
     * [postman description]
     * @return [type] [description]
     */
    public function postman()
    {
        //获取token
        dd(1111);
        $token=$_SERVER['HTTP_TOKEN'];
        $request_uri=$_SERVER['REQUEST_URI'];
        $url_hash=md5($token . $request_uri);
        $key='count:url'.$url_hash;
        $time=10;
        $count=Redis::get($key);
        echo "当前接口访问次数:".$count;echo "</br>";
        if($count>=5){
            echo "请勿平凡操作,请 $time 秒后再试";
            Redis::expire($key,$time);die;
        }
        $count=Redis::incr($key);
        echo "count:".$count;die;
    }
}
