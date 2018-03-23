<?php

namespace sifdb;


class SifHelper
{
    static private $unresolvedSymbols = ['-',' ',',',':','.',';','\\','/','*','+','=','(',')','&','?','<','>','%','$','#','@','!'];

    static private $saltLengthMin = 10;
    static private $saltLengthMax = 32;
    static private $saltSymbols = 'ABCDEFGHIGKLMNOPQRSTUVWXYZ0123456789abcdefghigklmnopqrstuvwxyz';

    static private $strDeletedContent = '----';

    static public function getPath($path = '')
    {
        return str_replace(['/', '//', '\\', '\\\\', '/\\'], DIRECTORY_SEPARATOR, $path);
    }

    static public function normalizeName($name = '')
    {
        return str_replace(self::$unresolvedSymbols, '_', strtolower($name));
    }

    static public function mkDir($path = '')
    {
        return !is_dir($path) ? mkdir(self::getPath($path), 0755, true) : true;
    }

    static private function getSalt()
    {
        return substr( md5( str_shuffle(self::$saltSymbols) ), 0, rand(self::$saltLengthMin, self::$saltLengthMax));
    }

    static private function encrypt($data = [], $method = '', $key = '')
    {
        $salt = self::getSalt();
        $iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length($method) );
        return base64_encode(
            strlen($salt) . $salt . $iv .
            openssl_encrypt(
                serialize($data),
                $method,
                $salt . $key,
                OPENSSL_RAW_DATA,
                $iv
            )
        );
    }

    static private function decrypt($data = '', $method = '', $key = '')
    {
        $data = base64_decode($data);
        $saltLength = intval(substr($data, 0, 2));
        $ivLength = openssl_cipher_iv_length($method);
        return unserialize(
            openssl_decrypt(
                substr($data, 2 + $saltLength + $ivLength, strlen($data)),
                $method,
                substr($data,2, $saltLength) . $key,
                OPENSSL_RAW_DATA,
                substr($data, 2 + $saltLength, $ivLength)
            )
        );
    }

    static private function strIsDeleted($data = '')
    {
        return empty($data) || $data == self::$strDeletedContent;
    }

    static public function fileStrRead($path = '', $decrypt = false, $method = '', $key = '')
    {
        $handle = fopen($path, "r");
        while(!feof($handle)) {
            $data = trim(fgets($handle));
            if (!self::strIsDeleted($data))
                yield ($decrypt && !empty($method) && !empty($key) ?
                    self::decrypt($data, $method, $key) :
                    $data);
        }
        fclose($handle);
    }

    static public function fileStrCount($path = '')
    {
        return iterator_count(self::fileStrRead());
    }

    static public function fileStrFind($path = '', $position, $decrypt = false, $method = '', $key = '')
    {
        $handle = fopen($path, "r");
        $data = fgets($handle, fseek($handle, $position));
        fclose($handle);
        if ($data && !self::strIsDeleted($data)) {
            return ($decrypt && !empty($method) && !empty($key) ?
                self::decrypt($data, $method, $key) :
                $data);
        }
        return false;
    }

    static public function fileStrInsert($path = '', $data = '', $position, $encrypt = false, $method = '', $key = '')
    {
        $temp = fopen('php://temp', "rw+");
        $file = fopen($path, 'r+b');

        fseek($file, $position);
        stream_copy_to_stream($file, $temp);

        fseek($file, $position);
        fwrite(
            $file,
            $encrypt && !empty($method) && !empty($key) ? self::encrypt($data, $method, $key) : $data
        );

        rewind($temp);
        stream_copy_to_stream($temp, $file);

        fclose($temp);
        fclose($file);
    }

    static public function fileStrAppend($path = '', $data = '', $encrypt = false, $method = '', $key = '')
    {
        $file = fopen($path, 'a');
        fwrite(
            $file,
            ($encrypt && !empty($method) && !empty($key) ? self::encrypt($data, $method, $key) : $data) . PHP_EOL
        );
        fclose($file);
    }

    static public function fileStrDelete($path = '', $position)
    {
        self::fileStrInsert($path, self::$strDeletedContent, $position);
    }

}