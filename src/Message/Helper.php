<?php

namespace Omnipay\Cathaybk\Message;

class Helper
{
    /**
     * @param $data
     * @param $keys
     * @return string
     */
    public static function caValue($data, $keys)
    {
        $data = array_key_exists('CUBXML', $data)
            ? static::combin($data, $data['CUBXML'])
            : static::combin($data, $data);

        return md5(implode('', array_map(function ($key) use ($data) {
            return $data[$key];
        }, $keys)));
    }

    /**
     * @param $data
     * @param int $tabDepth
     * @return string
     */
    public static function array2xml($data, $tabDepth = 0)
    {
        return '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'.static::makeXml($data, $tabDepth);
    }

    /**
     * @param $plainText
     * @return array
     */
    public static function xml2array($plainText)
    {
        $arr = [];

        $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';

        preg_match_all($reels, $plainText, $elements);

        foreach ($elements[1] as $ie => $xx) {
            $name = $elements[1][$ie];
            $arr[$name] = [];

            $cdEnd = strpos($elements[3][$ie], '<');
            if ($cdEnd > 0) {
                $arr[$name] = substr($elements[3][$ie], 0, $cdEnd - 1);
            }

            if (preg_match($reels, $elements[3][$ie])) {
                $arr[$name] = self::xml2array($elements[3][$ie]);
            } elseif ($elements[3][$ie]) {
                $arr[$name] = $elements[3][$ie];
            }
        }

        return $arr;
    }

    private static function combin($data, $cubXML)
    {
        if (array_key_exists('ORDERINFO', $cubXML)) {
            $data = array_merge($data, $cubXML['ORDERINFO']);
        }

        if (array_key_exists('AUTHINFO', $cubXML)) {
            $data = array_merge($data, $cubXML['AUTHINFO']);
        }

        return $data;
    }

    private static function makeXml($data, $tabDepth = 0)
    {
        $output = '';
        $nl = "\n".str_repeat("\t", $tabDepth++);
        foreach ($data as $key => $value) {
            $output .= $nl.'<'.$key.'>';
            if (is_bool($value)) {
                $value = (int) $value;
            }
            if (is_array($value)) {
                $output .= self::makeXml($value, $tabDepth).$nl;
            } else {
                $output .= $value;
            }
            $output .= '</'.$key.'>';
        }

        return $output;
    }
}
