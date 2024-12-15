<?php
/**
 * @desc index.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2024/12/15 10:17
 */
declare(strict_types=1);

// 引入命名空间
use tinywan\ioc\Container;

// 引入自动加载文件
require __DIR__ . '/vendor/autoload.php';

// 定义配置接口
interface ConfigInterface
{
}

// PHP配置类实现配置接口
class PHPConfig implements ConfigInterface
{
}

// YAML配置类实现配置接口
class YAMLConfig implements ConfigInterface
{
}

// 创建并返回相同的单例容器实例
$container = Container::instance();
if (Container::instance() !== $container) {
    throw new Exception();
}

// 使用接口命名空间绑定到容器
$container->bind(ConfigInterface::class, PHPConfig::class);
if ($container->get(ConfigInterface::class) !== PHPConfig::class) {
    throw new Exception();
}

// 覆盖接口绑定
$container->bind(ConfigInterface::class, YAMLConfig::class);
if ($container->get(ConfigInterface::class) !== YAMLConfig::class) {
    throw new Exception();
}

// 使用类命名空间绑定到容器
$container->bind(PHPConfig::class, YAMLConfig::class);
if ($container->get(PHPConfig::class) !== YAMLConfig::class) {
    throw new Exception();
}

// 绑定一个单例到容器
$config = new PHPConfig();
$container->singleton(PHPConfig::class, $config);
if ($container->get(PHPConfig::class) !== $config) {
    throw new Exception();
}

// 检查构造函数和方法注入参数
class App1
{
    public ConfigInterface $config;
    public ConfigInterface $methodConfig;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function handle(ConfigInterface $config)
    {
        $this->methodConfig = $config;
    }
}

// 检查构造函数参数
$container->bind(ConfigInterface::class, PHPConfig::class);

$app1 = $container->resolve(App1::class);
if (get_class($app1->config) !== PHPConfig::class) {
    throw new Exception();
}

// 检查方法参数
$container->resolveMethod($app1, 'handle');
if (get_class($app1->methodConfig) !== PHPConfig::class) {
    throw new Exception();
}

// 检查额外传递的参数
class App2
{
    public ConfigInterface $config;
    public string $arg1;
    public string $arg2;

    public function __construct(ConfigInterface $config, string $arg1, string $arg2)
    {
        $this->config = $config;
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}

$app2 = $container->resolve(App2::class, [
    'arg1' => 'value1',
    'arg2' => 'value2'
]);
if ($app2->arg1 !== 'value1') {
    throw new Exception();
}
if ($app2->arg2 !== 'value2') {
    throw new Exception();
}
