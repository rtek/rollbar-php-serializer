<?php declare(strict_types=1);

namespace Rtek\Rollbar\Serializer\Custom;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rtek\Rollbar\Serializer\DefaultSerializer;
use Rtek\Rollbar\Serializer\DefaultRootSerializer;
use Rtek\Rollbar\Serializer\RootSerializer;
use Rtek\Rollbar\Serializer\Serializer;

final class Psr7Serializer implements Serializer
{
    public function canSerialize($mixed): bool
    {
        return $mixed instanceof MessageInterface;
    }

    /**
     * @param RootSerializer $root
     * @param MessageInterface $mixed
     * @return array
     */
    public function serialize(RootSerializer $root, $mixed): array
    {
        $result = self::serializeMessage($root, $mixed);

        if ($mixed instanceof ResponseInterface) {
            $result['status'] = $result['status'] . ' ' . $mixed->getStatusCode() . ' ' . $mixed->getReasonPhrase();
            return $result;
        }

        if ($mixed instanceof RequestInterface) {
            $result['status'] = $result['status'] . ' ' . $mixed->getMethod() . ' ' . $mixed->getUri();
        }

        return $result;
    }

    private static function serializeMessage(DefaultRootSerializer $root, MessageInterface $msg): array
    {
        return [
            '__' => DefaultSerializer::serializeObject($msg),
            'status' => 'HTTP/' . $msg->getProtocolVersion(),
            'headers' => self::serializeHeaders($msg->getHeaders()),
            'body' => (string)$msg->getBody(),
        ];
    }

    /**
     * @param string[][] $headers
     * @return string[]
     */
    private static function serializeHeaders(array $headers): array
    {
        $result = [];
        foreach($headers as $name => $values) {
            $result[$name] = implode(',', $values);
        }
        return $result;
    }
}
