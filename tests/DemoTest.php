<?php

namespace Rtek\Rollbar\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Rollbar\Payload\Payload;
use Rollbar\Scrubber;
use Rtek\Rollbar\Patch\RollbarLogger;
use Rtek\Rollbar\Patch\RollbarUtilities;
use Rtek\Rollbar\Patch\RollbarUtilitiesFactory;
use Rtek\Rollbar\Serializer\Custom\Psr7Serializer;
use Rtek\Rollbar\Serializer\DefaultRootSerializer;

class DemoTest extends TestCase
{
    public function testPsr7Response(): void
    {
        $logger = new RollbarLogger([
            'access_token' => str_repeat('0', 32),
            'transmit' => false,
            'scrubber'=> $scrubber = $this->createMock(Scrubber::class)
        ]);

        $ruf = new class implements RollbarUtilitiesFactory {
            public function create(Payload $payload): RollbarUtilities
            {
                return new DefaultRootSerializer([
                    new Psr7Serializer()
                ]);
            }
        };

        $logger->setRollbarUtilitiesFactory($ruf);

        $response =  new Response(400, [
            'Foo' => ['bar', 'baz'],
        ], 'foobarbaz');


        $scrubber->method('scrub')
            ->willReturnCallback(function ($data) {
                file_put_contents('tests/_files/output/demo.json', json_encode($data, JSON_PRETTY_PRINT));
                $arg = $data['body']['trace']['frames'][12]['args'][0];
                $this->assertStringContainsString('GuzzleHttp\Psr7\Response#',$arg['__']);
                $this->assertSame('HTTP/1.1 400 Bad Request', $arg['status']);
                $this->assertSame('bar,baz', $arg['headers']['Foo']);
                return '';
            });


        (function($arg) use($logger) {
            $logger->error(new \Exception('foobar'));
        })($response);

    }
}
