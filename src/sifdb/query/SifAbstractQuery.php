<?php

namespace sifdb\query;

use sifdb\SifDB;
use sifdb\SifDBException;
use sifdb\SifHelper;

abstract class SifAbstractQuery
{
    protected $collectionName = '';
    protected $collectionDir = '';
    protected $collectionChunkSize = 50;

    protected $storage = null;

    /**
     * SifAbstractQuery constructor.
     * @param string $collectionName
     * @param string $collectionDir
     * @param string $storage
     * @param null $collectionChunkSize
     * @throws SifDBException
     */
    function __construct($collectionName = '', $collectionDir = '', $storage = SifDB::STORAGE_NAME_DEFAULT, $collectionChunkSize = null)
    {
        if (empty($collectionName))
            throw new SifDBException('$collectionName required', SifDBException::CODE_WRONG_USAGE);
        if (empty($collectionDir))
            throw new SifDBException('$collectionDir required', SifDBException::CODE_WRONG_USAGE);

        if (!empty($collectionChunkSize) && is_int($collectionChunkSize)) $this->collectionChunkSize = $collectionChunkSize;
        if (!empty($storage)) $this->storage = SifDB::gi($storage)->handler();
        $this->collectionName = SifHelper::normalizeName($collectionName);
        $this->collectionDir = SifHelper::getPath("{$collectionDir}/{$this->collectionName}/");

        if (!SifHelper::mkDir($this->collectionDir))
            throw new SifDBException("Cannot create directory {$this->collectionDir}", SifDBException::CODE_FS_ERROR);
    }

    protected function conditionsRight($conditions = [], $data = [])
    {
        for ($i = 0; $i < count($conditions); $i++) {

        }
    }
}