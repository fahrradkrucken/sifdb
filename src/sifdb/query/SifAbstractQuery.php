<?php

namespace sifdb\query;

use sifdb\SifDBException;
use sifdb\SifHelper;

abstract class SifAbstractQuery
{
    protected $collectionName = '';
    protected $collectionDir = '';
    protected $collectionChunkSize = 50;

    /**
     * SifAbstractQuery constructor.
     * @param string $collectionName
     * @param string $collectionDir
     * @param null $collectionChunkSize
     * @throws SifDBException
     */
    function __construct($collectionName = '', $collectionDir = '', $collectionChunkSize = null)
    {
        if (empty($collectionName))
            throw new SifDBException('$collectionName required', SifDBException::CODE_WRONG_USAGE);
        if (empty($collectionDir))
            throw new SifDBException('$collectionDir required', SifDBException::CODE_WRONG_USAGE);

        if (!empty($collectionChunkSize) && is_int($collectionChunkSize))
            $this->collectionChunkSize = $collectionChunkSize;
        $this->collectionName = SifHelper::normalizeName($collectionName);
        $this->collectionDir = SifHelper::getPath("{$collectionDir}/{$this->collectionName}/");

        if (!SifHelper::mkDir($this->collectionDir))
            throw new SifDBException("Cannot create directory {$this->collectionDir}", SifDBException::CODE_FS_ERROR);
    }
}