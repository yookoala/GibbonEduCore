<?php

namespace Gibbon\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * A simple PSR response emitter implementation for
 * operating outside of existing framework.
 *
 * Should be retired if decide what framework to use.
 */
class Emitter
{
    /**
     * Send a HTTP response
     *
     * @return void
     */
    function emit(ResponseInterface $response)
    {
        $http_line = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($http_line, true, $response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
    }
}
