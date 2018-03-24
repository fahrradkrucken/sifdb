<?php

namespace sifdb;


class SifCollection
{
    protected $collectionName = '';
    protected $collectionDir = '';
    protected $collectionChunkSize = 50;

    protected $_id = null;
    protected $attributes = [];

    /**
     * @param string $collectionName
     * @param string $collectionDir
     * @param null $collectionChunkSize
     * @throws SifDBException
     */
    public function init($collectionName = '', $collectionDir = '', $collectionChunkSize = null)
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

    public function load($attributes = [])
    {
        if (!empty($attributes))
            foreach ($this->attributes as $name => $value)
                if (in_array($name, $attributes))
                    $this->attributes[$name] = $attributes[$name];
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes($attributes = [])
    {
        $this->load($attributes);
    }

    public function getAttribute($name = '')
    {
        return $this->__get($name);
    }

    public function setAttribute($name = '', $value)
    {
        $this->__set($name, $value);
    }

    public function getId()
    {
        return $this->_id;
    }

    public function __get($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->attributes[$name]) && !empty($this->attributes[$name]);
    }

    public function __unset($name)
    {
        if (isset($this->attributes[$name])) unset($this->attributes[$name]);
    }

    public function __toJson()
    {
        return json_encode(
            array_merge($this->attributes, ['_id' => $this->_id]),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    public function __toString()
    {
        return serialize( array_merge($this->attributes, ['_id' => $this->_id]) );
    }
}