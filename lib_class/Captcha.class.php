<?php
defined('IN_MY_PHP')||die(0);
/**
 * 一个字体大小不可随意可控的验证码生成类
 * @author netmou <leiyanfo@sina.com>
 */
class Captcha {
    public $width = 70; // 文件上传路径 结尾加斜杠
    public $height = 25; // 缩略图路径（必须在$images_dir下建立） 结尾加斜杠
    public $captcode=null;
    
    /**
    * +----------------------------------------------------------
    * 构造函数
    * +----------------------------------------------------------
    */
    function __construct ($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }

    /**
    * +----------------------------------------------------------
    * 图片上传的处理函数
    * +----------------------------------------------------------
    */
    function create() {
        $chars = "abcdefghkmnprstuvwxy3456789";
        $word = '';
        for($i = 0; $i < 4; $i++) {
            $word .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $this->captcode=$word;

        // 绘制基本框架
        $im = imagecreatetruecolor($this->width, $this->height);
        $bg_color = imagecolorallocate($im, 235, 236, 237);
        imagefilledrectangle($im, 0, 0, $this->width, $this->height, $bg_color);
        $border_color = imagecolorallocate($im, 118, 151, 199);
        imagerectangle($im, 0, 0, $this->width - 1, $this->height - 1, $border_color);

        // 添加干扰
        for($i = 0; $i < 5; $i++) {
            $rand_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($im, mt_rand(-$this->width, $this->width), mt_rand(-$this->height, $this->height), mt_rand(30, $this->width *
            2), mt_rand(20, $this->height * 2), mt_rand(0, 360), mt_rand(0, 360), $rand_color);
        }
        for($i = 0; $i < 50; $i++) {
            $rand_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, mt_rand(0, $this->width), mt_rand(0, $this->height), $rand_color);
        }

        // 生成验证码图片
        $text_color = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        imagestring($im, 6, 18, 5, $word, $text_color);

        header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
        header("Content-type: image/png;charset=utf-8");

        /* 绘图结束 */
        imagepng($im);
        imagedestroy($im);
        return true;
    }

}
?>