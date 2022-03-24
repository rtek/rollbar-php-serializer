<?php declare(strict_types=1);

namespace Rtek\Rollbar\Patch;

interface RollbarUtilities
{
    public function serializeForRollbar($mixed, ?array $customKeys = null, array &$objectHashes = [], int $maxDepth = -1, int $depth = 0);

    public function serializeForRollbarInternal($mixed, ?array $customKeys = null);
}
