<?php

namespace Tochka\Cache;

class IgBinarySerializer implements SerializerInterface
{
    public function serialize(mixed $data, string $fileName): bool
    {
        return file_put_contents($fileName, igbinary_serialize($data));
    }

    public function unserialize(string $fileName): mixed
    {
        return igbinary_unserialize(file_get_contents($fileName));
    }
}
