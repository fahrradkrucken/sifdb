<?php

namespace sifdb\query;


class SifQueryUpdate extends SifAbstractQuery
{

    public function where()
    {
        return $this;
    }

    public function andWhere()
    {
        return $this;
    }

    public function orWhere()
    {
        return $this;
    }
}