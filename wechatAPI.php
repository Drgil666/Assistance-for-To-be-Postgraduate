<?php
define("TOKEN", "wechat2020");

class wechatAPI
{
    public function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $signatureArr = array($token, $timestamp, $nonce);
        sort($signatureArr, SORT_STRING);
        $signatureStr = implode($signatureArr);
        $signatureStr = sha1($signatureStr);
        if ($signatureStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    public function receiveXMLData()
    {
        $rawData = $GLOBALS['HTTP_RAW_POST_DATA'];
        if (!empty($rawData)) {
            return simplexml_load_string($rawData, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
    }

    public function sendText($from_user_name, $to_user_name, $content)
    {
        $textTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName> 
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        <FuncFlag>0</FuncFlag>
        </xml>";
        $text = sprintf($textTpl, $from_user_name, $to_user_name, time(), 'text', $content);
        echo $text;
    }

    /******************************* 发送图文消息方法，向微信平台发送XML封装好的图文内容 *********************
     * 输入数组格式为:
     * $newsArray = [];
     * $newsArray = array("Title"=>"图文消息1", "Description"=>"图文消息描述", "PicUrl"=>"图片链接地址", "Url"=>"文章链接地址");
     * @param $from_user_name
     * @param $to_user_name
     * @param $newsArray
     */
    public function sendNews($from_user_name, $to_user_name, $newsArray)
    {
        $itemTpl = "<item>
							<Title><![CDATA[%s]]></Title>
							<Description><![CDATA[%s]]></Description>
							<PicUrl><![CDATA[%s]]></PicUrl>
							<Url><![CDATA[%s]]></Url>
					</item>";

        $item_str = "";
        if (is_array($newsArray)) {
            foreach ($newsArray as $news) {
                $item_str .= sprintf($itemTpl, $news["Title"], $news["Description"], $news["PicUrl"], $news["Url"]);
            }
        }
        $xmlTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<ArticleCount>%s</ArticleCount>
					<Articles>%s</Articles>
				</xml>";
        $news_str = sprintf($xmlTpl, $from_user_name, $to_user_name, time(), 'news', count($newsArray), $item_str);
        echo $news_str;
    }

    public function get_access_token($appid, $appsecret)
    {
        $url_tmp = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s'; //微信access token接口地址
        $url = sprintf($url_tmp, $appid, $appsecret);
        $channel = curl_init(); //创建连接通道
        curl_setopt($channel, CURLOPT_URL, $url); //建立连接
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, TRUE); //回传数据
        $json_data = curl_exec($channel); //提取数据
        curl_close($channel); //关闭连接
        $data = json_decode($json_data, TRUE); //json格式数据解码成数组
        return $data['access_token'];
    }


    //*****************调用菜单设置接口设置菜单 ******************************************************/
    public function set_menu($access_token, $json_menu)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
        $data_recieved = $this->http_request($url, $json_menu);
        return $data_recieved;
    }

    //*********************调用菜单查询接口查询菜单 */
    public function get_menu($access_token)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=' . $access_token;
        $data_recieved = $this->http_get($url);
        return $data_recieved;
    }

    //*********************调用菜单删除接口删除菜单 */
    public function delete_menu($access_token)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $access_token;
        $data_recieved = $this->http_get($url);
        return $data_recieved;
    }

    //***************************http请求方式为GET的访问函数 */
    public function http_get($url)
    {
        $channel = curl_init(); //创建连接通道
        curl_setopt($channel, CURLOPT_URL, $url); //建立连接
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, TRUE); //回传数据
        $json_data = curl_exec($channel); //提取数据
        curl_close($channel); //关闭连接
        return $json_data;
    }

    //***************************将给定json格式数据$data发送给指定$url接口，并获取返回至$data_recieved中 */
    public function http_request($url, $data)
    {
        $channel = curl_init(); //创建连接通道
        curl_setopt($channel, CURLOPT_URL, $url); //建立连接
        curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, FALSE); //终止服务器验证
        curl_setopt($channel, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($channel, CURLOPT_POST, TRUE); //请求方式为POST
            curl_setopt($channel, CURLOPT_POSTFIELDS, $data); //数据表单
        }
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, TRUE); //回传数据
        $data_recieved = curl_exec($channel); //提取数据
        curl_close($channel); //关闭连接
        return $data_recieved;
    }

}

?>