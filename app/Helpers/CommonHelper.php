<?php

if (!function_exists('mm')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function mm()
    {
        array_map(function ($x) {
            (new \Illuminate\Support\Debug\Dumper)->dump($x);
        }, func_get_args());
    }
}

if (!function_exists('is_empty')) {
    function is_empty($obj)
    {
        if (empty($obj)) {
            return true;
        }

        if ($obj instanceof Illuminate\Support\Collection) {
            $items = $obj->all();
            return empty($items);
        }
        return false;
    }
}

/**
 * 生成图片缩略图
 * @param $srcpath 原图片完整路径（包括文件名）
 * @param $topath 缩略图完整路径（包括文件名）
 * @param $maxwidth 最大宽度
 * @param $maxheight 最大高度
 * 原图宽高小于最大宽高时，不进行缩略图生成，直接copy原图。
 */
function createThumb($srcpath, $topath, $maxwidth, $maxheight)
{
    if (!file_exists($srcpath)) {
        return false;
    }
    $data = getimagesize($srcpath);
    $img = null;
    switch ($data[2]) {
        case 1:
            if (function_exists("imagecreatefromgif")) {
                $img = imagecreatefromgif($srcpath);
            }
            break;
        case 2:
            if (function_exists("imagecreatefromjpeg")) {
                $img = imagecreatefromjpeg($srcpath);
            }
            break;
        case 3:
            if (function_exists("imagecreatefrompng")) {
                $img = imagecreatefrompng($srcpath);
            }
            break;
    }
    if (!$img) {
        return false;
    }
    $srcw = imagesx($img);
    $srch = imagesy($img);
    if (($maxwidth > 0 && $srcw > $maxwidth) || ($maxheight > 0 && $srch > $maxheight)) {
        $towidth = $srcw;
        $toheight = $srch;
        if ($maxwidth > 0 && ($maxheight == 0 || $srcw / $srch >= $maxwidth / $maxheight)) {
            $towidth = $maxwidth;
            $toheight = $maxwidth * $srch / $srcw;
        } elseif ($maxheight > 0 && ($maxwidth == 0 || $srcw / $srch <= $maxwidth / $maxheight)) {
            $toheight = $maxheight;
            $towidth = $maxheight * $srcw / $srch;
        }
        if (function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && @$imgthumb = imagecreatetruecolor($towidth,
                $toheight)
        ) {
            imagecopyresampled($imgthumb, $img, 0, 0, 0, 0, $towidth, $toheight, $srcw, $srch);
        } elseif (function_exists("imagecreate") && function_exists("imagecopyresized") && @$imgthumb = imagecreate($towidth,
                $toheight)
        ) {
            imagecopyresized($imgthumb, $img, 0, 0, 0, 0, $towidth, $toheight, $srcw, $srch);
        } else {
            return false;
        }
        if (function_exists('imagejpeg')) {
            imagejpeg($imgthumb, $topath, 50);
        } elseif (function_exists('imagepng')) {
            imagepng($imgthumb, $topath);
        }
    } else {
        copy($srcpath, $topath);
    }
    imagedestroy($img);
    if (!file_exists($topath)) {
        return false;
    }
    return true;
}

/**
 * 递归显示当前指定目录下所有文件
 * 使用dir函数
 * @param string $dir 目录地址
 * @param boolean $recursion 是否递归
 * @return array $files 文件列表
 */
function getFiles($dir, $recursion = false)
{
    $files = array();

    if (!is_dir($dir)) {
        return $files;
    }

    $d = dir($dir);
    while (false !== ($file = $d->read())) {
        if ($file != '.' && $file != '..') {
            $filename = $dir . "/" . $file;

            if (is_file($filename)) {
                $files[] = $filename;
            } else {
                if ($recursion) {
                    $files = array_merge($files, getFiles($filename, $recursion));
                }
            }
        }
    }
    $d->close();
    return $files;
}

/**
 * 如果路径不存在，自动创建
 * @param $filePath
 * User: zzp
 * Date: 2017-03-13
 * @return bool
 */
function autoMakeDir($filePath)
{
    if (!file_exists($filePath)) {
        mkdir($filePath, 0777, true);
    }
}

/**
 * 获取IP地址
 * 由于服务器nginx代理的缘故 先通过 header 拿到真实ip
 * User: Howard
 * Date: 2017-05-23
 * @return string
 */
function getClientIp()
{
    if (isset($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return request()->getClientIp();

}

function saveAvatar($avatar)
{
    if (!empty($avatar) && (strpos($avatar, 'http://') === 0 || strpos($avatar, 'https://') === 0)) {
        $avatarDis = dirname(APP_ROOT) . config('common.picture_path.user_avatar_path');
        autoMakeDir($avatarDis);
        $curl = new Curl\Curl();
        if ($curl->download($avatar, $avatarDis . md5($avatar) . '.jpg')) {
            $avatar = md5($avatar) . '.jpg';
        }
        return $avatar;
    }
    return $avatar;
}
