<?php declare(strict_types=1);

namespace Rtek\Rollbar\Patch;

final class RollbarLogger extends \Rollbar\RollbarLogger
{
    private ?RollbarUtilitiesFactory $rollbarUtilitiesFactory;

    public function setRollbarUtilitiesFactory(?RollbarUtilitiesFactory $value): void
    {
        $this->rollbarUtilitiesFactory = $value;
    }

    protected function getPayload($accessToken, $level, $toLog, $context)
    {
        $payload = parent::getPayload($accessToken, $level, $toLog, $context);

        if ($this->rollbarUtilitiesFactory) {
            $utilities = $this->rollbarUtilitiesFactory->create($payload);
            self::injectRollbarUtilities($payload, $utilities);
        }
        return $payload;
    }


    /**
     * @param object $object
     * @param RollbarUtilities $utilities
     */
    public static function injectRollbarUtilities(object $object, RollbarUtilities $utilities): void
    {
        try {
            $refl = new \ReflectionClass($object);
            $prop = $refl->getProperty('utilities');
            $prop->setAccessible(true);
            $prop->setValue($object, $utilities);
        } catch (\ReflectionException $e) {
            throw new \LogicException('injectRollbarUtilities failed on ' . get_class($object), 0, $e);
        }
    }
}
