<?php
include "wechatAPI.php";
header("Content-Type: text/html;charset=utf-8");
$wechatAPI = new wechatAPI();
if (isset($_GET['code'])) {
    $code = $_GET['code'];
//    echo "获取的code为" . $code;
    $mem = memcache_init();
    $appId = $mem->get('1812190132appId');
    $appSecret = $mem->get('1812190132appSecret');
    $mem->close();
    $json_data = $wechatAPI->http_request(getTokenUrl($appId, $appSecret, $code), null);
    $data = json_decode($json_data, TRUE);
    $accessToken = $data["access_token"];
    $openId = $data["openid"];
    $json_info = $wechatAPI->http_request(getUserInfoUrl($accessToken, $openId), null);
//    echo $json_info;
    if (userExist($openId) == 0) {
        createUser($openId);
    }
    $user = getUser($openId);
    if (check($user["last_time"])) {
        updateUser($openId);
        $today = date("Y年m月d日", time());
        $days = getDays();
        $motto = getMotto();
        $clockIn = getUser($openId)["clock_in"];
        $msg = "打卡成功!您已累计打卡" . $clockIn . "次!<br/>今天是" . $today . ",距离考研还有" . $days . "天。<br/>今日格言:" . $motto;
        echo $msg;
    } else {
        echo "您今天已经完成打卡!请明天再来!";
    }
} else {
    echo "NO CODE";
}

function getTokenUrl($appId, $appSecret, $code)
{
    return "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appId .
        "&secret=" . $appSecret . "&code=" . $code . "&grant_type=authorization_code";
}

function getUserInfoUrl($accessToken, $openId)
{
    return "https://api.weixin.qq.com/sns/userinfo?access_token=" . $accessToken . "&openid=" . $openId . "&lang=ZH_CN";
}

function getDays()
{
    date_default_timezone_set('PRC');
    $startTime = strtotime(date("Y-m-d"));
    $endTime = strtotime("2021-12-26");
    return round(($endTime - $startTime) / 3600 / 24);
}

function getMottoList()
{
    $dbname = "assistance";
    $host = "47.111.2.22";
    $port = "3306";
    $user = "root";
    $pwd = "Yez3.1415926";
    $array = array();
    $link = @mysql_connect("{$host}:{$port}", $user, $pwd, true);
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_query("SET NAMES 'UTF8'");
    if (!mysql_select_db($dbname, $link)) {
        die("Select Database Failed: " . mysql_error($link));
    }
    $sql = "select * from motto";
    $ret = mysql_query($sql, $link);
    if ($ret === false) {
        die("Select Failed: " . mysql_error($link));
    } else {
//        echo "Select Succeed";
        while ($row = mysql_fetch_assoc($ret)) {
//            echo "{$row['name']}";
            array_push($array, $row['name']);
        }
    }
    mysql_close($link);
    return $array;
}

function getMotto()
{
    $array = getMottoList();
    return $array[rand(0, sizeof($array) - 1)];
}

function userExist($openId)
{
    $dbname = "assistance";
    $host = "47.111.2.22";
    $port = "3306";
    $user = "root";
    $pwd = "Yez3.1415926";
    $link = @mysql_connect("{$host}:{$port}", $user, $pwd, true);
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }
    if (!mysql_select_db($dbname, $link)) {
        die("Select Database Failed: " . mysql_error($link));
    }
    $sql = "select count(*) from user where username='{$openId}' LIMIT 1";
    $ret = mysql_fetch_array(mysql_query($sql));
    if ($ret === false) {
        die("Select Failed: " . mysql_error($link));
    } else {
        mysql_close($link);
        return $ret[0];
    }
}

function createUser($openId)
{
    $dbname = "assistance";
    $host = "47.111.2.22";
    $port = "3306";
    $user = "root";
    $pwd = "Yez3.1415926";
    $link = @mysql_connect("{$host}:{$port}", $user, $pwd, true);
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }
    if (!mysql_select_db($dbname, $link)) {
        die("Select Database Failed: " . mysql_error($link));
    }
    $sql = "insert into user (username,clock_in) value ('{$openId}',0)";
    mysql_query($sql);
    mysql_close($link);
}

function updateUser($openId)
{
    date_default_timezone_set('PRC');
    $dbname = "assistance";
    $host = "47.111.2.22";
    $port = "3306";
    $user = "root";
    $pwd = "Yez3.1415926";
    $link = @mysql_connect("{$host}:{$port}", $user, $pwd, true);
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }
    if (!mysql_select_db($dbname, $link)) {
        die("Select Database Failed: " . mysql_error($link));
    }
    $sql = "update user set clock_in=clock_in+1,last_time=now() where username='{$openId}'";
    mysql_query($sql);
    mysql_close($link);
}

function getUser($openId)
{
    $dbname = "assistance";
    $host = "47.111.2.22";
    $port = "3306";
    $user = "root";
    $pwd = "Yez3.1415926";
    $link = @mysql_connect("{$host}:{$port}", $user, $pwd, true);
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }
    if (!mysql_select_db($dbname, $link)) {
        die("Select Database Failed: " . mysql_error($link));
    }
    $sql = "select * from user where username='{$openId}'";
    $ret = mysql_fetch_array(mysql_query($sql));
    if ($ret === false) {
        die("Select Failed: " . mysql_error($link));
    } else {
        mysql_close($link);
        return $ret;
    }
}

function check($datetime)
{
//    echo $datetime . '<br/>';
    if ($datetime == null)
        return true;
    date_default_timezone_set('PRC');
    $lastTime = date("Y-m-d", strtotime($datetime));
//    echo $lastTime . '<br/>';
    $nowTime = date("Y-m-d");
//    echo $nowTime;
    return $nowTime != $lastTime;
}

?>