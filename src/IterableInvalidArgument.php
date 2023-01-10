<?php

namespace Tochka\Cache;

use Psr\SimpleCache\InvalidArgumentException;

class IterableInvalidArgument extends \RuntimeException implements InvalidArgumentException
{
}
