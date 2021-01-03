<?php
include "wechatAPI.php";
$wechatAPI = new wechatAPI();
//$appId = "wx1b999a8c73c76dcf";
//$appSecret = "54aaa16dd13d6c6cd54e0a1eb3b5a81c";
$appId = 'wx1d7b79083952c222';
$appSecret = 'bf3de1b011cf707590b7ab86ee6574a2';
//$originalUri='http://weixinproject.applinzi.com/project_1812190132/oauth2.php';
$redirectUri = 'http%3a%2f%2fweixinproject.applinzi.com%2fproject_1812190132%2foauth2.php';
if (isset($_GET['echostr'])) {
    if ($wechatAPI->checkSignature()) {
        echo $_GET['echostr'];
    }
} else {
    $input = $wechatAPI->receiveXMLData();
    $fromUserName = $input->FromUserName;
    $toUserName = $input->ToUserName;
    $accessToken = $wechatAPI->get_access_token($appId, $appSecret);
    $menu = getMenu();
    $wechatAPI->delete_menu($accessToken);
    $wechatAPI->set_menu($accessToken, $menu);
    if ($input->MsgType == 'text') {
        $content = $input->Content;
        $wechatAPI->sendText($fromUserName, $toUserName, "auto text reply!");
    }
    if ($input->MsgType == 'event') {
        $key = $input->EventKey;
        if ($key == 'clock_in') { //打卡功能
            $mem = memcache_init();
            $mem->set('1812190132appId', $appId, 0);
            $mem->set('1812190132appSecret', $appSecret, 0);
            $mem->close();
            $url = getAuth($appId, $redirectUri);
            $content="<a href='".$url."'>点击打卡</a>";
            $wechatAPI->sendText($fromUserName, $toUserName, $content);
        } else {
            $wechatAPI->sendText($fromUserName, $toUserName, $key);
        }
    }
}
function getMenu()
{
    return '{
      "button":[
      {
           "name":"找学校",
           "sub_button":[
            {
               "type":"click",
                "name":"专业信息",
                "key":"major"
            },
            {
               "type":"click",
                "name":"考纲查询",
                "key":"outline"
            },
            {
               "type":"click",
                "name":"分数线查询",
                "key":"score"
            }]
       },
       {
           "name":"干货分享",
           "sub_button":[
            {
               "type":"click",
                "name":"课程资料",
                "key":"course"
            },
            {
               "type":"click",
                "name":"真题下载",
                "key":"download"
            },
            {
               "type":"click",
                "name":"经验分享",
                "key":"experience"
            }]
       },
       {
           "name":"一起上岸",
           "sub_button":[
            {
               "type":"click",
                "name":"每日打卡",
                "key":"clock_in"
            }]
       }]
}';
}

function getAuth($appId, $redirectUri)
{
    return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . $redirectUri . '&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
}

?>