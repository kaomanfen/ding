<?php

require '../vendor/autoload.php';

//***获取用户信息***
//搜索获取 第二个参数可以为邮箱、工号、钉钉ID等
$info4 = Kmf\Ding\Api::query(Kmf\Ding\Api::QUERY_USER_INFO,'jxl@kmf.com');

//获取事业部全部员工数据
$info4 = Kmf\Ding\Api::query(Kmf\Ding\Api::GET_ALL_USERS);

//返回
/*
array(1) {
    [0]=>
  array(5) {
        ["dingid"]=>
    string(18) "xxx"
        ["workcode"]=>
    string(6) "xxx"
        ["name"]=>
    string(9) "蒋新良"
        ["email"]=>
    string(11) "jxl@kmf.com"
        ["avatar"]=>
    string(68) "http://static.dingtalk.com/media/lADPACOG81VEEULNAu7NAu4_750_750.jpg"
  }
}*/

//***消息推送***

//基于邮箱
//单发
$info4 = Kmf\Ding\Api::query(Kmf\Ding\Api::SEND_MSG_EMAIL,'jxl@kmf.com','你好 加油 加油');
//批量
$info4 = Kmf\Ding\Api::query(Kmf\Ding\Api::SEND_MSG_EMAIL,['jxl@kmf.com','zhangzhengyu@kmf.com'],'你好 加油 加油');

//钉钉ID版
$info4 = Kmf\Ding\Api::query(Kmf\Ding\Api::SEND_MSG_DINGID,'dingdingid1','你好 加油 加油');
$info4 = Kmf\Ding\Api::query(Kmf\Ding\Api::SEND_MSG_DINGID,['dingdingid1','dingdingid2'],'你好 加油 加油');

//返回
/*array(3) {
  ["errcode"]=>
  int(0)
  ["errmsg"]=>
  string(2) "ok"
  ["messageId"]=>
  string(32) "0eb035c4a5d93e1e95894c0c2bebbba0"
}*/


