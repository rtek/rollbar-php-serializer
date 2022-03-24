<?php declare(strict_types=1);

namespace Rtek\Rollbar\Serializer;

final class DefaultSerializer implements Serializer
{
    public function canSerialize($mixed): bool
    {
        return true;
    }

    public function serialize(RootSerializer $root, $mixed)
    {
        if (is_string($mixed) || is_int($mixed) || is_float($mixed) || is_bool($mixed) || is_null($mixed)) {
            return $mixed;
        }

        if (is_resource($mixed)) {
            return sprintf('%s#%d', get_resource_type($mixed), get_resource_id($mixed));
        }

        if (is_array($mixed)) {
            $result = [];
            foreach ($mixed as $key => $value) {
                $result[$key] = $root->serialize($value);
            }
            return $result;
        }

        if (is_object($mixed)) {
            return self::serializeObject($mixed);
        }

        return var_export($mixed, true);
    }

    public static function serializeObject(object $object): string
    {
        return sprintf('%s#%d', get_class($object), spl_object_id($object));
    }
}
