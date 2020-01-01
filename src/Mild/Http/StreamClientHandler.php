<?php

namespace Mild\Http;

use Psr\Http\Message\RequestInterface;

class StreamClientHandler extends AbstractClientHandler
{

    /**
     * @param RequestInterface $request
     * @return Response
     * @throws NetworkException
     */
    public function handle(RequestInterface $request)
    {
        $this->setOption('http', [
            'method'           => ($method = $request->getMethod()),
            'header'           => $this->normalizeHeaders($request->getHeaders()),
            'protocol_version' => $request->getProtocolVersion(),
            'ignore_errors'    => true,
            'follow_location'  => 0
        ]);

        if (!empty($body = $request->getBody()->__toString())) $this->setOption('http.content', $body);

        if (!($handle = @fopen($request->getUri()->__toString(), 'r', false, stream_context_create($this->getOptions())))) throw new NetworkException($request);

        stream_set_timeout($handle, $this->getOption('timeout', 15));

        $headers = $http_response_header ?? [];

        $body = fgets($handle);

        fclose($handle);

        return $this->createResponse($headers, $body);
    }
}