<?php
/**
 * @desc Container.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2024/12/15 9:48
 */
declare(strict_types=1);

namespace tinywan\ioc;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Container类是一个简单的依赖注入容器
 * 它允许绑定服务标识到特定的类或实例，并提供方法来获取和解析这些服务
 * 通过实现ContainerInterface接口，它确保了容器的基本功能
 */
class Container implements ContainerInterface
{
    // 存储绑定的服务标识和对应的命名空间或实例
    protected array $bindings = [];

    /**
     * 获取Container的单例实例
     * 这个方法确保了一个容器实例的全局访问点
     * @return  Container
     */
    public static function instance(): ?Container
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * 绑定一个服务标识到一个命名空间
     * 这个方法允许容器知道当请求一个服务时应该实例化哪个类
     * @param string $id 服务的标识符
     * @param string $namespace 对应的类的命名空间
     * @return Container 返回容器实例，支持链式调用
     */
    public function bind(string $id, string $namespace): Container
    {
        $this->bindings[$id] = $namespace;
        return $this;
    }

    /**
     * 绑定一个服务标识到一个已存在的实例
     * 这个方法用于当需要在整个应用中共享一个单一实例时
     * @param string $id 服务的标识符
     * @param object $instance 已存在的实例
     * @return Container 返回容器实例，支持链式调用
     */
    public function singleton(string $id, object $instance): Container
    {
        $this->bindings[$id] = $instance;
        return $this;
    }

    /**
     * 获取与服务标识绑定的实例或类
     * 这个方法首先检查服务是否已绑定，如果没有找到则抛出异常
     * @param mixed $id 服务的标识符
     * @return mixed 绑定的实例或类
     * @throws Exception 当服务标识未在容器中注册时
     */
    public function get($id)
    {
        if ($this->has($id)) {
            return $this->bindings[$id];
        }
        throw new Exception("Container entry not found for: {$id}");
    }

    /**
     * 检查服务标识是否已绑定到容器
     * @param mixed $id 服务的标识符
     * @return bool 如果服务已绑定返回true，否则返回false
     */
    public function has($id): bool
    {
        return array_key_exists($id, $this->bindings);
    }

    /**
     * 解析一个类的实例
     * 这个方法通过ClassResolver类来实例化一个类，并允许传递构造函数参数
     * @param string $namespace 类的命名空间
     * @param array $args 构造函数参数
     * @return object 实例化的对象
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function resolve(string $namespace, array $args = []): object
    {
        return (new ClassResolver($this, $namespace, $args))->getInstance();
    }

    /**
     * 解析并调用对象的方法
     * 这个方法通过MethodResolver类来调用对象的某个方法，并允许传递方法参数
     * @param object $instance 对象实例
     * @param string $method 方法名
     * @param array $args 方法参数
     * @return mixed 方法调用的结果
     * @throws \ReflectionException
     */
    public function resolveMethod(object $instance, string $method, array $args = [])
    {
        return (new MethodResolver($this, $instance, $method, $args))->getValue();
    }
}
