<?php
include "wechatAPI.php";
$wechatAPI = new wechatAPI();
$appId = "wx1d7b79083952c222";
$appSecret = "bf3de1b011cf707590b7ab86ee6574a2";
if (isset($_GET['echostr'])) {
    if ($wechatAPI->checkSignature()) {
        echo $_GET['echostr'];
    }
} else {
    $input = $wechatAPI->receiveXMLData();
    $from_user_name = $input->FromUserName;
    $to_user_name = $input->ToUserName;
    $access_token = $wechatAPI->get_access_token($appId, $appSecret);
    $menu=get_menu();
    $wechatAPI->delete_menu($access_token);
    $wechatAPI->set_menu($access_token,$menu);
    if ($input->MsgType == 'text') {
        $content = $input->Content;
        $wechatAPI->sendText($from_user_name, $to_user_name, "auto text reply!");
    }
}
function get_menu()
{
    return '{  
     "button":[  
      {      
               "type":"view",  
               "name":"精选课程",  
               "url":"https://w.url.cn/s/ASOsHnk"  
      },
      {  
           "name":"优研优选",  
           "sub_button":[  
            {      
               "type":"click",  
               "name":"院校&导师",  
               "key":"SCHOOCL_TEACHER"  
            },  
            {  
               "type":"view",  
               "name":"快速登录",  
               "url":"http://www.uyanuxuan.com/index.php"  
            },  
            {  
               "type":"view",  
               "name":"导师计划",  
               "url":"http://www.uyanuxuan.com/index.php/Home/About/xsjh.html"  
            }]  
       },  
        {  
           "name":"我的",  
           "sub_button":[  
            {      
               "type":"click",  
               "name":"联系我们",  
               "key":"CONTACTUS"  
            },  
            {  
               "type":"view",  
               "name":"正版软件",  
               "url":"http://www.xmypage.com/model2_37685.html"  
            },  
            {  
               "type":"view",  
               "name":"四六级冲刺",  
               "url":"https://h5.youyinian.cn/"  
            }]  
        }        
       ]  
 }';
}

?>