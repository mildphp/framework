<?php

namespace Mild\Mail;

class Encoder
{
    /**
     * @var string
     */
    const BASE64 = 'base64';
    /**
     * @var string
     */
    const QUOTED_PRINTABLE = 'quoted-printable';

    /**
     * @param $string
     * @param $type
     * @return string
     * @throws MailException
     */
    public static function encode($string, $type)
    {
        switch (strtolower($type)) {
            case self::BASE64:
                $string = chunk_split(base64_encode($string));
                break;
            case self::QUOTED_PRINTABLE:
                $string = quoted_printable_encode($string);
                break;
            default:
                throw new MailException('Unsupported ['.$type.'] encoding.');
                break;
        }

        return trim($string);
    }
}