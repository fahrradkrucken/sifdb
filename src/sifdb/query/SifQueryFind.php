<?php

namespace sifdb\query;


class SifQueryFind extends SifAbstractQuery
{
    public function where($condition = [])
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

    public function like()
    {
        return $this;
    }

    public function orderBy()
    {
        return $this;
    }

}