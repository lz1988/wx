<?php
/**
  * wechat php test
  */

//define your token
header("Content-type:text/html;charset=utf-8");
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();
//$wechatObj->valid();

class wechatCallbackapiTest
{
    /*public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }*/

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
                
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $RX_TYPE = trim($postObj->MsgType);

                switch($RX_TYPE)
                {
                    case "text":
                        $resultStr = $this->handleText($postObj);
                        break;
                    case "event":
                        $resultStr = $this->handleEvent($postObj);
                        break;
                    default:
                        $resultStr = "Unknow msg type: ".$RX_TYPE;
                        break;
                }
                echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    public function handleText($postObj)
    {
        $keyword = trim($postObj->Content); 

        if(!empty( $keyword ))
        {
            $msgType    = "text";

            if (strtolower($keyword) == "nba")
            {
                $contentStr = "<a href='http://nba.sina.cn/'>NAB新闻</a>";
            }
            elseif ($keyword == "新闻")
            {
                $contentStr = "<a href='http://3g.sina.com.cn/'>Sina新闻</a>";
            }else{

                $str        = mb_substr($keyword,-2,2,"UTF-8");
                $str_key    = mb_substr($keyword,0,-2,"UTF-8");

                if($str == '天气' && !empty($str_key))
                {
                    $data = $this->weather($str_key);
                    if(empty($data->weatherinfo))
                    {
                        $contentStr = "抱歉，没有查到\"".$str_key."\"的天气信息！";
                    } else {
                        $contentStr = "【".$data->weatherinfo->city."天气预报】\n".$data->weatherinfo->date_y." ".$data->weatherinfo->fchh."时发布"."\n\n实时天气\n".$data->weatherinfo->weather1." ".$data->weatherinfo->temp1." ".$data->weatherinfo->wind1."\n\n温馨提示：".$data->weatherinfo->index_d."\n\n明天\n".$data->weatherinfo->weather2." ".$data->weatherinfo->temp2." ".$data->weatherinfo->wind2."\n\n后天\n".$data->weatherinfo->weather3." ".$data->weatherinfo->temp3." ".$data->weatherinfo->wind3;
                    }
                }
                elseif (strpos($keyword,'翻译') !== false && mb_substr($keyword,0,2,"UTF-8") == "翻译"){
                    $word       = mb_substr($keyword,2,220,"UTF-8");
                    $contentStr = $this->baiduDic($word);
                }else{
                   /*$contentStr = "感谢您关注【新闻志哥哥】"."\n"."微信号：xinwenzhigege"."\n".
                              "目前平台功能如下："."\n"."【1】 查天气，如输入：深圳+天气"."\n"."【2】 翻译，如输入：翻译+你好"."\n"."【3】 看新闻，如输入：新闻或者nba"."\n";
                              */
                    $record=array(
                        'title' =>'山塘街',
                        'description' =>'山塘街东起阊门渡僧桥，西至苏州名胜虎丘山的望山桥，长约七里，所以苏州俗语说“七里山塘到虎丘”...',
                        'picUrl' => 'http://thinkshare.duapp.com/images/suzhou.jpg',
                        'url' =>'http://mp.weixin.qq.com/mp/appmsg/show?__biz=MjM5NDM0NTEyMg==&appmsgid=10000046&itemidx=1&sign=9e7707d5615907d483df33ee449b378d#wechat_redirect'
                    );
                }
            }
            
            //$resultStr  = $this->responseText($postObj,$contentStr);
            //echo $resultStr;

            $resultStr = response_news($postObj,$record);
            echo $resultStr;
        }else{
            echo "Input something...";
        }
    }


    public function handleEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "感谢您关注【新闻志哥哥】"."\n"."微信号：xinwenzhigege"."\n".
                              "目前平台功能如下："."\n"."【1】 查天气，如输入：深圳+天气"."\n"."【2】 翻译，如输入：翻译+你好"."\n"."【3】 看新闻，如输入：新闻或者nba"."\n";
                break;
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }
    
    /*封装公共发送xml方法*/
    public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

    private function weather($n){
        include("weather_cityId.php");
        $c_name=$weather_cityId[$n];
        if(!empty($c_name)){
            $json=file_get_contents("http://m.weather.com.cn/data/".$c_name.".html");
            return json_decode($json);
        } else {
            return null;
        }
    }

    /*baidu翻译*/
    public function baiduDic($word,$from="auto",$to="auto"){
        
        //首先对要翻译的文字进行 urlencode 处理
        $word_code=urlencode($word);
        
        //注册的API Key
        $appid="uVkNBqbZ7MVmi7aBzM3qYpOq";
        
        //生成翻译API的URL GET地址
        $baidu_url = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=".$appid."&q=".$word_code."&from=".$from."&to=".$to;
        
        $text=json_decode($this->language_text($baidu_url));

        $text = $text->trans_result;

        return $text[0]->dst;
    }
        
    //百度翻译-获取目标URL所打印的内容
    public function language_text($url){

        if(!function_exists('file_get_contents')){
            $file_contents = file_get_contents($url);
        }else{
            //初始化一个cURL对象
            $ch = curl_init();
            $timeout = 5;
            //设置需要抓取的URL
            curl_setopt ($ch, CURLOPT_URL, $url);
            //设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            //在发起连接前等待的时间，如果设置为0，则无限等待
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            //运行cURL，请求网页
            $file_contents = curl_exec($ch);
            //关闭URL请求
            curl_close($ch);
        }

        return $file_contents;
    }

    public function response_news($object,$newsContent)
    {
        $newsTplHead = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>1</ArticleCount>
                <Articles>";
        $newsTplBody = "<item>
                <Title><![CDATA[%s]]></Title> 
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
                </item>";
        $newsTplFoot = "</Articles>
                <FuncFlag>0</FuncFlag>
                </xml>";

        //$newsTplHead
        $header = sprintf($newsTplHead, $object->FromUserName, $object->ToUserName, time());

        //newsTplBody
        $title = $newsContent['title'];
        $desc = $newsContent['description'];
        $picUrl = $newsContent['picUrl'];
        $url = $newsContent['url'];
        $body = sprintf($newsTplBody, $title, $desc, $picUrl, $url);

        //newsTplFoot
        $FuncFlag = 0;
        $footer = sprintf($newsTplFoot, $FuncFlag);

        return $header.$body.$footer;
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}

?>