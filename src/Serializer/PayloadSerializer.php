<?php declare(strict_types=1);

namespace Rtek\Rollbar\Serializer;

use Rollbar\Payload\Level;
use Rollbar\Payload\Trace;
use Rollbar\Payload\TraceChain;
use Rtek\Rollbar\Patch\RollbarLogger;
use Rtek\Rollbar\Patch\RollbarUtilities;

final class PayloadSerializer implements Serializer, RollbarUtilities
{
    private ?DefaultRootSerializer $root;

    public function serializeForRollbar($mixed, ?array $customKeys = null, &$objectHashes = [], $maxDepth = -1, $depth = 0)
    {
        return $this->root->serialize($mixed);
    }

    public function serializeForRollbarInternal($mixed, ?array $customKeys = null)
    {
        return $this->serializeForRollbar($mixed, $customKeys);
    }

    public function canSerialize($mixed): bool
    {
        return is_object($mixed) && strpos(get_class($mixed), 'Rollbar\Payload') === 0;
    }

    public function serialize(RootSerializer $root, $mixed)
    {
        switch (get_class($mixed)) {
            case TraceChain::class:
                //reverse the trace order to serialize objects LIFO
                $result = $root->serialize(array_reverse($mixed->getTraces()));
                return array_reverse($result);
            case Trace::class:
                //reverse the frame order to serialize objects LIFO
                $result = $this->serializeDefault($root, new Trace(array_reverse($mixed->getFrames(), true), $mixed->getException()));
                $result['frames'] = array_reverse($result['frames']);
                return $result;
            case Level::class:
                return $mixed->serialize();
            default:
                return $this->serializeDefault($root, $mixed);
        }
    }

    private function serializeDefault(DefaultRootSerializer $root, $mixed)
    {
        RollbarLogger::injectRollbarUtilities($mixed, $this);
        $this->root = $root;

        $result = $mixed->serialize();
        foreach ($result as $key => $value) {
            if ($value === null) {
                unset($result[$key]);
            }
        }

        return $result;
    }

}
