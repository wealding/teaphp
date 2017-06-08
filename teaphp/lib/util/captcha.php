<?php

class captcha
{
    public $consts = 'bcdfhjkmnpqrstwxyz';
    public $vowels = 'aei23456789';
    public $height = 24;
    public $length = 4;
    public $angle = 10;
    public $contort = 1;
    public $fonts;

    public function __construct()
    {
        global $tea;
        $this->fonts = TEA_PATH.'/lib/util/fonts/couri.ttf';
    }

    public function setconsts($consts)
    {
        $this->consts = $consts;
    }

    public function setvowels($height)
    {
        $this->vowels = $height;
    }

    public function setheight($vowels)
    {
        $this->height = $vowels;
    }

    public function setlength($length)
    {
        $this->length = $length;
    }

    public function setangle($angle)
    {
        $this->angle = $angle;
    }

    public function setcontort($contort)
    {
        $this->contort = $contort;
    }

    public function setfonts($fonts)
    {
        $this->fonts = $fonts;
    }

    public function string()
    {
        $string = $this->_string();

        return $string;
    }

    public function image()
    {
        $string = $this->_string();
        $this->_image($string);
    }

    public function valid($seccode, $destroy = true)
    {
        $result = (isset($_SESSION['seccode']) && $seccode === $_SESSION['seccode']) ? true : false;
        if ($destroy) {
            unset($_SESSION['seccode']);
        }

        return $result;
    }

    public function _string()
    {
        $constslen = strlen($this->consts) - 1;
        $vowelslen = strlen($this->vowels) - 1;
        $string = '';
        for ($x = 0; $x < $this->length; $x++) {
            $string .= $x % 2 == 0 ? substr($this->consts, mt_rand(0, $constslen), 1) : substr($this->vowels, mt_rand(0, $vowelslen), 1);
        }
        $_SESSION['seccode'] = $string;

        return $_SESSION['seccode'];
    }

    public function _image($string)
    {
        ob_clean();
        $imageX = strlen($string) * 13;    //the image width
        $imageY = $this->height;                        //the image height
        $im = imagecreatetruecolor($imageX, $imageY);

        //背景
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));

        //字体颜色
        $color_r = mt_rand(0, 120);
        $color_g = mt_rand(0, 120);
        $color_b = mt_rand(0, 120);
        $fontColor = imagecolorallocate($im, $color_r, $color_g, $color_b);

        //角度旋转写入
        for ($i = 0; $i < strlen($string); $i++) {
            $angle = mt_rand(-$this->angle, $this->angle);    //角度随机
            $fontsize = mt_rand(12, 16);    //字体大小随机
            imagefttext($im, $fontsize, $angle, 2 + $i * 11, 18, $fontColor, $this->fonts, $string[$i]);
        }

        //扭曲
        $dstim = imagecreatetruecolor($imageX, $imageY);
        imagefill($dstim, 0, 0, imagecolorallocate($dstim, 255, 255, 255));

        $this->contort = mt_rand(1, $this->contort);
        $funcs = ['sin', 'cos'];
        $func = $funcs[mt_rand(0, 1)];
        for ($j = 0; $j < $imageY; $j++) {
            $amend = round($func($j / $imageY * 2 * M_PI - M_PI * 0.5) * $this->contort);
            for ($i = 0; $i < $imageX; $i++) {
                $rgb = imagecolorat($im, $i, $j);
                imagesetpixel($dstim, $i + $amend, $j, $rgb);
            }
        }

        //边框
        $border = imagecolorallocate($dstim, 133, 153, 193);
        imagerectangle($dstim, 0, 0, $imageX - 1, $imageY - 1, $border);

        header("content-type:image/png\r\n");
        imagepng($dstim);
        imagedestroy($im);
        imagedestroy($dstim);
    }
}
