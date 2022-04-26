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

    #[Cacheable(prefix: 'cv', ttl: 1000)]
    public function index()
    {
        dump($this->request);
        return 'Hello, AOP.';
    }
}

```
我使用了两个注解`Inject`和`Cacheable`, 使用`php artisan serve`
启动内置服务，访问一下来生成代理类，再访问一下，会走代理，输出内容为：`Before hello.（dump内容）After hello, Hello, AOP.`,
执行顺序是正确的。刷新页面会返回被缓存的内容，改函数返回值会被缓存1000秒。

> 当然，对于Scanner扫描范围中的类，都可以被切入，例如

```php
<?php

namespace App\Lib;

use Illuminate\Support\Collection;
use Max\LaravelAop\Aspects\Cacheable;

class Test
{
    #[Cacheable(ttl: 100)]
    public function getUsers()
    {
        var_dump(123);
        return Collection::make(['users']);
    }
}

```

我要在控制器中调用getUsers方法来获取用户列表，对于已经切入的类，无论是使用容器创建实例还是直接new，标注的属性都会被注入，标识的方法都会被切入

```php
<?php

namespace App\Http\Controllers;

use App\Lib\Test;

class IndexController
{
    public function index()
    {
        $user = (new Test())->getUsers();
        return $user;
    }
}

```


使用多个切面类会按照顺序执行。切面类实现方法参考`Cacheable`, 属性注入注解参考`Inject`，例如

```php
#[Cacheable]
#[ReteLimit]
public function index()
{
    echo 'Hello AOP.'; // 这里用echo可以测试执行顺序
}
```

> 注意：修改代码后要删除`storage/runtime`文件夹
