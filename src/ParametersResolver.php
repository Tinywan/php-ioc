<?php
/**
 * @desc ParametersResolver.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2024/12/15 10:08
 */
declare(strict_types=1);

namespace tinywan\ioc;

use Psr\Container\ContainerInterface;
use ReflectionParameter;

class ParametersResolver
{
    protected ContainerInterface $container;
    protected array $parameters;
    protected array $args = [];

    /**
     * 构造函数，初始化依赖容器、参数列表和额外参数。
     *
     * @param ContainerInterface $container 依赖注入容器，用于解析类实例。
     * @param array $parameters 参数列表，包含反射参数对象。
     * @param array $args 额外参数，用于覆盖默认参数值或注入值。
     */
    public function __construct(ContainerInterface $container, array $parameters, array $args = [])
    {
        $this->container = $container;
        $this->parameters = $parameters;
        $this->args = $args;
    }

    /**
     * 获取并解析所有参数值。
     *
     * @return array 包含解析后的参数值的数组。
     * @throws \ReflectionException
     */
    public function getArguments(): array
    {
        // 遍历参数列表
        return array_map(
            function (ReflectionParameter $param) {
                // 如果额外参数中存在该参数名称，则返回该值
                if (array_key_exists($param->getName(), $this->args)) {
                    return $this->args[$param->getName()];
                }
                // 如果参数是类类型，则解析并返回类实例；否则返回默认值
                return $param->getType() && !$param->getType()->isBuiltin()
                    ? $this->getClassInstance($param->getType()->getName())
                    : $param->getDefaultValue();
            },
            $this->parameters
        );
    }

    /**
     * 根据命名空间解析并返回类实例。
     *
     * @param string $namespace 类的命名空间。
     * @return object 类的实例。
     */
    protected function getClassInstance(string $namespace): object
    {
        return (new ClassResolver($this->container, $namespace))->getInstance();
    }
}
