<?php
declare(strict_types=1);

namespace Services;

use KikCMS\Classes\Phalcon\Loader;
use KikCMS\Services\NamespaceService;

class NamespaceServiceTest extends \Codeception\Test\Unit
{
    public function testGetClassNamesByNamespace()
    {
        $testFolderPath = dirname(dirname(__DIR__)) . '/Helpers/NamespaceServiceTestFiles';

        $namespaceService = new NamespaceService();
        $namespaceService->cache = false;

        $loader = $this->createMock(Loader::class);
        $loader->method('getNamespaces')->willReturn(['test' => [$testFolderPath]]);

        $namespaceService->loader = $loader;

        $result = $namespaceService->getClassNamesByNamespace('test');

        $this->assertEquals(['testTestClass'], $result);
    }
}
