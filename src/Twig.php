<?php
// +----------------------------------------------------------------------
// | tp5.
// +----------------------------------------------------------------------
// | FileName: Template.php
// +----------------------------------------------------------------------
// | Author: King east <1207877378@qq.com>
// +----------------------------------------------------------------------


namespace ke;

use think\App;
use think\exception\TemplateNotFoundException;
use think\Loader;

class Twig
{

    private $app;

    // 模板引擎参数
    protected $config = [
        // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写
        'auto_rule'   => 1,
        // 视图基础目录（集中式）
        'view_base'   => '',
        // 模板起始路径
        'view_path'   => '',
        // 模板文件后缀
        'view_suffix' => 'html',
        // 模板文件名分隔符
        'view_depr'   => DIRECTORY_SEPARATOR,
        // 是否开启模板编译缓存,设为false则每次都会重新编译
        'tpl_cache'   => true,
		// 模板全局变量
		'tpl_replace_string'=>[],
    ];

    public function __construct(App $app, $config = [])
    {
        $this->app    = $app;
        $this->config = array_merge($this->config, (array) $config);

        if (empty($this->config['view_path'])) {
            $this->config['view_path'] = $app->getModulePath() . 'view' . DIRECTORY_SEPARATOR;
        }

    }


    /**
     * 检测是否存在模板文件
     * @access public
     * @param  string $template 模板文件或者模板规则
     * @return bool
     */
    public function exists($template)
    {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }
        return is_file($template);
    }

    /**
     * 渲染模板文件
     * @access public
     * @param  string    $template 模板文件
     * @param  array     $data 模板变量
     * @return string
     */
    public function fetch($template, $data = [])
    {
        $twig = $this->getTwigHandle(new \Twig_Loader_Filesystem($this->config['view_path']));
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }

        // 记录视图信息
        $this->app
            ->log('[ VIEW ] ' . $template . ' [ ' . var_export(array_keys($data), true) . ' ]');

        return $twig->render($template, $data);
    }

    /**
     * 渲染模板内容
     * @access public
     * @param  string    $content 模板内容
     * @param  array     $data 模板变量
     * @return string
     */
    public function display($content, $data = [])
    {
        $loader = new \Twig_Loader_Array([
            'index'=>$content
        ]);

        return $this->getTwigHandle($loader)->render('index', $data);
    }

    /**
     * 根据loader渲染模板
     * @param \Twig_LoaderInterface $loader
     * @return \Twig_Environment
     */
    protected function getTwigHandle(\Twig_LoaderInterface $loader)
    {
        $twig = new \Twig_Environment($loader, [
            'debug'=>$this->app->isDebug(),
            'cache'=>$this->app->getRuntimePath() . 'compilation'
        ]);
        // 添加url函数
        $function = new \Twig_Function('url', 'url');
        $twig->addFunction($function);

        // 添加Request全局变量
        $twig->addGlobal('Request', $this->app->request);

        foreach ($this->config['tpl_replace_string'] as $key=>$value) {
            $twig->addGlobal($key, $value);
        }

        // 加载拓展库
        if (!empty($config['taglib_extension'])) {
            foreach ($config['taglib_extension'] as $ext) {
                $twig->addExtension(new $ext());
            }
        }

        return $twig;
    }


    /**
     * 自动定位模板文件
     * @access private
     * @param  string $template 模板文件规则
     * @return string
     */
    private function parseTemplate($template)
    {
        // 分析模板文件规则
        $request = $this->app['request'];
        $depr = $this->config['view_depr'];

        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = Loader::parseName($request->controller());

            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $this->getActionTemplate($request);
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }

        return ltrim($template, '/') . '.' . ltrim($this->config['view_suffix'], '.');
    }

    protected function getActionTemplate($request)
    {
        $rule = [$request->action(true), Loader::parseName($request->action(true)), $request->action()];
        $type = $this->config['auto_rule'];

        return isset($rule[$type]) ? $rule[$type] : $rule[0];
    }

}
