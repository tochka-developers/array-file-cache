<?php

namespace Tochka\Cache;

interface SerializerInterface
{
    public function serialize(mixed $data, string $fileName): bool;

    public function unserialize(string $fileName): mixed;
}
