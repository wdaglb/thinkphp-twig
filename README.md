**安装**
```$xslt
composer require ke/thinkphp-twig
```
**使用**
> 需要在template.php里把type节点修改为\\ke\\Twig，如：
```$xslt
'type'         => \ke\Twig::class,
'view_suffix'  => 'twig',
```
_PS:官方的tpl_begin,tpl_end,taglib_begin,taglib_end配置节点是无效的_

**注册全局变量**

```
    'global_vars'=>[
        '__STATIC__'=>'/static'
    ]
```


**注册拓展库**

> twig不支持直接使用php的函数,但是可以经过拓展定义使用

```
// 在template.php的taglib_extension里传入类名，如（必须是数组）：
    'taglib_extension'=>[
        \taglib\Lib::class
    ]
```


> 生成url
```$xslt
{{ url('index') }}
```

> 使用Request对象
```$xslt
// 读取$_GET['page']
{{ Request.get.page }}
```

> 使用Config对象
```
// 判断当前是否调试
{{ Config.app_debug }}
```

> if判断
```
{% if condition %}
真
{% else %}
假
{% endif %}

// 判断变量是否定义
{% if item is defined %}
变量已定义
{% endif %}

// 判断变量是否为空
{% if item is null %}
变量为空
{% endif %}

```

> 循环一个数组
```
{% for item in array %}
    {{ item.name }}
{% endfor %}


{% for index, item in array %}
    {{ index }}:
    {{ item.name }}
{% endfor %}

{% for item in array %}
    * {{ item.name }}
{% else %}
    No array have been found.
{% endfor %}


```

> 指定次数循环
```
{% for num in 1..20 %}
    {{ num }}
{% endfor %}
```

> 注释
```
{# 注释内容 #}
```