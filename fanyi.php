<?php
$appid="uVkNBqbZ7MVmi7aBzM3qYpOq";
$word_code = "你好";      
$from   = "auto";
$to 	= "auto";  
//生成翻译API的URL GET地址
$baidu_url = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=".$appid."&q=".$word_code."&from=".$from."&to=".$to;
$content = file_get_contents($baidu_url);
print_r($content);