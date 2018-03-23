<?php

namespace sifdb\query;

use sifdb\SifDBException;
use sifdb\SifHelper;

class SifQuery
{
    private $collectionName = '';

    private $collectionDir = '';

    private $queryFind = [];

    /**
     * SifQuery constructor.
     * @param string $collectionName
     * @param string $collectionDir
     * @throws SifDBException
     */
    function __construct($collectionName = '', $collectionDir = '')
    {
        if (empty($collectionName))
            throw new SifDBException('$collectionName required', SifDBException::CODE_WRONG_USAGE);
        if (empty($collectionDir))
            throw new SifDBException('$collectionDir required', SifDBException::CODE_WRONG_USAGE);

        $this->collectionName = SifHelper::normalizeName($collectionName);
        $this->collectionDir = SifHelper::getPath("{$collectionDir}/{$this->collectionName}/");

        if (!SifHelper::mkDir($this->collectionDir))
            throw new SifDBException("Cannot create directory {$this->collectionDir}",
                SifDBException::CODE_FS_ERROR);
    }
}