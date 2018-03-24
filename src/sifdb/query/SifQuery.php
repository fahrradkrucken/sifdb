<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 24.03.2018
 * Time: 17:12
 */

namespace sifdb\query;


use sifdb\SifDB;

class SifQuery extends SifAbstractQuery
{
    public function find($condition = [])
    {

    }

    public function findOne($_id = null, $condition = [])
    {
        $_id = intval($_id);
        $fileN = 1;
        $strN = $_id - 1;

        $result = null;

        if (!empty($_id)) {
            if ($strN > $this->collectionChunkSize) {
                $strN = ($_id % $this->collectionChunkSize) - 1;
                $fileN = intval(floor($_id / $this->collectionChunkSize));
            }
            $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
            if (file_exists($fileName) && is_file($fileName)) $result = $this->storage->fileStrFind($fileName, $strN);
        } elseif (!empty($condition)) {
            $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
            while (file_exists($fileName)) {
                $fileStrings = $this->storage->fileStrFind($fileName);
                foreach ($fileStrings as $number => $data)
                $fileN++;
                $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
            }
        }

        return $result;
    }
}