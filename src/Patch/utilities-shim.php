<?php declare(strict_types=1);

namespace Rollbar {
    trait UtilitiesTrait
    {
        private $utilities;

        private function utilities()
        {
            return $this->utilities ?? $this->utilities = new Utilities();
        }
    }
}
