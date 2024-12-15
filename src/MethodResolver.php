<?php
/**
 * @desc MethodResolver.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2024/12/15 10:08
 */
declare(strict_types=1);

namespace tinywan\ioc;

use Psr\Container\ContainerInterface;
use ReflectionMethod;

/**
 * MethodResolver 类负责解析并执行给定对象实例上的方法。
 * 它使用依赖注入来解析方法参数。
 */
class MethodResolver
{
    protected ContainerInterface $container;
    protected object $instance;
    protected string $method;
    protected array $args = [];

    /**
     * 构造一个 MethodResolver 实例。
     *
     * @param ContainerInterface $container 依赖注入容器接口，用于解析方法依赖。
     * @param object $instance 要在其上执行方法的对象实例。
     * @param string $method 要执行的方法名称。
     * @param array $args 传递给方法的附加参数，默认为空数组。
     */
    public function __construct(ContainerInterface $container, object $instance, string $method, array $args = [])
    {
        $this->container = $container;
        $this->instance = $instance;
        $this->method = $method;
        $this->args = $args;
    }

    /**
     * 执行指定的方法并返回结果。
     *
     * 此方法首先创建一个 ReflectionMethod 实例以反映要执行的方法，
     * 然后使用 ParametersResolver 类来解析方法所需的参数。
     * 最后，它使用解析出的参数调用方法并返回执行结果。
     *
     * @return mixed 执行方法的返回值。
     * @throws \ReflectionException
     */
    public function getValue()
    {
        // 获取类方法的反射类
        $method = new ReflectionMethod(
            $this->instance,
            $this->method
        );
        // 查找并解析方法参数
        $argumentResolver = new ParametersResolver(
            $this->container,
            $method->getParameters(),
            $this->args
        );
        // 使用注入的参数调用方法
        return $method->invokeArgs(
            $this->instance,
            $argumentResolver->getArguments()
        );
    }
}
