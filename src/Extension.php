<?php
// +----------------------------------------------------------------------
// | thinkphp-twig.
// +----------------------------------------------------------------------
// | FileName: Extension.php
// +----------------------------------------------------------------------
// | Author: King east <1207877378@qq.com>
// +----------------------------------------------------------------------


namespace ke;


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    protected $config = [];


    public function __construct($config)
    {
        $this->config = $config;
    }


    public function getFunctions()
    {
        return [
            /**
             * url生成
             */
            new TwigFunction('url', 'url'),
            /**
             * 加载资源文件
             */
            new TwigFunction('load', function ($type, $str) {
                $load = function ($str) use($type) {
                    $str = $this->config['asset_path'] . $str;
                    if ($type === 'js') {
                        echo "<script src=\"{$str}\"></script>";
                        return;
                    } elseif ($type === 'css') {
                        echo '<link rel="stylesheet" href="' . $str . '">';
                        return;
                    }
                    echo $str;
                };
                if (strpos($str, ',') === false) {
                    echo $load($str);
                } else {
                    $e = '';
                    $tmp = explode(',', $str);
                    foreach ($tmp as $s) {
                        $e .= $load($s);
                    }
                    echo $e;
                }
            })
        ];
    }

}