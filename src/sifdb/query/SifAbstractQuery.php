<?php

namespace sifdb\query;

use sifdb\SifDB;
use sifdb\SifDBException;
use sifdb\SifHelper;

abstract class SifAbstractQuery
{
    use SifComparisonTrait;

    protected $collectionName = '';
    protected $collectionDir = '';
    protected $collectionChunkSize = 50;

    protected $storage = null;

    /**
     * SifAbstractQuery constructor.
     * @param string $collectionName
     * @param string $storage
     * @param int $collectionChunkSize
     * @throws SifDBException
     */
    function __construct($collectionName = '', $storage = SifDB::STORAGE_NAME_DEFAULT, $collectionChunkSize = 50)
    {
        if (empty($collectionName)) throw new SifDBException('$collectionName required', SifDBException::CODE_WRONG_USAGE);
        if (!empty($collectionChunkSize) && is_int($collectionChunkSize)) $this->collectionChunkSize = $collectionChunkSize;
        else throw new SifDBException('$collectionChunkSize required', SifDBException::CODE_WRONG_USAGE);
        if (!empty($storage)) $this->storage = SifDB::gi($storage)->handler();
        else throw new SifDBException('$storage required', SifDBException::CODE_WRONG_USAGE);

        $this->collectionName = SifHelper::normalizeName($collectionName);
        $this->collectionDir = SifHelper::getPath(SifDB::gi($storage)->getStorageDirCollections() . "/{$this->collectionName}/");

        if (!SifHelper::mkDir($this->collectionDir))
            throw new SifDBException("Cannot create directory {$this->collectionDir}", SifDBException::CODE_FS_ERROR);
    }
}