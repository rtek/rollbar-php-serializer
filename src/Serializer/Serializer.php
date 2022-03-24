<?php declare(strict_types=1);

namespace Rtek\Rollbar\Serializer;

interface Serializer
{
    public function canSerialize($mixed): bool;

    public function serialize(RootSerializer $root, $mixed);
}
