<?php

namespace sifdb;

use sifdb\query\SifQuery;
use sifdb\query\SifQueryFind;

class SifDB
{
    const STORAGE_NAME_DEFAULT = 'dafault';
    const COLL_EXT = '.sifdata';
    const COLL_EXT_SCHEMA = '.sifschema';
    const COLL_FILENAME = 'chunk_';

    private $storageDir = '';
    private $storageDirFiles = '';
    private $storageDirCollections = '';
    private $storageKey = '';
    private $storageAlg = '';

    private $handler = null;

    static private $instances = [];
    private function __clone() {}

    /**
     * SifDB constructor.
     * @param array $config
     * @throws SifDBException
     */
    private function __construct($config = [])
    {
        $this->storageDir = SifFS::getPath(
            !empty($config['dir']) ?
                str_replace(['-', ' '], '_', trim($config['dir'])) :
                $_SERVER['DOCUMENT_ROOT'] . '/sifdb_storage/'
        );
        $this->storageDirCollections = SifFS::getPath(
            !empty($config['dir_collections']) ?
                str_replace(['-', ' '], '_', trim($config['dir_collections'])) :
                $this->storageDir .'collections'
        );
        $this->storageDirFiles = SifFS::getPath(
            $this->storageDir . (!empty($config['dir_files']) ? $config['dir_files'] : 'files')
        );
        $this->storageKey = !empty($config['key']) ? $config['key'] : '';
        $this->storageAlg = !empty($config['alg']) ? $config['alg'] : 'AES-192-CBC';

        if (!empty($this->storageKey) && empty($this->storageAlg))
            throw new SifDBException('You should specify the storage cypher alg. that your system supports',
                SifDBException::CODE_WRONG_USAGE);
        if (empty($this->storageKey) && !empty($this->storageAlg))
            throw new SifDBException('You should specify the storage cypher key if alg. is specified',
                SifDBException::CODE_WRONG_USAGE);
        if (!in_array($this->storageAlg, openssl_get_cipher_methods(true)))
            throw new SifDBException("Cypher alg. {$this->storageAlg} not supported",
                SifDBException::CODE_CYPHER_ERROR);

        if (!SifFS::mkDir($this->storageDir))
            throw new SifDBException("Cannot create directory {$this->storageDir}",
                SifDBException::CODE_FS_ERROR);
        if (!SifFS::mkDir($this->storageDirCollections))
            throw new SifDBException("Cannot create directory {$this->storageDirCollections}",
                SifDBException::CODE_FS_ERROR);
        if (!SifFS::mkDir($this->storageDirFiles))
            throw new SifDBException("Cannot create directory {$this->storageDirFiles}",
                SifDBException::CODE_FS_ERROR);

        $this->handler = new SifFS($this->storageKey, $this->storageAlg, $config['key_schema']);
    }

    /**
     * @param array $config
     * @param string $instanceName
     * @return SifDB
     */
    static public function gi($instanceName = self::STORAGE_NAME_DEFAULT, $config = [])
    {
        return empty($instances[$instanceName]) ? (new self($config)) : $instances[$instanceName];
    }

    public function collection($collectionName = '', $collectionChunkSize = null)
    {
        return (new SifQuery($collectionName, $this->storageDirCollections, $collectionChunkSize));
    }

    public function handler()
    {
        return $this->handler;
    }

    public function getStorageDir() {return $this->storageDir;}

    public function getStorageDirFiles() {return $this->storageDirFiles;}

    public function getStorageDirCollections() {return $this->storageDirCollections;}

    public function getStorageKey() {return $this->storageKey;}

    public function getStorageAlg() {return $this->storageAlg;}


}