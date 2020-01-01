<?php

namespace Mild\Mail;

use Mild\Support\Str;
use Mild\Contract\Mail\BodyInterface;
use Mild\Contract\Mail\CollectionInterface;

class BodyCollection implements CollectionInterface
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param BodyInterface $body
     */
    public function add(BodyInterface $body)
    {
        $this->items[] = $body;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $result = '';

        foreach ($this->getParts() as $key => $parts) {
            $result .= $this->resolvePart($key, $parts, count($parts) > 1);
        }

        return $result;
    }

    private function getParts()
    {
        $parts = [
            'multipart/mixed' => [],
            'multipart/alternative' => []
        ];

        foreach ($this->items as $item) {

            if (($pos = strpos($contentType = strtolower($item->getHeader()->line('Content-Type')), ';')) !== false) {
                $contentType = substr($contentType, 0, $pos);
            }

            $item = $item->toString();

            if (!in_array($contentType = trim($contentType), ['text/html', 'text/plain'])) {
                $parts['multipart/mixed'][] = $item;
            } else {
                $parts['multipart/alternative'][] = $item;
            }

        }

        if (!empty($parts['multipart/mixed']) && !empty($parts['multipart/alternative'])) {

            $parts['multipart/mixed'] = array_merge(
                (count($parts['multipart/alternative']) > 1) ? [['multipart/alternative' => $parts['multipart/alternative']]] : $parts['multipart/alternative'],
                $parts['multipart/mixed']
            );

            unset($parts['multipart/alternative']);
        }

        return array_filter($parts);
    }

    /**
     * @param $contentType
     * @param $parts
     * @param $multiple
     * @return string
     */
    private function resolvePart($contentType, $parts, $multiple)
    {
        $boundary = sprintf('_=_mild_%s_%s_=_', time(), Str::random());

        $result = $multiple ? (sprintf("Content-Type: %s; boundary=\"%s\"", $contentType, $boundary)."\r\n") : '';

        foreach ($parts as $part) {
            if (is_array($part)) {
                foreach ($part as $key => $value) {
                    $result .= sprintf("\r\n--%s\r\n", $boundary).$this->resolvePart($key, $value, count($value) > 1)."\r\n";
                }
                continue;
            }

            if ($multiple) {
                $part = sprintf("\r\n--%s\r\n%s\r\n", $boundary, $part);
            }

            $result .= $part;
        }

        return $result.($multiple ? sprintf("\r\n--%s--", $boundary) : '');
    }
}