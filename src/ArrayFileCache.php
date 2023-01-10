<?php

namespace Tochka\Cache;

use Psr\SimpleCache\CacheInterface;

class ArrayFileCache implements CacheInterface
{
    private ?array $data = null;
    private string $cachePath;
    private string $cacheName;

    public function __construct(string $cachePath, string $cacheName)
    {
        $this->cachePath = $cachePath;
        $this->cacheName = $cacheName;
    }

    protected function getData(): array
    {
        if ($this->data === null) {
            $filePath = $this->getCacheFilePath();
            if (file_exists($filePath)) {
                $this->data = require $filePath;
            } else {
                $this->data = [];
            }
        }

        return $this->data;
    }

    protected function saveData(array $data): void
    {
        if (!is_dir($this->cachePath) && !mkdir($this->cachePath) && !is_dir($this->cachePath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->cachePath));
        }

        $this->data = $data;

        file_put_contents($this->getCacheFilePath(), '<?php return ' . var_export($data, true) . ';' . PHP_EOL);
    }

    public function get(string $key, mixed $default = null)
    {
        return array_key_exists($key, $this->getData()) ? $this->getData()[$key] : $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null)
    {
        $data = $this->getData();
        $data[$key] = $value;

        $this->saveData($data);
    }

    public function delete(string $key)
    {
        $data = $this->getData();
        unset($data[$key]);

        $this->saveData($data);
    }

    public function getMultiple(iterable $keys, mixed $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null)
    {
        $data = $this->getData();

        if (is_array($values)) {
            $mergedValues = $values;
        } elseif ($values instanceof \Traversable) {
            $mergedValues = iterator_to_array($values);
        } else {
            throw new IterableInvalidArgument('Argument $values must be iterable');
        }

        $this->saveData(array_merge($data, $mergedValues));
    }

    public function deleteMultiple(iterable $keys)
    {
        $data = $this->getData();

        foreach ($keys as $key) {
            unset($data[$key]);
        }

        $this->saveData($data);
    }

    public function clear(): void
    {
        $this->data = [];

        $filePath = $this->getCacheFilePath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
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
