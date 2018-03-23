<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 23.03.2018
 * Time: 22:41
 */

namespace sifdb\query;


class SifQueryBuilder extends SifAbstractQuery
{
    public function find($condition = [])
    {
        return (new SifQueryFind($this->collectionName, $this->collectionDir, $this->collectionChunkSize))
            ->where($condition);
    }

    public function insert($data = [])
    {
        return (new SifQueryInsert($this->collectionName, $this->collectionDir, $this->collectionChunkSize))
            ->insert($data);
    }
}