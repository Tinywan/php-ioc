<?php
/**
 * @desc ClassResolver.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2024/12/15 10:06
 */
declare(strict_types=1);

namespace tinywan\ioc;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;

/**
 * ClassResolver 负责解析并创建类实例。
 * 它使用依赖注入容器和命名空间来实例化类。
 */
class ClassResolver
{
    protected ContainerInterface $container;
    protected string $namespace;
    protected array $args = [];

    /**
     * ClassResolver 构造函数。
     *
     * @param ContainerInterface $container 依赖注入容器接口，用于检索绑定的实例或命名空间。
     * @param string $namespace 要解析的类的命名空间。
     * @param array $args 构造函数参数，默认为空数组。
     */
    public function __construct(ContainerInterface $container, string $namespace, array $args = [])
    {
        $this->container = $container;
        $this->namespace = $namespace;
        $this->args = $args;
    }

    /**
     * 获取解析的类实例。
     *
     * 该方法首先检查容器中是否有当前命名空间的条目，
     * 如果有，则尝试从容器中获取实例；如果容器条目不是实例，
     * 则将命名空间更新为容器绑定的命名空间。
     * 接下来，它尝试使用 ReflectionClass 创建类的实例，
     * 如果类构造函数存在且是公共的，它会解析构造函数参数并创建实例；
     * 如果没有构造函数或构造函数没有参数，则直接创建实例而不调用构造函数。
     *
     * @return object 返回创建的类实例。
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function getInstance(): object
    {
        // 检查容器中是否有当前命名空间的条目
        if ($this->container->has($this->namespace)) {
            $binding = $this->container->get($this->namespace);

            // 如果容器中有实例或单例，则直接返回
            if (is_object($binding)) {
                return $binding;
            }
            // 将命名空间设置为容器绑定的命名空间
            $this->namespace = $binding;
        }
        // 创建反射类
        $refClass = new ReflectionClass($this->namespace);

        // 获取构造函数
        $constructor = $refClass->getConstructor();

        // 检查构造函数是否存在且可访问
        if ($constructor && $constructor->isPublic()) {
            // 检查构造函数是否有参数并解析它们
            if (count($constructor->getParameters()) > 0) {
                $argumentResolver = new ParametersResolver(
                    $this->container,
                    $constructor->getParameters(),
                    $this->args
                );
                // 解析构造函数参数
                $this->args = $argumentResolver->getArguments();
            }
            // 使用构造函数参数创建新实例
            return $refClass->newInstanceArgs($this->args);
        }
        // 没有参数，直接创建新实例而不调用构造函数
        return $refClass->newInstanceWithoutConstructor();
    }
}
