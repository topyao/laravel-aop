<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\LaravelAop;

use Composer\Autoload\ClassLoader;
use Max\LaravelAop\Contracts\AspectInterface;
use Max\Utils\Filesystem;
use PhpParser\Error;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerExceptionInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use Throwable;

final class Scanner
{
    /**
     * @var string
     */
    protected string $runtimeDir;

    /**
     * @var string
     */
    protected string $proxyMap;

    /**
     * @var Scanner
     */
    private static Scanner $scanner;

    private Parser $parser;

    /**
     * @param ClassLoader $loader
     * @param array       $scanDir    扫描路径
     * @param string      $runtimeDir 缓存路径
     */
    private function __construct(
        protected ClassLoader $loader,
        protected array       $scanDir,
        string                $runtimeDir)
    {
        $this->runtimeDir = $runtimeDir = rtrim($runtimeDir, '/\\') . '/aop/';
        is_dir($runtimeDir) || mkdir($runtimeDir, 0755, true);
        $this->proxyMap = $proxyMap = $runtimeDir . 'proxy.php';
        //        file_exists($proxyMap) && unlink($proxyMap);
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->loader->addClassMap($this->proxy());
    }

    /**
     * 根据绝对路径扫描完整类名[一个文件只能存放一个类，否则可能解析失败]
     *
     * @param string $dir
     *
     * @return array
     */
    public function scanDir(string $dir): array
    {
        $dir     = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        $classes = [];
        foreach ($dir as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $path = $file->getRealPath() ?: $file->getPathname();
            if ('php' !== pathinfo($path, PATHINFO_EXTENSION)) {
                continue;
            }
            $classes = array_merge($classes, $this->findClasses($path));
            gc_mem_caches();
        }
        return $classes;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function findClasses(string $path): array
    {
        try {
            $ast     = $this->parser->parse(file_get_contents($path));
            $classes = [];
            foreach ($ast as $stmt) {
                try {
                    if ($stmt instanceof Namespace_) {
                        $namespace = $stmt->name->toCodeString();
                        foreach ($stmt->stmts as $subStmt) {
                            if ($subStmt instanceof Class_) {
                                $classes[$namespace . '\\' . $subStmt->name->toString()] = $path;
                            }
                        }
                    }
                } catch (Error $error) {
                    echo $error->getMessage() . PHP_EOL;
                }
            }
            return $classes;
        } catch (Error $error) {
            echo $error->getMessage() . PHP_EOL;
            return [];
        }
    }

    /**
     * @param ClassLoader $loader
     * @param array       $scanDir
     * @param string      $runtimeDir
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws Exceptions\NotFoundException
     * @throws ReflectionException
     */
    public static function init(ClassLoader $loader, array $scanDir, string $runtimeDir): void
    {
        if (!isset(self::$scanner)) {
            self::$scanner = new Scanner($loader, $scanDir, $runtimeDir);
        }
    }

    /**
     * @return mixed|void
     * @throws ContainerExceptionInterface
     * @throws Exceptions\NotFoundException
     * @throws ReflectionException
     */
    protected function proxy()
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->proxyMap)) {
            $proxyDir = $this->runtimeDir . 'proxy/';
            $filesystem->makeDirectory($proxyDir, 0755, true, true);
            $filesystem->cleanDirectory($proxyDir);
            $classMap = $this->collect();
            $scanMap  = [];
            foreach ($classMap as $class => $path) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                $filesystem->put($proxyPath, $this->parse($class, $path));
                $scanMap[$class] = $proxyPath;
            }
            $filesystem->put($this->proxyMap, sprintf("<?php \nreturn %s;", var_export($scanMap, true)));
            return $scanMap;
        }
        return include $this->proxyMap;
    }

    /**
     * @param $class
     * @param $path
     *
     * @return string
     */
    protected function parse($class, $path): string
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast       = $parser->parse(file_get_contents($path));
            $traverser = new NodeTraverser();
            $metadata  = new Metadata($this->loader, $class);
            $traverser->addVisitor(new PropertyHandlerVisitor($metadata));
            $traverser->addVisitor(new ProxyHandlerVisitor($metadata));
            $modifiedStmts = $traverser->traverse($ast);
            $prettyPrinter = new Standard;
            return $prettyPrinter->prettyPrintFile($modifiedStmts);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return '';
        }
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    protected function collect(): array
    {
        $proxies = [];
        foreach ($this->scanDir as $dir) {
            foreach ($this->scanDir($dir) as $class => $path) {
                $reflectionClass = new \ReflectionClass($class);
                foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                    foreach ($reflectionMethod->getAttributes() as $attribute) {
                        try {
                            $instance = $attribute->newInstance();
                            if ($instance instanceof AspectInterface) {
                                $proxies[$class] = $path;
                                break;
                            }
                        } catch (Throwable) {
                            // 这个注解不能用，通常是类不存在
                        }
                    }
                }
            }
        }
        return $proxies;
    }
}
