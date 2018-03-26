<?php

use sifdb\SifDB;
use sifdb\SifDBException;

class ExceptionsTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $configWrongUsage = [
        'alg' => 'AES-256-CBC',
    ];

    protected $configCypherErr = [
        'key' => 'mySuperStrongKey',
        'alg' => 'AES-256-CBCBlaBla',
    ];

    public function testDryUsage()
    {
        SifDB::gi();
    }

    public function testWrongUsage()
    {
        $this->expectException(SifDBException::class);
        $this->expectExceptionCode(SifDBException::CODE_WRONG_USAGE);
        $newInstance = SifDB::gi(md5(time()), $this->configWrongUsage);
        $this->tester->comment("We get wrong usage ERROR");
    }

    public function testCypherError()
    {
        $this->expectException(SifDBException::class);
        $this->expectExceptionCode(SifDBException::CODE_CYPHER_ERROR);
        $newInstance = SifDB::gi(md5(time()), $this->configCypherErr);
    }
}