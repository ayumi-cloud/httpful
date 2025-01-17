<?php

declare(strict_types=1);

namespace Httpful\tests;

use Httpful\ClientPromise;
use Httpful\Request;
use Httpful\Response;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ClientPromiseTest extends TestCase
{
    public function testGet()
    {
        $client = new ClientPromise();

        $request = (new Request('GET'))
            ->withUriFromString('http://moelleken.org')
            ->followRedirects();

        $promise = $client->sendAsyncRequest($request);

        /** @var Response $result */
        $result = null;
        $promise->then(static function (Response $response, Request $request) use (&$result) {
            $result = $response;
        });

        $promise->wait();

        static::assertInstanceOf(Response::class, $result);
        static::assertContains('Lars Moelleken', (string) $result);
    }

    public function testGetMultiPromise()
    {
        $client = new ClientPromise();

        $client->add_get('http://google.com?a=b');
        $client->add_get('http://moelleken.org');

        $promise = $client->getPromise();

        /** @var Response[] $results */
        $results = [];
        $promise->then(static function (Response $response, Request $request) use (&$results) {
            $results[] = $response;
        });

        $promise->wait();

        static::assertCount(2, $results);
        static::assertContains('<!doctype html>', (string) $results[0]);
        static::assertContains('Lars Moelleken', (string) $results[1]);
    }
}
