<?php

namespace Rtek\Rollbar\Patch;

use Rollbar\Payload\Payload;

interface RollbarUtilitiesFactory
{
    public function create(Payload $payload): RollbarUtilities;
}
