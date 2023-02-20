<?php

namespace Tochka\Cache;

class VarExportSerializer implements SerializerInterface
{
    public function serialize(mixed $data, string $fileName): bool
    {
        return file_put_contents($fileName, '<?php return ' . var_export($data, true) . ';' . PHP_EOL);
    }

    public function unserialize(string $fileName): mixed
    {
        return require $fileName;
    }
}
