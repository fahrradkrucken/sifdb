<?php

namespace sifdb\query;


use sifdb\SifDB;

class SifQuery extends SifAbstractQuery
{
    public function find($condition = [])
    {
        $this->result = [];
        $fileN = 1;

        $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
        while (file_exists($fileName)) {
            $fileStrings = $this->storage->fileStrRead($fileName);
            foreach ($fileStrings as $number => $data) if ($this->conditionsRight($condition, $data)) $this->result[] = $data;
            $fileN++;
            $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
        }

        return $this;
    }

    public function findOne($_id = null, $condition = [])
    {
        $this->result = null;

        $_id = intval($_id);
        $fileN = 1;
        $strN = $_id - 1;

        if (!empty($_id)) {

            if ($strN > $this->collectionChunkSize) {
                $strN = ($_id % $this->collectionChunkSize) - 1;
                $fileN = intval(floor($_id / $this->collectionChunkSize));
            }
            $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
            if (file_exists($fileName) && is_file($fileName)) $this->result = $this->storage->fileStrFind($fileName, $strN);

        } elseif (!empty($condition)) {

            $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
            while (file_exists($fileName)) {
                $fileStrings = $this->storage->fileStrRead($fileName);
                foreach ($fileStrings as $number => $data) if ($this->conditionsRight($condition, $data)) $this->result = $data;
                if ($this->result) break;
                $fileN++;
                $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
            }
        }

        return $this;
    }

    public function order($name, $order = true)
    {
        if (is_callable($order)) {

            usort($this->result, function ($a, $b) use ($name, $order) {
                return call_user_func_array($order, [$a, $b, $name]);
            });

        } elseif (is_numeric($this->result[0][$name]) || is_string($this->result[0][$name])) {
            $order = boolval($order);

            if (is_numeric($this->result[0][$name])) {
                usort($this->result, function ($a, $b) use ($name, $order) {
                    if ($a[$name] == $b[$name]) return 0;
                    return $order ?
                        ($a[$name] < $b[$name] ? -1 : 1) :
                        ($a[$name] > $b[$name] ? -1 : 1);
                });
            } elseif (is_string($this->result[0][$name])) {
                usort($this->result, function ($a, $b) use ($name, $order) {
                    if (strcmp($a[$name], $b[$name]) === 0) return 0;
                    return $order ?
                        (strcmp($a[$name], $b[$name]) ? -1 : 1) :
                        (strcmp($a[$name], $b[$name]) ? 1 : -1);
                });
            }
        }

        return $this;
    }

    public function distinct($name)
    {
        if (is_numeric($this->result[0][$name]) || is_string($this->result[0][$name])) {

            $comparedArr = [];
            $resultArr = [];

            for ($i = 0; count($this->result); $i++) {
                if (!in_array($this->result[$i][$name], $comparedArr)) {
                    $comparedArr[] = $this->result[$i][$name];
                    $resultArr[] = $this->result[$i];
                }
            }

            $this->result = $resultArr;
            unset($comparedArr);
            unset($resultArr);
        }

        return $this;
    }

    public function index($attr, $key = '_id')
    {
        $this->result = array_column($this->result, $attr, $key);
        return $this;
    }

    public function insert($data = [])
    {
        $fileN = 0;
        $fileName = '';
        do {
            $fileN++;
            $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
        } while(file_exists($fileName));

        if ($this->storage->fileStrCount($fileName) == $this->collectionChunkSize)
            $fileName = $this->collectionDir . SifDB::COLL_FILENAME . ($fileN + 1) . SifDB::COLL_EXT;

        $this->storage->fileStrAppend($fileName, $data);
    }

    public function insertMany($dataArr = [])
    {
        $fileN = 0;
        $fileName = '';
        do {
            $fileN++;
            $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
        } while(file_exists($fileName));

        $strCWritten = file_exists($fileName) ? $this->storage->fileStrCount($fileName) : $this->collectionChunkSize;
        $strCChunk = $this->collectionChunkSize;
        $strCCanWrite = $strCChunk - $strCWritten;
        $strCToWrite = count($dataArr);

        if ($strCCanWrite <= $strCToWrite) { // file (not)exists and can write to 1 file

            $this->storage->fileStrAppendMany($fileName, $dataArr);

        } else { // file (not)exists and we canNOT write to 1 file

            $dataArrChunks = [];
            if ($strCCanWrite < $strCChunk) { // we can append some to existing file
                $i = 0;
                for($i = 0; $i < $strCCanWrite; $i++) $dataArrChunks[0][] = $dataArr[$i];
                $dataArr = array_slice($dataArr, $i, count($dataArr));
            } else { // we need to create a new file
                $fileN++;
                $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
            }
            $dataArrChunks = array_merge($dataArrChunks, array_chunk($dataArr, $strCChunk));

            for ($i = 0; $i < count($dataArrChunks); $i++) {
                $this->storage->fileStrAppendMany($fileName, $dataArrChunks[$i]);
                $fileN++;
                $fileName = $this->collectionDir . SifDB::COLL_FILENAME . $fileN . SifDB::COLL_EXT;
            }
        }
    }

}