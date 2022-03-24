<?php declare(strict_types=1);

namespace Rtek\Rollbar\Serializer;

interface RootSerializer
{
    public function serialize($mixed);
}
