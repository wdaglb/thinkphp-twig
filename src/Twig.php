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
use think\Loader;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class Twig
{

    private $app;

    // 模板引擎参数
    protected $config = [
        // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写
        'auto_rule'   => 1,
        // 模板起始路径
        'view_path'   => '',
        // 模板文件后缀
        'view_suffix' => 'twig',
        // 资源文件目录
        'asset_path'  => '/static',
        // 模板文件名分隔符
        'view_depr'   => DIRECTORY_SEPARATOR,
        // 扩展库
        'extension'=>[]
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
     */
    public function fetch($template, $data = [])
    {
        $twig = $this->getTwigHandle(new FilesystemLoader($this->config['view_path']));
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }

        // 记录视图信息
        $this->app
            ->log('[ VIEW ] ' . $template . ' [ ' . var_export(array_keys($data), true) . ' ]');

        $twig->display($template, $data);
    }

    /**
     * 渲染模板内容
     * @access public
     * @param  string    $content 模板内容
     * @param  array     $data 模板变量
     */
    public function display($content, $data = [])
    {
        $loader = new ArrayLoader([
            'index'=>$content
        ]);

        $this->getTwigHandle($loader)->display('index', $data);
    }

    /**
     * 根据loader渲染模板
     * @param LoaderInterface $loader
     * @return TwigEnvironment
     */
    protected function getTwigHandle(LoaderInterface $loader)
    {
        $twig = new TwigEnvironment($loader, [
            'debug'=>$this->app->isDebug(),
            'cache'=>$this->app->getRuntimePath() . 'twig_compilation'
        ]);

        // 注册Request全局变量
        $twig->addGlobal('Request', $this->app->request);

        // 注册Config全局变量
        $twig->addGlobal('Config', $this->app->config);

        // 注册配置项全局变量
        if (!empty($this->config['global_vars'])) {
            foreach ($this->config['global_vars'] as $name=>$value) {
                $twig->addGlobal($name, $value);
            }
        }

        // 加载拓展库
        $twig->addExtension(new Extension($this->config));

        if (!empty($this->config['extension'])) {
            foreach ($this->config['extension'] as $ext) {
                $twig->addExtension(new $ext($this->config));
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
