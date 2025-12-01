<?php

namespace App\Core\Container;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionUnionType;
use ReflectionNamedType;
use Exception;

class Container
{
    /**
     * The current globally available container (if any).
     */
    protected static ?Container $instance = null;

    /**
     * The container's bindings.
     */
    protected array $bindings = [];

    /**
     * The container's shared instances.
     */
    protected array $instances = [];

    /**
     * The registered type aliases.
     */
    protected array $aliases = [];

    /**
     * Register a binding with the container.
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        // If no concrete type was given, we will simply set the concrete type to the
        // abstract type. After that, the concrete type to be registered as shared
        // without being forced to state their classes in both of the parameters.
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Register a shared binding in the container.
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register an existing instance as shared in the container.
     */
    public function instance(string $abstract, $instance): mixed
    {
        $this->instances[$abstract] = $instance;
        return $instance;
    }

    /**
     * Resolve the given type from the container.
     */
    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Resolve the given type from the container.
     */
    public function resolve(string $abstract, array $parameters = []): mixed
    {
        // If an instance of the type is currently being managed as a singleton we'll
        // just return an existing instance instead of instantiating new instances
        // so the developer can keep using the same objects instance every time.
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConcrete($abstract);

        // We're ready to instantiate an instance of the concrete type registered for
        // the binding. This will instantiate the types, as well as resolve any of
        // its "nested" dependencies recursively until all have been resolved.
        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->make($concrete, $parameters);
        }

        // If the requested type is registered as a singleton we'll want to cache off
        // the instances in "memory" so we can return it later without creating an
        // entirely new instance of an object on each subsequent request for it.
        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get the concrete type for a given abstract.
     */
    protected function getConcrete(string $abstract): mixed
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Determine if the given concrete is buildable.
     */
    protected function isBuildable(mixed $concrete, string $abstract): bool
    {
        return $concrete === $abstract || $concrete instanceof \Closure;
    }

    /**
     * Determine if the given abstract type has been bound.
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Determine if the given abstract type is shared.
     */
    public function isShared(string $abstract): bool
    {
        return isset($this->instances[$abstract]) ||
            (isset($this->bindings[$abstract]['shared']) &&
                $this->bindings[$abstract]['shared'] === true);
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @throws \Exception
     */
    public function build(mixed $concrete, array $parameters = []): mixed
    {
        // If the concrete type is actually a Closure, we will just execute it and
        // hand back the results of the functions, which allows functions to be
        // used as resolvers for more fine-tuned resolution of these objects.
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new Exception("Target class [$concrete] does not exist.", 0, $e);
        }

        // If the type is not instantiable, the developer is attempting to resolve
        // an abstract type such as an Interface or Abstract Class and there is
        // no binding registered for the abstractions so we need to bail out.
        if (! $reflector->isInstantiable()) {
            throw new Exception("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // If there are no constructors, that means there are no dependencies then
        // we can just resolve the instances of the objects right away, without
        // resolving any other types or dependencies out of these containers.
        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.
        try {
            $instances = $this->resolveDependencies($dependencies, $parameters);
        } catch (Exception $e) {
            throw $e;
        }

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     */
    protected function resolveDependencies(array $dependencies, array $parameters): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            // If the dependency has an override parameter, we use that.
            if (array_key_exists($dependency->name, $parameters)) {
                $results[] = $parameters[$dependency->name];
                continue;
            }

            // If the class is null, it means the dependency is a string or some other
            // primitive type which we can not resolve since it is not a class and
            // we will just bomb out with an error since we have no-where to go.
            $type = $dependency->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($dependency->isDefaultValueAvailable()) {
                    $results[] = $dependency->getDefaultValue();
                    continue;
                }

                throw new Exception("Unresolvable dependency resolving [$dependency] in class {$dependency->getDeclaringClass()->getName()}");
            }

            try {
                $results[] = $this->resolve($type->getName());
            } catch (Exception $e) {
                if ($dependency->isOptional()) {
                    $results[] = $dependency->getDefaultValue();
                    continue;
                }

                throw $e;
            }
        }

        return $results;
    }

    /**
     * Set the globally available instance of the container.
     */
    public static function setInstance(Container $container = null): ?Container
    {
        return static::$instance = $container;
    }

    /**
     * Get the globally available instance of the container.
     */
    public static function getInstance(): Container
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }
}
