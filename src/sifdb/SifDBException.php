<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 22.03.2018
 * Time: 21:43
 */

namespace sifdb;


class SifDBException extends \Exception {
    const CODE_WRONG_USAGE = 1;
    const CODE_FS_ERROR = 2;
    const CODE_CYPHER_ERROR = 3;
}