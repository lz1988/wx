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
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";         

        if(!empty( $keyword ))
        {
            $msgType    = "text";

            if ($keyword == "nba")
            {
                $contentStr = "<a href='http://nba.sina.cn/'>NAB新闻</a>";
            }
            elseif ($keyword == "国内")
            {
                $contentStr = "<a href='http://sina.cn/nc.php'>国内新闻</a>";
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
                }else{
                    $contentStr = "感谢您关注【新闻志哥哥】"."\n"."微信号：xinwenzhigege"."\n".
                              "目前平台功能如下："."\n"."【1】 查天气，如输入：深圳天气"."\n";
                }

            }
            
            $resultStr  = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
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
                $contentStr = "感谢您关注【新闻志哥哥】"."\n"."微信号：xinwenzhigege"."\n"."最前沿的新闻资讯，最新的实时交流。"."\n"."目前平台功能如下："."\n"."【1】 查军事，如输入：军事"."\n"."【2】 查国内新闻，如输入：国内新闻"."\n"."【3】 国外新闻，如输入：国外"."\n"."【4】 NBA，如输入：NBA"."\n"."更多内容，敬请期待...";
                break;
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }
    
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