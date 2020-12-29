<?php
include "wechatAPI.php";
$wechatAPI = new wechatAPI();
//$appId = "wx1b999a8c73c76dcf";
//$appSecret = "54aaa16dd13d6c6cd54e0a1eb3b5a81c";
$appId = 'wx1d7b79083952c222';
$appSecret = 'bf3de1b011cf707590b7ab86ee6574a2';
if (isset($_GET['echostr'])) {
    if ($wechatAPI->checkSignature()) {
        echo $_GET['echostr'];
    }
} else {
    $input = $wechatAPI->receiveXMLData();
    $fromUserName = $input->FromUserName;
    $toUserName = $input->ToUserName;
//    $accessToken = $wechatAPI->get_access_token($appId, $appSecret);
//    $menu = getMenu();
//    $wechatAPI->delete_menu($accessToken);
//    $wechatAPI->set_menu($accessToken, $menu);
    if ($input->MsgType == 'text') {
        $content = $input->Content;
        $wechatAPI->sendText($fromUserName, $toUserName, "auto text reply!");
    }
    if ($input->MsgType == 'event') {
        $key = $input->EventKey;
        //打卡功能
        if ($key == 'clock_in') {
            $today=date("Y年m月d日", time());
            $days = getDays();
            $motto = getMotto();
            $msg = "打卡成功!\n今天是".$today.",距离考研还有" . $days . "天。\n今日格言:" . $motto;
            $wechatAPI->sendText($fromUserName, $toUserName, $msg);
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
            },
            {
               "type":"view",
                "name":"房间学习",
                "url":"http://mp.weixin.qq.com/s?__biz=MzkyMzE2NjQ1Mw==&mid=100000002&idx=1&sn=8e52777516516b89077758ec537a8f6a&chksm=41e87298769ffb8e4b4ac0a46755359678dfa923182b8f920805d83adff990bd9eae95fcef83&mpshare=1&scene=23&srcid=1229p9l2cC8EcDE7MaeQjwiT&sharer_sharetime=1609212049366&sharer_shareid=2736de8c30a0af4281b64fd4767e723a#rd"
            }]
       }]
}';
}
function getDays(){
    date_default_timezone_set('PRC');
    $startTime=strtotime(date("Y-m-d"));
    $endTime=strtotime("2021-12-26");
    return round(($endTime-$startTime)/3600/24);
}
function getMotto(){
    return "不要觉得树会在冬天死去,你总会遇到更加强大的自己。";
}
?>