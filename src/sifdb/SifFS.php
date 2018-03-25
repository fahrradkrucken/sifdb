<?php

namespace sifdb;


class SifFS
{
    const KEY_SCHEMA_DEFAULT = [0, 2, 5, 1, 4, 3];

    static private $unresolvedSymbols = ['-',' ',',',':','.',';','\\','/','*','+','=','(',')','&','?','<','>','%','$','#','@','!'];

    static private $saltLengthMin = 10;
    static private $saltLengthMax = 32;
    static private $saltSymbols = 'ABCDEFGHIGKLMNOPQRSTUVWXYZ0123456789abcdefghigklmnopqrstuvwxyz';

    static private $strDeletedContent = '----';

    private $cypherKey = '';
    private $cypherAlg = '';
    private $cypher = false;

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

    function __construct($cypherKey = '', $cypherAlg = '', $cypherKeySchema = null)
    {
        if (!empty($cypherKey) || !empty($cypherAlg)) {
            $this->cypherAlg = $cypherAlg;
            $this->cypherKey = $this->hashKey($cypherKey, $cypherKeySchema);
            $this->cypher = true;
        }
    }

    public function fileStrRead($path = '')
    {
        $handle = fopen($path, "r");
        while(!feof($handle)) {
            $data = trim(fgets($handle));
            if (!$this->strIsDeleted($data))
                yield ($this->cypher ?
                    $this->decrypt($data) :
                    $data);
        }
        fclose($handle);
    }

    public function fileStrCount($path = '')
    {
        return iterator_count($this->fileStrRead($path));
    }

    public function fileStrFind($path = '', $position)
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek($position);
        $data = $file->current();
        $file = null;
        if ($data && !$this->strIsDeleted($data)) {
            return ($this->cypher ?
                $this->decrypt($data) :
                $data);
        }
        return false;
    }

    public function fileStrInsert($path = '', $data = '', $position)
    {
        $temp = fopen('php://temp', "rw+");
        $file = fopen($path, 'r+b');

        fseek($file, $position);
        stream_copy_to_stream($file, $temp);

        fseek($file, $position);
        fwrite(
            $file,
            ($this->cypher ? $this->encrypt($data) : $data) . PHP_EOL
        );

        rewind($temp);
        stream_copy_to_stream($temp, $file);

        fclose($temp);
        fclose($file);
    }

    public function fileStrAppend($path = '', $data = '')
    {
        $file = fopen($path, 'a');
        fwrite(
            $file,
            ($this->cypher ? $this->encrypt($data) : $data) . PHP_EOL
        );
        fclose($file);
    }

    public function fileStrDelete($path = '', $position)
    {
        $this->fileStrInsert($path, self::$strDeletedContent, $position);
    }

    static private function hashKey($key = '', $schema = self::KEY_SCHEMA_DEFAULT)
    {
        $keyLength = strlen($key);
        $keyArr = [
            md5(substr($key, 0, intval($keyLength * 0.5))),
            md5(substr($key, 0, intval($keyLength * 0.25))),
            md5(substr($key, intval($keyLength * 0.25), intval($keyLength * 0.5))),
            md5(substr($key, intval($keyLength * 0.5), intval($keyLength * 0.75))),
            md5(substr($key, intval($keyLength * 0.75), $keyLength)),
            md5(substr($key, intval($keyLength * 0.5), $keyLength)),
        ];
        $keyStr = '';
        for ($i = 0; $i < 32; $i++) for ($j = 0; $j < 6; $j++) $keyStr .= $keyArr[ $schema[ $j ] ][ $i ];
        return $keyStr;
    }

    private function getSalt($binary = false)
    {
        if ($binary)
            return substr( md5( openssl_random_pseudo_bytes(32) ), 0, rand(self::$saltLengthMin, self::$saltLengthMax));
        else
            return substr( md5( str_shuffle(self::$saltSymbols) ), 0, rand(self::$saltLengthMin, self::$saltLengthMax));
    }

    private function encrypt($data = [])
    {
        $salt = $this->getSalt();
        $iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length($this->cypherAlg) );
        return base64_encode(
            strlen($salt) . $salt . $iv .
            openssl_encrypt(
                serialize($data),
                $this->cypherAlg,
                $salt . $this->cypherKey,
                OPENSSL_RAW_DATA,
                $iv
            )
        );
    }

    private function decrypt($data = '')
    {
        $data = base64_decode($data);
        $saltLength = intval(substr($data, 0, 2));
        $ivLength = openssl_cipher_iv_length($this->cypherAlg);
        return unserialize(
            openssl_decrypt(
                substr($data, 2 + $saltLength + $ivLength, strlen($data)),
                $this->cypherAlg,
                substr($data,2, $saltLength) . $this->cypherKey,
                OPENSSL_RAW_DATA,
                substr($data, 2 + $saltLength, $ivLength)
            )
        );
    }

    private function strIsDeleted($data = '')
    {
        return empty($data) || (string)$data === self::$strDeletedContent;
    }

}