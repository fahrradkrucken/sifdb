<?php

namespace sifdb\query;

use sifdb\SifDBException;
use sifdb\SifHelper;

abstract class SifAbstractQuery
{
    private $collectionName = '';

    private $collectionDir = '';

    /**
     * SifQuery constructor.
     * @param string $collectionName
     * @param string $collectionDir
     * @throws SifDBException
     */
    function __construct($collectionName = '', $collectionDir = '')
    {
        if (empty($collectionName)) throw new SifDBException('$collectionName required');
        if (empty($collectionDir)) throw new SifDBException('$collectionDir required');

        $this->collectionName = str_replace(['-', ' '], '_', trim($collectionName));
        $this->collectionDir = SifHelper::getPath("{$collectionDir}/{$this->collectionName}/");

        if (!SifHelper::mkDir($this->collectionDir))
            throw new SifDBException("Cannot create directory {$this->collectionDir}");
    }
}