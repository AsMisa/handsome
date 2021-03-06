<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Utils.php
 * Author     : Hran,hewro
 * Date       : 2017/05/29
 * Version    :
 * Description: handsome主题的一些工具方法
 */

class Utils {

    public static function formatDate($obj,$time, $format) {
        if (strtoupper($format) == 'NATURAL') {
            return self::naturalDate($time,"all");
        }else{
            //return self::naturalDate($time);//强制开启友好化格式化时间
            $obj->date($format);
        }
    }

    public static function naturalDate($from,$level) {
        $now = time();
        $between = time() - $from;
        if ($level != "short"){
            if ($between > 31536000) {
                return date(I18n::dateFormat(), $from);
            } else if ($between > 0 && $between < 172800                                // 如果是昨天
                && (date('z', $from) + 1 == date('z', $now)                             // 在同一年的情况
                    || date('z', $from) + 1 == date('L') + 365 + date('z', $now))) {    // 跨年的情况
                return _mt('昨天 %s', date('H:i', $from));
            }
        }
        $f = array(
            '31536000' => '%d 年前',
            '2592000' => '%d 个月前',
            '604800' => '%d 星期前',
            '86400' => '%d 天前',
            '3600' => '%d 小时前',
            '60' => '%d 分钟前',
            '1' => '%d 秒前',
        );
        if ($between == 0){
            return _mt("刚刚");
        }
        foreach ($f as $k => $v) {
            if (0 != $c = floor($between / (int)$k)) {
                if ($c == 1) {//一倍整除
                    return _mt(sprintf($v, $c));
                }
                return _mt($v, $c);//多倍整除
            }
        }
        return "";
    }

    public static function avatarHtml($obj){
        $email = $obj->mail;
        $avatorSrc = Utils::getAvator($email,65);

        return '<img nogallery src="'.$avatorSrc.'" class="avatar-40 photo img-circle" style="height:40px!important; width: 40px!important;">';


    }

    public static function getAvator($email,$size){
        $options = mget();
        $cdnUrl = $options->CDNURL;
        if (@ in_array('emailToQQ',$options->featuresetup)){
            $str = explode('@', $email);
            if (@$str[1] == 'qq.com' && @ctype_digit($str[0]) && @strlen($str[0]) >=5
                && @strlen($str[0])<=11) {
                $avatorSrc = 'https://q.qlogo.cn/g?b=qq&nk='.$str[0].'&s=100';
            }else{
                $avatorSrc = Utils::getGravator($email,$cdnUrl,$size);
            }
        }else{
            $avatorSrc = Utils::getGravator($email,$cdnUrl,$size);
        }
        return $avatorSrc;
    }

    public static function getGravator($email,$host,$size){
        $options = mget();
        $default = '';
        if (strlen($options->defaultAvator) > 0){
            $default = $options->defaultAvator;
        }
        $url = '/';//自定义头像目录,一般保持默认即可
        //$size = '40';//自定义头像大小
        $rating = Helper::options()->commentsAvatarRating;
        $hash = md5(strtolower($email));
        $avatar = $host . $url . $hash . '?s=' . $size . '&r=' . $rating . '&d=' . $default;
        return $avatar;
    }

    public static function sticky($archive){
        $db = Typecho_Db::get();
        $paded = $archive->request->get('page', 1);
        $sticky_post = $db->fetchRow($archive->select()->where('cid = ?', 10));
        $archive->push($sticky_post);
        //$select->where('table.contents.cid != ?', 10);

    }

    public static function loginAction($archive){
        $requestURL = $archive->request->getRequestUrl();

        $requestURL = str_replace('&_pjax=%23content', '', $requestURL);
        $requestURL = str_replace('?_pjax=%23content', '', $requestURL);
        $requestURL = str_replace('_pjax=%23content', '', $requestURL);

        return $archive->widget('Widget_Security')->getTokenUrl($archive->rootUrl.'/index.php/action/login');
    }

    /**
     * @param $url
     * @return array 返回是的歌单解析信息的数组
     */
    public static function parseMusicUrl($url){
        $result = array();
        //如果为空，返回一个默认正确的URL
        $url=trim($url);
        if(empty($url))
            $url = 'http://music.163.com/#/my/m/music/playlist?id=883542351';

        $media='netease';$id='883542351';$type='playlist';
        if(strpos($url,'163.com')!==false){
            $media='netease';
            if(preg_match('/playlist\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/toplist\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/album\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'album');
            elseif(preg_match('/song\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'song');
            elseif(preg_match('/artist\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
        }
        elseif(strpos($url,'qq.com')!==false){
            $media='tencent';
            if(preg_match('/playlist\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/album\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'album');
            elseif(preg_match('/song\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'song');
            elseif(preg_match('/singer\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'artist');
        }
        elseif(strpos($url,'xiami.com')!==false){
            $media='xiami';
            if(preg_match('/collect\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/album\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'album');
            elseif(preg_match('/[\/.]\w+\/[songdem]+\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'song');
            elseif(preg_match('/artist\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
            if(!preg_match('/^\d*$/i',$id,$t)){
                $data=curl($url);
                preg_match('/'.$type.'\/(\d+)/i',$data,$id);
                $id=$id[1];
            }
        }
        elseif(strpos($url,'kugou.com')!==false){
            $media='kugou';
            if(preg_match('/special\/single\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/#hash\=(\w+)/i',$url,$id))list($id,$type)=array($id[1],'song');
            elseif(preg_match('/album\/[single\/]*(\d+)/i',$url,$id))list($id,$type)=array($id[1],'album');
            elseif(preg_match('/singer\/[home\/]*(\d+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
        }
        elseif(strpos($url,'baidu.com')!==false){
            $media='baidu';
            if(preg_match('/songlist\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/album\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'album');
            elseif(preg_match('/song\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'song');
            elseif(preg_match('/artist\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
        }
        else{//输入的地址不能匹配到上述的第三方音乐平台
            $result = array(
                'title' =>  '歌曲名',
                'author' => '歌手',
                'url' => $url
            );
            return $result;
        }
        $result = array(
            'media' =>  $media,
            'id' => $id,
            'type' => $type
        );
        return $result;
    }

    public static function getImageNumRandomArray($NeedSize,$imageNum){
        $indexNumberArray = array();
        if ($NeedSize > $imageNum){
            //这种情况下图片重复是不可避免的，原因是缩略图的数目不够
            /*for($i = 0;$i<$options->pageSize - $options->RandomPicAmnt ;$i++){
                $indexNumberArray[] = random_int(1, $options->RandomPicAmnt);
            }*/
            while (count($indexNumberArray) < $NeedSize){
                $number = rand(1, $imageNum);
                $indexNumberArray[] = $number;
            }
        }else{
            while (count($indexNumberArray) < $NeedSize){
                $number = rand(1, $imageNum);
                $flag = false;//当前生成的数字是否已经存在了
                foreach ($indexNumberArray as $value){
                    if ($value == $number){
                        $flag = true;
                        break;
                    }
                }
                if (!$flag){
                    $indexNumberArray[] = $number;
                }
            }
        }
        //print_r($indexNumberArray);
        return $indexNumberArray;
    }

    /**
     * 16进制颜色转rgb颜色
     * @param $hexColor
     * @return string
     */
    public static function hex2rgb($hexColor) {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {

            $rgb = hexdec(substr($color, 0, 2)) . "," . hexdec(substr($color, 2, 2)). "," . hexdec(substr($color, 4, 2));
        } else {
            $color = $hexColor;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = hexdec($r) . "," . hexdec($g) . "," . hexdec($b);
        }
        return $rgb;
    }


    /**
     * 替换默认的preg_replace_callback函数
     * @param $pattern
     * @param $callback
     * @param $subject
     * @return string
     */
    public static function handle_preg_replace_callback($pattern, $callback, $subject){
        return self::handleHtml($subject,function ($content) use ($callback, $pattern) {
            return preg_replace_callback($pattern,$callback, $content);
        });
    }


    public static function handle_preg_replace($pattern, $replacement, $subject){
        return self::handleHtml($subject,function ($content) use ($replacement, $pattern) {
            return preg_replace($pattern,$replacement, $content);
        });
    }

    /**
     * 处理 HTML 文本，确保不会解析代码块中的内容
     * @param $content
     * @param callable $callback
     * @return string
     */
    public static function handleHtml($content, $callback) {
        $replaceStartIndex = array();
        $replaceEndIndex = array();
        $currentReplaceId = 0;
        $replaceIndex = 0;
        $searchIndex = 0;
        $searchCloseTag = false;
        $contentLength = strlen($content);
        while (true) {
            if ($searchCloseTag) {
                $tagName = substr($content, $searchIndex, 4);
                if ($tagName == "<cod") {
                    $searchIndex = strpos($content, '</code>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 7;
                } elseif ($tagName == "<pre") {
                    $searchIndex = strpos($content, '</pre>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 6;
                } elseif ($tagName == "<kbd") {
                    $searchIndex = strpos($content, '</kbd>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 6;
                } elseif ($tagName == "<scr") {
                    $searchIndex = strpos($content, '</script>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 9;
                } elseif ($tagName == "<sty") {
                    $searchIndex = strpos($content, '</style>', $searchIndex);
                    if (!$searchIndex) {
                        break;
                    }
                    $searchIndex += 8;
                } else {
                    break;
                }


                if (!$searchIndex) {
                    break;
                }
                $replaceIndex = $searchIndex;
                $searchCloseTag = false;
                continue;
            } else {
                $searchCodeIndex = strpos($content, '<code', $searchIndex);
                $searchPreIndex = strpos($content, '<pre', $searchIndex);
                $searchKbdIndex = strpos($content, '<kbd', $searchIndex);
                $searchScriptIndex = strpos($content, '<script', $searchIndex);
                $searchStyleIndex = strpos($content, '<style', $searchIndex);
                if (!$searchCodeIndex) {
                    $searchCodeIndex = $contentLength;
                }
                if (!$searchPreIndex) {
                    $searchPreIndex = $contentLength;
                }
                if (!$searchKbdIndex) {
                    $searchKbdIndex = $contentLength;
                }
                if (!$searchScriptIndex) {
                    $searchScriptIndex = $contentLength;
                }
                if (!$searchStyleIndex) {
                    $searchStyleIndex = $contentLength;
                }
                $searchIndex = min($searchCodeIndex, $searchPreIndex, $searchKbdIndex, $searchScriptIndex, $searchStyleIndex);
                $searchCloseTag = true;
            }
            $replaceStartIndex[$currentReplaceId] = $replaceIndex;
            $replaceEndIndex[$currentReplaceId] = $searchIndex;
            $currentReplaceId++;
            $replaceIndex = $searchIndex;
        }

        $output = "";
        $output .= substr($content, 0, $replaceStartIndex[0]);
        for ($i = 0; $i < count($replaceStartIndex); $i++) {
            $part = substr($content, $replaceStartIndex[$i], $replaceEndIndex[$i] - $replaceStartIndex[$i]);
            if (is_array($callback)) {
                $className = $callback[0];
                $method = $callback[1];
                $renderedPart = call_user_func($className.'::'.$method, $part);
            } else {
                $renderedPart = $callback($part);
            }
            $output.= $renderedPart;
            if ($i < count($replaceStartIndex) - 1) {
                $output.= substr($content, $replaceEndIndex[$i], $replaceStartIndex[$i + 1] - $replaceEndIndex[$i]);
            }
        }
        $output .= substr($content, $replaceEndIndex[count($replaceStartIndex) - 1]);
        return $output;
    }


    public static function returnImageSrcWithSuffix($imageSrc = null,$cdnType = null, $width = 0, $height = 0){
        $isLocal = true;
        $options = mget();
        if ($options->cdn_add == ""){
            return $imageSrc;
        }
        if($imageSrc!=null && strpos($imageSrc,$options->rootUrl) === false){//不是本地服务器图片
            $isLocal = false;
        }else{//替换图片的域名地址为云存储空间地址
            $cdnArray = explode("|",$options->cdn_add);
            $imageSrc = str_ireplace($options->rootUrl,trim($cdnArray[0]),$imageSrc);
            $cdnType = trim($cdnArray[1]);
        }
        return $imageSrc . self::getImageAddOn($options,$isLocal,$cdnType,$width,$height);
    }

    /**
     * 云存储选项
     * @param array $options 后台选项设置
     * @param bool $isLocal 是否是本地服务器图片
     * @param String $cdnType 云服务商类型
     * @param int $width 目标图片的宽度
     * @param int $height 目标图片的高度
     * @return string
     */
    public static function getImageAddOn($options,$isLocal = false,$cdnType = null, $width = 0, $height = 0){
        $addOn = "";//图片后缀
        if (!$isLocal){//不是本地服务器图片
            return $addOn;
        }
        if ($options->cdn_add!="" && (@in_array('1',$options->cloudOptions) || @in_array('0',$options->cloudOptions))){//开启了镜像存储的功能

            if ($cdnType == null){//如果参数中没有cdnType，这里会进行获取cdn类型
                $cdnArray = explode("|",$options->cdn_add);
                $cdnType = trim($cdnArray[1]);
            }

            if (@in_array('1',$options->cloudOptions)){//启用了webp格式
                if ($cdnType == "ALIOSS" || $cdnType == "UPYUN"){//阿里云和又拍云
                    $addOn .= "!";//分隔符
                }else{//七牛云
                    $addOn .= "?";//分隔符
                }
                if ($cdnType == "ALIOSS"){//阿里云
                    $format = "/format,webp";
                }else{//又拍云和七牛云
                    $format = "/format/webp";
                }
                $addOn .= $format;
            }
            if (!($width == 0 && $height == 0) && @in_array('0',$options->cloudOptions)){//启用了图片缩放 TODO:优化这个缩放
                if (trim($addOn) == ""){
                    if ($cdnType == "ALIOSS" || $cdnType == "UPYUN"){//阿里云和又拍云
                        $addOn .= "!";//分隔符
                    }else{//七牛云
                        $addOn .= "?";//分隔符
                    }
                }
                if ($height == 0){//根据宽度尺寸进行缩放
                    if ($cdnType == "UPYUN"){
                        $addOn .= "/fw/$width";
                    }else if ($cdnType == "ALIOSS"){
                        $addOn .= "/x-oss-process=image/resize,w_$width";
                    }else{//七牛云
                        $addOn .=  "/imageView2/2/w/$width";
                    }
                }else if ($width === 0){//根据高度尺寸进行缩放
                    if ($cdnType == "UPYUN"){
                        $addOn .= "/fh/$height";
                    }else if ($cdnType == "ALIOSS"){
                        $addOn .= "/x-oss-process=image/resize,h_$height";
                    }else{//七牛云
                        $addOn .=  "/imageView2/2/h/$height";
                    }
                }else{
                    if ($cdnType == "UPYUN"){
                        $addOn .= "/fwfh/".$width."x".$height;
                    }else if ($cdnType == "ALIOSS"){
                        $addOn .= "/x-oss-process=image/resize,m_lfit,h_".$height.",w_".$width;
                    }else{//七牛云
                        $addOn .=  "/imageView2/2/w/".$width."/h/".$height;
                    }
                }
            }
        }
        return $addOn;
    }



    public static function returnDivLazyLoadHtml($originalSrc,$width,$height){
        $options = mget();
        $placeholder = Utils::choosePlaceholder($options);
        $lazyLoadHtml = "";

        $originalSrc = self::returnImageSrcWithSuffix($originalSrc,null,$width,$height);

        if (in_array('lazyload',$options->featuresetup)){
            $imageSrc = $placeholder;
            $lazyLoadHtml = 'data-original="'.$originalSrc.'"';
        }else{
            $imageSrc = $originalSrc;
        }



        return $lazyLoadHtml. ' style="background-image: url('.$imageSrc.')"';
    }

    public static function returnImageLazyLoadHtml($originalSrc,$width,$height){
        $options = mget();
        $placeholder = Utils::choosePlaceholder($options);

        $originalSrc .= self::getImageAddOn($options,$originalSrc,null,$width,$height);

        if (in_array('lazyload',$options->featuresetup)){
            $imageSrc = $placeholder;
            $lazyLoadHtml = 'data-original="'.$originalSrc.'"';
        }else{
            $lazyLoadHtml = "";
            $imageSrc = $originalSrc;
        }

        return $lazyLoadHtml. ' src="'.$imageSrc.'"';
    }

    public static function choosePlaceholder($options){
        if (@in_array("opacityMode",$options->indexsetup)){//透明模式
            return Handsome_Config::OPACITY_PLACEHOLDER;
        }else{//普通占位符
            return Handsome_Config::NORMAL_PLACEHOLDER;

        }
    }

    public static function  getSj1ImageNum(){
        try{
            $basedir = dirname(dirname(__FILE__))."/usr/img/sj";
            $arr = scandir($basedir);
            $image = count(preg_grep("/\.jpg$/", $arr));
            return $image;
        }catch (Exception $e){
            print_r($e);
            return 5;
        }
    }

    /**
     * 获取右侧边栏的图片数目
     * @return int
     */
    public static function  getSj2ImageNum(){
        try{
            $basedir = dirname(dirname(__FILE__))."/usr/img/sj2";
            $arr = scandir($basedir);
            $image = count(preg_grep("/\.jpg$/", $arr));
            return $image;
        }catch (Exception $e){
            print_r($e);
            return 5;
        }
    }

    /**
     * 返回运行时间
     */
    public static function getOpenDays(){
        $options = mget();
        $oldtime = $options->startTime;
        try{
            $catime = strtotime($oldtime);
            $now = time();
            $difference = $now - $catime;
            $year = floor($difference/31536000);
            if ($year >=1){
                $difference = $difference - $year * 31536000;
                $day = floor($difference/86400);
                return sprintf(_mt("%d年%d天"),$year,$day);
            }else{//小于一年
                $day = floor($difference/86400);
                return sprintf(_mt("%d天"),$day);
            }
        }catch (Exception $exception){
            return "null";
        }

    }

    /**
     * 返回最后更新时间
     */
    public static function getLatestTime($obj){
        $recent = $obj->widget('Widget_Contents_Post_Recent','pageSize=1');
        if($recent->have()){
            while($recent->next()){
                return Utils::naturalDate($recent->modified,"short");
            }
        }

    }

    public static function hEcho($text){
        if (strtoupper(Handsome_Config::HANDSOME_DEBUG_DISPLAY) == 'ON'){
            echo $text;
        }
    }

    public static function print_r($text){
        if (strtoupper(Handsome_Config::HANDSOME_DEBUG_DISPLAY) == 'ON'){
            print_r ($text);
        }
    }

    public static function var_dump($text){
        if (strtoupper(Handsome_Config::HANDSOME_DEBUG_DISPLAY) == 'ON'){
            var_dump ($text);
        }
    }

    public static function returnDefaultIfEmpty($target, $default){
        if (trim($target) == ""){
            return $default;
        }else{
            return $target;
        }
    }

    public static function getWordsOfContentPost($content){
        return mb_strlen(trim($content));
    }

}