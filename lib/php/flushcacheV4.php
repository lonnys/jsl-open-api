<?php
header("Content-Type: text/html; charset=utf-8");
$auth_user = "test"; //刷新刷新验证用户名
$auth_password = "123456"; //刷新刷新验证密码
$username=$_POST["username"];
$passwd=$_POST["password"];
if ($username != $auth_user or $passwd != $auth_password) {
    echo"<script>alert('用户名或密码错误，请重新输入.');history.go(-1);</script>"; 
    echo "用户名或密码错误，请重新输入.";
    exit();
}
		
function make_signiture($secret_key,$data) {
    $param_strings = http_build_query($data);
    $param_strings = urldecode($param_strings);
    $hash = hash_hmac('sha1', $param_strings, $secret_key);
    return $hash;
}

function make_header($user, $token) {
    $b64string = base64_encode($user.':'.$token);
    $header = array('Authorization: Basic ' . $b64string,);
    return $header;
}

function http_request($url, $postdata, $header) {
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    return curl_exec($ch);
}

function main($url, $user, $secret_key, $param) {
    //加速乐开放API主函数
    ksort($param);
    $token = make_signiture($secret_key,$param);
    $header = make_header($user, $token);
    $postdata = http_build_query($param);
    $ret_cont = http_request($url, $postdata, $header);
    return $ret_cont;
}
// 定义操作类型（对应$PARAM_ACTION中的动作，可用动作 list/upsert/purge/del).
$action = 'purge';
// 开放API主要信息，以下信息由加速乐提供.
$API_INFO = array(
    // API接口地址
    'url' => 'https://www.jiasule.com/api/site/'.$action.'/', 
    // 认证用户名
    'user' => '553a075bdf224fb92810e21f', 
    // 认证密钥
    'secret_key' => 'RxHScFXEFq5t18XbpxwQyqyNNKa8cv7eUIJ8PNIvqh72dS1pHcckjGn9dMEzbUfJ', 
);
// 获取清理类型
$type=$_POST["type"];
// 获取操作域
$domain = $_POST["domain"];
// 获取要清理的URL对象
$urls=$_POST["url_list"];
// 时间戳必需定义
$time = time();
// 以下内容值用户可根据情况进行修改.
$PARAM_ACTION = array(
    // 刷新某个域名的缓存
    'purge' => array(
            'domain' => $domain,
            'type' => $type,
            'urls' => $urls,
            'time' => $time,
    ),
);

/* 调用主函数
    函数结构：
    main(API接口地址, 认证用户名, 认证密钥, 操作参数)
*/
$result = main($API_INFO['url'], $API_INFO['user'], $API_INFO['secret_key'], $PARAM_ACTION[$action]);

// 显示操作结果
$ret = json_decode($result,true); 

if($ret["status"] == 'ok') {
    echo "<script>alert('缓存清理指令下达成功！');history.go(-1);</script>"; 
    //echo "缓存清理指令下达成功！";
} else {
	echo "<script>alert('".$ret["msg"]."[".$ret["error_code"]."]');history.go(-1);</script>";
    //echo $result;
}
?>
