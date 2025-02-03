<?php

namespace LumeSocial\Mixpost;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Inovector\Mixpost\Collection\ServiceCollection;
use Inovector\Mixpost\Exceptions\ServiceNotRegistered;
use Inovector\Mixpost\Models\Service as ServiceModel;
use Inovector\Mixpost\Services\FacebookService;
use Inovector\Mixpost\Services\TenorService;
use Inovector\Mixpost\Services\TwitterService;
use Inovector\Mixpost\Services\UnsplashService;
use Inovector\Mixpost\Support\Log;
use Illuminate\Contracts\Foundation\Application;

class ServiceManager
{
    protected ?ServiceCollection $cacheServices = null;
    protected mixed $config;
    /** @var array<string, mixed> */
    protected array $exposedFormAttributes = [];
    protected array $services = [];

    public function __construct(Application $app)
    {
        $this->config = $app->make('config');
        $this->exposedFormAttributes = [];
        $this->services = [
            'social_providers' => config('mixpost.social_providers', [])
        ];
    }

    protected function registeredServices(): array
    {
        return [
            FacebookService::class,
            TwitterService::class,
            UnsplashService::class,
            TenorService::class,
        ];
    }

    public function services(): ServiceCollection
    {
        if ($this->cacheServices) {
            return $this->cacheServices;
        }

        return $this->cacheServices = new ServiceCollection($this->registeredServices());
    }

    public function getServiceClass(string $name): string|null
    {
        $service = Arr::first($this->services()->getClasses(), function ($serviceClass) use ($name) {
            return $serviceClass::name() === $name;
        });

        if (!$service) {
            throw new ServiceNotRegistered($name);
        }

        return $service;
    }

    public function isActive(string|array $name = null): array|bool
    {
        if (is_string($name)) {
            return (bool)$this->get($name, 'active');
        }

        if (is_array($name)) {
            return array_reduce($name, function ($array, $serviceName) {
                $array[$serviceName] = $this->isActive($serviceName);
                return $array;
            }, []);
        }

        return array_reduce($this->services()->getCollection(), function ($array, $service) {
            $array[$service['name']] = $this->isActive($service['name']);
            return $array;
        }, []);
    }

    public function isConfigured(string|array $name = null): array|bool
    {
        if (is_string($name)) {
            $requiredInputs = array_keys(Arr::where($this->getServiceClass($name)::formRules(), function ($rules) {
                return in_array('required', $rules);
            }));

            $configuration = $this->get($name, 'configuration');

            return empty(array_filter($requiredInputs, function ($input) use ($configuration) {
                return empty(Arr::get($configuration, $input));
            }));
        }

        if (is_array($name)) {
            return array_reduce($name, function ($array, $serviceName) {
                $array[$serviceName] = $this->isConfigured($serviceName);
                return $array;
            }, []);
        }

        return array_reduce($this->services()->getCollection(), function ($array, $service) {
            $array[$service['name']] = $this->isConfigured($service['name']);
            return $array;
        }, []);
    }

    public function exposedConfiguration(string|array $name = null): array
    {
        if (is_string($name)) {
            $serviceClass = $this->getServiceClass($name);
            return Arr::only($this->get($name, 'configuration'), $serviceClass::$exposedAttributes ?? []);
        }

        if (is_array($name)) {
            return array_reduce($name, function ($array, $serviceName) {
                $array[$serviceName] = $this->exposedConfiguration($serviceName);
                return $array;
            }, []);
        }

        return array_reduce($this->services()->getCollection(), function ($array, $service) {
            $array[$service['name']] = $this->exposedConfiguration($service['name']);
            return $array;
        }, []);
    }

    public function put(string $name, array $configuration, bool $active = false): void
    {
        Cache::put($this->resolveCacheKey($name), [
            'configuration' => Crypt::encryptString(json_encode($configuration)),
            'active' => $active,
        ]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->services, $key, $default);
    }

    public function all(): array
    {
        return array_reduce($this->services()->getCollection(), function ($array, $service) {
            $array[$service['name']] = $this->get($service['name']);
            return $array;
        }, []);
    }

    public function getFromCache(string $name, mixed $default = null)
    {
        return Cache::get($this->resolveCacheKey($name), $default);
    }

    public function forget($name): void
    {
        Cache::forget($this->resolveCacheKey($name));
    }

    public function forgetAll(): void
    {
        foreach ($this->services()->getNames() as $service) {
            $this->forget($service);
        }
    }

    protected function resolveCacheKey(string $name): string
    {
        return $this->config->get('mixpost.cache_prefix') . ".services.$name";
    }

    protected function logDecryptionError(string $name, DecryptException $exception): void
    {
        Log::error("The application key cannot decrypt the service configuration: {$exception->getMessage()}", [
            'service_name' => $name
        ]);
    }

    public function exposedFormAttributes(): array
    {
        return $this->exposedFormAttributes;
    }

    public function setExposedFormAttributes(array $attributes): self
    {
        $this->exposedFormAttributes = $attributes;
        return $this;
    }

    public function getExposedFormAttributes(): array
    {
        return $this->exposedFormAttributes;
    }
}
