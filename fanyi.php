<?php
$appid="uVkNBqbZ7MVmi7aBzM3qYpOq";
$word_code = "���";      
$from   = "auto";
$to 	= "auto";  
//���ɷ���API��URL GET��ַ
$baidu_url = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=".$appid."&q=".$word_code."&from=".$from."&to=".$to;
$content = file_get_contents($baidu_url);
print_r($content);