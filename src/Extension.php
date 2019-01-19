<?php
// +----------------------------------------------------------------------
// | thinkphp-twig.
// +----------------------------------------------------------------------
// | FileName: Extension.php
// +----------------------------------------------------------------------
// | Author: King east <1207877378@qq.com>
// +----------------------------------------------------------------------


namespace ke;


class Extension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            /**
             * url生成
             */
            new \Twig_Function('url', 'url'),
            /**
             * 加载资源文件
             */
            new \Twig_Function('load', function ($str) {
                $load = function ($str) {
                    $ext = pathinfo($str, PATHINFO_EXTENSION);
                    if ($ext === 'js') {
                        return "<script src=\"{$str}\"></script>";
                    } elseif ($ext === 'css') {
                        return '<link rel="stylesheet" href="' . $str . '">';
                    }
                    return $str;
                };
                if (strpos($str, ',') === false) {
                    return $load($str);
                } else {
                    $e = '';
                    $tmp = explode(',', $str);
                    foreach ($tmp as $s) {
                        $e .= $load($s);
                    }
                    return $e;
                }
            })
        ];
    }

}