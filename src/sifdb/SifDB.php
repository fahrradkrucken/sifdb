<?php

namespace sifdb;

use sifdb\query\SifQuery;

class SifDB
{
    private $storageDir = '';
    private $storageDirFiles = '';
    private $storageDirCollections = '';
    private $storageKey = '';
    private $storageAlg = '';

    private $storageExtColections = 'sifdata';

    static private $instances = [];
    private function __clone() {}

    /**
     * SifDB constructor.
     * @param array $config
     * @throws SifDBException
     */
    private function __construct($config = [])
    {
        $this->storageDir = SifHelper::getPath(
            !empty($config['dir']) ?
                str_replace(['-', ' '], '_', trim($config['dir'])) :
                $_SERVER['DOCUMENT_ROOT'] . '/sifdb_storage/'
        );
        $this->storageDirCollections = SifHelper::getPath(
            !empty($config['dir_collections']) ?
                str_replace(['-', ' '], '_', trim($config['dir_collections'])) :
                $this->storageDir .'collections'
        );
        $this->storageDirFiles = SifHelper::getPath(
            $this->storageDir . (!empty($config['dir_files']) ? $config['dir_files'] : 'files')
        );
        $this->storageKey = !empty($config['key']) ? $config['key'] : '';
        $this->storageAlg = !empty($config['alg']) ? $config['alg'] : 'AES-192-CBC';

        if (!empty($this->storageKey) && empty($this->storageAlg))
            throw new SifDBException('You should specify the storage cypher alg. that your system supports',
                SifDBException::CODE_WRONG_USAGE);
        if (!in_array($this->storageAlg, openssl_get_cipher_methods(true)))
            throw new SifDBException("Cypher alg. {$this->storageAlg} not supported",
                SifDBException::CODE_CYPHER_ERROR);

        if (!SifHelper::mkDir($this->storageDir))
            throw new SifDBException("Cannot create directory {$this->storageDir}",
                SifDBException::CODE_FS_ERROR);
        if (!SifHelper::mkDir($this->storageDirCollections))
            throw new SifDBException("Cannot create directory {$this->storageDirCollections}",
                SifDBException::CODE_FS_ERROR);
        if (!SifHelper::mkDir($this->storageDirFiles))
            throw new SifDBException("Cannot create directory {$this->storageDirFiles}",
                SifDBException::CODE_FS_ERROR);
    }

    function __toString() { return 'Stringify this class not allowed'; }

    /**
     * @param array $config
     * @param string $instanceName
     * @return SifDB
     */
    static public function gi($config = [], $instanceName = 'default') {
        return empty($instances[$instanceName]) ? (new self($config)) : $instances[$instanceName];
    }

    /**
     * @return bool TRUE if this instance uses cypher to store data
     */
    private function useCypher()
    {
        return !empty($this->storageKey) && !empty($this->storageAlg);
    }

    /**
     * @param string $collectionName
     * @return SifQuery
     */
    public function collection($collectionName = '')
    {
        return (new SifQuery($collectionName, $this->storageDirCollections));
    }
}