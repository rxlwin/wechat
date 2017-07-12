<?php
/**
 * Created by PhpStorm.
 * User: rxlwin
 * Date: 2017-7-6
 * Time: 13:46
 */

namespace rxlwin\wechat;


class Base{
    private $config;
    private static $xmlobj=null;
    private static $xmldata = null;
    private static $xmlarray = [];
    public function __construct($config){
        $this->config=$config;
        $this->validate();
        $this->setXMLobj();
    }

    /**
     * 设置验证
     */
    public function validate(){
        if(!isset($_GET['echostr'])) return;
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->config["Token"];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            echo $_GET["echostr"];exit;
        }
    }

    /**
     * 将传来的xml文件转为对象存在静态属性中
     */
    private function setXMLobj(){
        if(!is_null(self::$xmlobj))return;
        if(!isset($GLOBALS['HTTP_RAW_POST_DATA'])) exit;
        $xmldata = $GLOBALS['HTTP_RAW_POST_DATA'];
        self::$xmldata = $xmldata;

        $putdata = $xmldata."\r\n\r\n===================\r\n\r\n";
        file_put_contents("./log.txt",$putdata,FILE_APPEND | LOCK_EX);

        $preg = "/^<xml>.*<\/xml>$/is"; //正则
        if(preg_match($preg,$xmldata)){
            $xmlobj = simplexml_load_string($xmldata);
            self::$xmlobj=$xmlobj;
            self::$xmlarray=$this->objtoarray($xmlobj);
        }
    }

    /**
     * 获取消息
     * 100  取消关注
     * 101  关注
     * 102  关注后扫描
     * 103  自动上传坐标
     * 104  点击事件
     * 105  阅读事件
     *
     * 201  发来文本
     * 301  发来图片
     * 401  发来声音
     * 501  发来视频
     * 601  发来小视频
     * 701  发来当地位置
     */
    public function getType(){
        $xmlobj = self::$xmlobj;
        $MsgType = strtolower($xmlobj->MsgType);
        switch ($MsgType){
            case "event":
                switch (strtolower(self::$xmlobj->Event)){
                    case "unsubscribe":
                        return 100;
                    case "subscribe":
                        return 101;
                    case "scan":
                        return 102;
                    case "location":
                        return 103;
                    case "click":
                        return 104;
                    case "view":
                        return 105;
                }
            case "text":
                return 201;
            case "image":
                return 301;
            case "voice":
                return 401;
            case "video":
                return 501;
            case "shortvideo":
                return 601;
            case "location":
                return 701;
            default:
                return 0;
        }
    }

    /**
     * 将对象转成数组
     * 采用了递归方式
     * @param $obj 参数,对象类型
     * @return array 返回值,数组类型
     */
    private function objtoarray($obj){
        $arr=[];
        if(is_object($obj)){
            foreach ($obj as $k => $v){
                if(count($v)){
                    $arr[$k]=$this->objtoarray($v);
                }else{
                    $arr[$k]=trim($v);
                }
            }
            return $arr;
        }else{
            return $obj;
        }
    }

    /**
     * 返回XML文本内容
     * @return null|string
     * 如果存在文本内容 ,就返回文本内容 ,如果不存在就返回一个null
     */
    public function getXML(){
        return self::$xmldata;
    }

    /**
     * 返回XML转换成对象后的内容
     * @return null
     */
    public function getXMLobj(){
        return self::$xmlobj;
    }

    /**
     * 返回XML转换成数组后的内容
     * @return array
     */
    public function getXMLarray(){
        return self::$xmlarray;
    }

    /**
     * 回复文本信息
     * @param $data 需要回复的文本内容
     */
    public function responsetext($text){
        $fromuser = self::$xmlarray["ToUserName"];
        $touser = self::$xmlarray["FromUserName"];
        $time=time();
        $str=<<<str
<xml>
<ToUserName><![CDATA[{$touser}]]></ToUserName>
<FromUserName><![CDATA[{$fromuser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[{$text}]]></Content>
</xml>
str;
        exit($str);
    }

    /**
     * 回复图片
     * @param $media_id
     */
    public function responseimg($media_id){
        $fromuser = self::$xmlarray["ToUserName"];
        $touser = self::$xmlarray["FromUserName"];
        $time=time();
        $str=<<<str
<xml>
<ToUserName><![CDATA[{$touser}]]></ToUserName>
<FromUserName><![CDATA[{$fromuser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA[{$media_id}]]></MediaId>
</Image>
</xml>
str;
        exit($str);
    }

    /**
     * 回复声音
     * @param $media_id
     */
    public function responsevoice($media_id){
        $fromuser = self::$xmlarray["ToUserName"];
        $touser = self::$xmlarray["FromUserName"];
        $time=time();
        $str=<<<str
<xml>
<ToUserName><![CDATA[{$touser}]]></ToUserName>
<FromUserName><![CDATA[{$fromuser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
<Voice>
<MediaId><![CDATA[{$media_id}]]></MediaId>
</Voice>
</xml>
str;
        exit($str);
    }

    /**
     * 回复视频
     * @param $media_id 必填 通过素材管理中的接口上传多媒体文件，得到的id
     * @param string $title 选填 视频消息的标题
     * @param string $description 选填 视频消息的描述
     */
    public function responsevideo($media_id,$title="",$description=""){
        $fromuser = self::$xmlarray["ToUserName"];
        $touser = self::$xmlarray["FromUserName"];
        $time=time();
        $str=<<<str
<xml>
<ToUserName><![CDATA[{$touser}]]></ToUserName>
<FromUserName><![CDATA[{$fromuser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
<Video>
<MediaId><![CDATA[{$media_id}]]></MediaId>
<Title><![CDATA[{$title}]]></Title>
<Description><![CDATA[{$description}]]></Description>
</Video>
</xml>
str;
        exit($str);
    }

    /**
     * 回复音乐
     * @param $media_id 缩略图的媒体id，通过素材管理中的接口上传多媒体文件，得到的id
     * @param string $title 音乐标题
     * @param string $description 音乐描述
     * @param string $music_url 音乐链接
     * @param string $hq_music_url 高质量音乐链接，WIFI环境优先使用该链接播放音乐
     */
    public function responsemusic($media_id,$title="",$description="",$music_url="",$hq_music_url=""){
        $fromuser = self::$xmlarray["ToUserName"];
        $touser = self::$xmlarray["FromUserName"];
        $time=time();
        $str=<<<str
<xml>
<ToUserName><![CDATA[{$touser}]]></ToUserName>
<FromUserName><![CDATA[{$fromuser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
<Music>
<Title><![CDATA[{$title}]]></Title>
<Description><![CDATA[{$description}]]></Description>
<MusicUrl><![CDATA[{$music_url}]]></MusicUrl>
<HQMusicUrl><![CDATA[{$hq_music_url}]]></HQMusicUrl>
<ThumbMediaId><![CDATA[{$media_id}]]></ThumbMediaId>
</Music>
</xml>
str;
        exit($str);
    }

    /**
     * 回复图文信息
     * @param array $news 要求是一个二维数组,格式如下
     * $news=[
     *          [
     *           "title"=>"",
     *           "description"=>"",
     *           "picurl"=>"",
     *           "url"=>"",
     *          ],
     *      ];
     */
    public function responsenews($news){
        $fromuser = self::$xmlarray["ToUserName"];
        $touser = self::$xmlarray["FromUserName"];
        $time=time();
        $count=count($news);
        $str=<<<str
<xml>
<ToUserName><![CDATA[{$touser}]]></ToUserName>
<FromUserName><![CDATA[{$fromuser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>{$count}</ArticleCount>
<Articles>

str;
        foreach ($news as $v){
            $str .=<<<str
<item>
<Title><![CDATA[{$v['title']}]]></Title> 
<Description><![CDATA[{$v['description']}]]></Description>
<PicUrl><![CDATA[{$v['picurl']}]]></PicUrl>
<Url><![CDATA[{$v["url"]}]]></Url>
</item>

str;
        }
        $str .="</Articles>\r\n</xml>";
        exit($str);
    }

    /**
     * 获取一个有效的access_token
     * @return mixed
     */
    public function getaccesstoken(){
        $path = $this->config["accesstoken_path"];
        if(is_file($path)){
            $t=include $path;
            if($t["starttime"]+$t["expires_in"]<(time()+200)){//如果过期
                return $this->ask_access_token();
            }else{
                return $t["access_token"];
            }
        }else{
            return $this->ask_access_token();
        }
    }

    /**
     * 向微信服务器请求access_token
     * @return mixed
     */
    private function ask_access_token(){
        //请求地址
        $url = "https://api.weixin.qq.com/cgi-bin/token";
        //获取access_token填写client_credential
        $grant_type = 'client_credential';
        //第三方用户唯一凭证
        $appid = $this->config['appid'];
        //第三方用户唯一凭证密钥，即appsecret
        $secret = $this->config['appsecret'];
        //最终地址
        $url .= "?grant_type={$grant_type}&appid={$appid}&secret={$secret}";
        //请求
        $json = file_get_contents( $url );
        //把返回的json转为对象
        $arr = json_decode( $json, true );
        $arr["starttime"] = time();
        $code = "<?php return ".var_export($arr,true)."; ?>";
        file_put_contents($this->config["accesstoken_path"],$code);
        return $arr["access_token"];
    }

    /**
     * 创建菜单
     * @param string $menu
     * @return mixed
     */
    public function createMenu( $menu = '' )
    {
        $token = $this->getaccesstoken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $token;
        //初始化curl
        $curl = curl_init();
        //需要获取的URL地址，也可以在curl_init()函数中设置。
        curl_setopt($curl, CURLOPT_URL, $url);
        //如果数据不为空
        if (!empty($menu)) {

            //把数组转json,并且不编码(因为微信服务器需要的是json)
            $menu = json_encode($menu, JSON_UNESCAPED_UNICODE);
            //替换转义的/
            $menu = str_replace('\\/', '/', $menu);
            //发送一个post请求
            curl_setopt($curl, CURLOPT_POST, 1);
            //发送的post的数据
            curl_setopt($curl, CURLOPT_POSTFIELDS, $menu);
        }
        //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        //关闭curl
        curl_close($curl);

        return $output;
    }
}