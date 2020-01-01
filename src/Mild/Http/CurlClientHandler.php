<?php

namespace Mild\Http;

use Psr\Http\Message\RequestInterface;

class CurlClientHandler extends AbstractClientHandler
{
    /**
     * CurlClientHandler constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (!isset($options[CURLOPT_CONNECTTIMEOUT])) {
            $options[CURLOPT_CONNECTTIMEOUT] = 15;
        }

        $options[CURLOPT_HEADER] = true;
        $options[CURLOPT_FOLLOWLOCATION] = 0;
        $options[CURLOPT_RETURNTRANSFER] = true;

        parent::__construct($options);
    }

    /**
     * @param RequestInterface $request
     * @return Response
     * @throws NetworkException
     */
    public function handle(RequestInterface $request)
    {
        $uri = $request->getUri();

        $ch = curl_init($uri->__toString());

        if (($method = $request->getMethod()) !== 'GET') {
            $this->setOption(CURLOPT_CUSTOMREQUEST, $method);
            if ($method === 'HEAD') $this->setOption(CURLOPT_NOBODY, true);
            elseif ($method === 'POST' && !empty($body = $request->getBody()->__toString())) $this->setOption(CURLOPT_POSTFIELDS, $body);
        }

        $this->setOption(CURLOPT_HTTPHEADER, $this->normalizeHeaders($request->getHeaders()));

        curl_setopt_array($ch,$this->getOptions());

        $output = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new NetworkException($request, curl_error($ch));
        }

        curl_close($ch);

        [$headers, $body] = explode("\r\n\r\n", $output);

        return $this->createResponse(explode("\r\n", $headers), $body);
    }
}