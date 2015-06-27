<?php

namespace Extend\Image;

class Captcha
{
    /**
     * 输出验证码
     * @param int 长度
     * @param int 宽度
     */
    public static function captcha($width=130, $height=50)
    {
        // 随机码
        $randomCode = substr(str_shuffle("abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789"), 0, 4);
        // 画布
        $image = imagecreatetruecolor($width, $height);
        // 颜色
        $color = imagecolorallocate($image, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
        // 填充颜色
      		imagefilledrectangle($image, 0, $height, $width, 0, $color);
      		// 填充文字
      		$position = $width/4;
      		$font = FONT.'BENNUDISE.ttf';
      		$fontsize = $height*0.5;
      		for($i=0; $i<4; $i++)
      		{
      		    $color = imagecolorallocate($image, mt_rand(0,156), mt_rand(0,156), mt_rand(0,156));
      		    imagettftext($image, $fontsize, mt_rand(-30,30), $position*$i+mt_rand(1,5), $height/1.4, $color , $font, $randomCode[$i]);
    }
    $_SESSION['captcha'] = $randomCode;
    //线条
    for($i=0; $i<6; $i++)
    {
    $color = imagecolorallocate($image, mt_rand(0,156), mt_rand(0,156), mt_rand(0,156));
    imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $color);
    }
    //雪花
    $number = $height*0.8;
    for($i=0; $i<$number; $i++)
    {
    $color = imagecolorallocate($image, mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
    imagestring($image, mt_rand(1,5), mt_rand(0,$width), mt_rand(0,$height), '*', $color);
    }
    // 输出
    header("Content-type: image/png");
    imagepng($image);
    // 销毁
		imagedestroy($image);
    }
}