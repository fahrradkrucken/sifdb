<?php

use sifdb\SifDB;
use sifdb\SifDBException;
use Helper\Unit as TestHelper;

class ExceptionsTest extends \Codeception\Test\Unit
{
    const OUTPUT_DIR = __DIR__ . '/../_output/storage_tests/';
    protected $storageDir = '';

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $configDryUsage = [
        'dir' => self::OUTPUT_DIR,
    ];

    protected $configWrongUsage = [
        'alg' => 'AES-256-CBC',
        'dir' => self::OUTPUT_DIR,
    ];

    protected $configCypherErr = [
        'key' => 'mySuperStrongKey',
        'alg' => 'AES-256-CBCBlaBla',
        'dir' => self::OUTPUT_DIR,
    ];

    protected function _after()
    {
        TestHelper::rrmdir(self::OUTPUT_DIR);
    }

    public function testDryUsage()
    {
        $newInstance = SifDB::gi(md5(time()), $this->configDryUsage);
        $this->assertDirectoryExists($newInstance->getStorageDir(), 'Srotage dir not created');
        $this->assertDirectoryExists($newInstance->getStorageDirCollections(),'Srotage collections dir not created');
        $this->assertDirectoryExists($newInstance->getStorageDirFiles(),'Srotage files dir not created');
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