<?php

namespace Tochka\Cache;

use Psr\SimpleCache\CacheInterface;

class ArrayFileCache implements CacheInterface
{
    private ?array $data = null;
    private string $cachePath;
    private string $cacheName;
    private SerializerInterface $serializer;

    public function __construct(string $cachePath, string $cacheName, ?SerializerInterface $serializer = null)
    {
        $this->cachePath = $cachePath;
        $this->cacheName = $cacheName;
        if ($serializer === null) {
            $this->serializer = new DefaultSerializer();
        } else {
            $this->serializer = $serializer;
        }
    }

    protected function getData(): array
    {
        if ($this->data === null) {
            $filePath = $this->getCacheFilePath();
            if (file_exists($filePath)) {
                $this->data = $this->serializer->unserialize($filePath);
            } else {
                $this->data = [];
            }
        }

        return $this->data;
    }

    protected function saveData(array $data): bool
    {
        if (!is_dir($this->cachePath) && !mkdir($this->cachePath) && !is_dir($this->cachePath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->cachePath));
        }

        $this->data = $data;
        return $this->serializer->serialize($data, $this->getCacheFilePath());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->getData()) ? $this->getData()[$key] : $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $data = $this->getData();
        $data[$key] = $value;

        return $this->saveData($data);
    }

    public function delete(string $key): bool
    {
        $data = $this->getData();
        unset($data[$key]);

        return $this->saveData($data);
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $data = $this->getData();

        if (is_array($values)) {
            $mergedValues = $values;
        } elseif ($values instanceof \Traversable) {
            $mergedValues = iterator_to_array($values);
        } else {
            throw new IterableInvalidArgument('Argument $values must be iterable');
        }

        return $this->saveData(array_merge($data, $mergedValues));
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $data = $this->getData();

        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $this->saveData($data);
    }

    public function clear(): bool
    {
        $this->data = [];

        $filePath = $this->getCacheFilePath();
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->getData());
    }

    private function getCacheFilePath(): string
    {
        return rtrim($this->cachePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->cacheName . '.php';
    }
}
