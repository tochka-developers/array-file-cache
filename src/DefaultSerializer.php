<?php

namespace Tochka\Cache;

class DefaultSerializer implements SerializerInterface
{
    public function serialize(mixed $data, string $fileName): bool
    {
        return file_put_contents($fileName, serialize($data));
    }

    public function unserialize(string $fileName): mixed
    {
        return unserialize(file_get_contents($fileName));
    }
}
