[TOC]
# 使用说明

## 如何布署

 1.配置composer.json,在require中加入以下代码
~~~
"require":{
    "rxlwin/wechat": "dev-master"
 }
~~~
 2.新建wechat配置文件wechatconfig.php,内容如下
~~~
$config=[
    "Token" => "tVn9QaYrv2KV9bsWnM15",
    "appid" => "您的appid",
    "appsecret" => "您的appsecret"
];

\rxlwin\wechat\Wechat::setconfig($config);
~~~
 3.载入config文件
 ~~~
 在您的composer.json文件中加入以下内容
 ----
 "autoload":{
    "files":[
        "您的wechatconfig文件路径"
    ]
 }
 ~~~
 
 ## 方法说明
 本类以符合人类正常思维方式为标准,设置了丰富的接口.
 ###getmessage()
 调用方法
 ~~~
 use rxlwin\wechat\Wechat;
 Wechat::getmessage()
 ~~~
 返回值
 ~~~
   100  取消关注
   101  关注
   102  关注后扫描
   103  自动上传坐标
   104  点击事件
   105  阅读事件
  
   201  发来文本
   301  发来图片
   401  发来声音
   501  发来视频
   601  发来小视频
   701  发来当地位置
 ~~~