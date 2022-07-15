<?php

namespace MailjetWp\Sepia\Test;

use MailjetWp\PHPUnit\Framework\TestCase;
use MailjetWp\Sepia\PoParser\Catalog\Catalog;
use MailjetWp\Sepia\PoParser\Parser;
abstract class AbstractFixtureTest extends TestCase
{
    /** @var string */
    protected $resourcesPath;
    public function setUp()
    {
        $this->resourcesPath = \dirname(__DIR__) . '/fixtures/';
    }
    /**
     * @param string $file
     *
     * @return Catalog
     */
    protected function parseFile($file)
    {
        //try {
        return Parser::parseFile($this->resourcesPath . $file);
        //} catch (\Exception $e) {
        //    $this->fail($e->getMessage());
        //}
    }
}
