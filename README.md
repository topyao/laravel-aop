Laravel-AOP 扩展包 [dev]

# 环境要求

```
php >=8.0
laravel 支持PHP8的版本
```

# 使用步骤

```php
composer require max/laravel-aop:dev-master
```

# 修改以下两个文件的代码

- public/index.php
- artisan

```php
$loader = require __DIR__ . '/../vendor/autoload.php';
$app    = require_once __DIR__ . '/../bootstrap/app.php';

\Max\LaravelAop\Scanner::init($loader, [app_path()], storage_path('runtime'));
```

# 使用

例如我有一个控制器`IndexController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Max\LaravelAop\Aspects\Cacheable;
use Max\LaravelAop\Aspects\Inject;

class IndexController
{
    #[Inject]
    protected Request $request;

    #[Cacheable]
    public function index()
    {
        echo 'Hello AOP.'; // 这里用echo可以测试执行顺序
    }
}

```

我使用了两个注解`Inject`和`Cacheable`, 其中`Cacheable`只用来测试，没有实际功能。使用`php artisan serve`
启动内置服务，访问一下来生成代理类，再访问一下，会走代理，输出内容为：`Before hello.Hello AOP.After hello`. 还可以使用`dump`打印下当前控制器的属性`$request`,发现已经被注入了。

> 注意：修改代码后要删除`storage/runtime`文件夹
