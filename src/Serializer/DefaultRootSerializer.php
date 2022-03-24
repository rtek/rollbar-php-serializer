<?php declare(strict_types=1);

namespace Rtek\Rollbar\Serializer;

use Rtek\Rollbar\Patch\RollbarUtilities;

final class DefaultRootSerializer implements RollbarUtilities, RootSerializer
{
    /** @var Serializer[] */
    private array $serializers;

    private int $maxDepth;

    private int $depth;

    /** @var int[] */
    private array $objects = [];

    public function __construct(array $serializers, int $maxDepth = PHP_INT_MAX)
    {
        $this->serializers = array_merge($serializers, [
            new PayloadSerializer(),
            new DefaultSerializer()
        ]);

        $this->maxDepth = $maxDepth;

        //otherwise there are no args from getTrace()
        ini_set('zend.exception_ignore_args', '0');
    }

    public function serializeForRollbar($mixed, ?array $customKeys = null, &$objectHashes = [], $maxDepth = -1, $depth = 0)
    {
        $this->depth = 0;
        return $this->serialize($mixed);
    }

    public function serializeForRollbarInternal($mixed, ?array $customKeys = null)
    {
        return $this->serializeForRollbar($mixed, $customKeys);
    }

    public function serialize($mixed)
    {
        if (is_object($mixed)) {
            $id = spl_object_id($mixed);
            if (isset($this->objects[$id])) {
                return '#' . DefaultSerializer::serializeObject($mixed);
            }
            $this->objects[$id] = true;
        }

        if (++$this->depth > $this->maxDepth) {
            $result = 'depth reached';
        } else {
            $result = 'failure';
            foreach ($this->serializers as $serializer) {
                if ($serializer->canSerialize($mixed)) {
                    $result = $serializer->serialize($this, $mixed);
                    break;
                }
            }
        }

        $this->depth--;

        return $result;
    }
}
