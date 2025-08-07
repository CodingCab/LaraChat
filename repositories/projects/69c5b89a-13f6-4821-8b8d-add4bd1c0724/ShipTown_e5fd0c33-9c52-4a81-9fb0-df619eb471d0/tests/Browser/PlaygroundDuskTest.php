<?php

namespace Tests\Browser;

use App\Tests\InventoryDashboardPageTestNew;
use App\Tests\LoginPageTestNew;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Tests\DuskTestCase;

class PlaygroundDuskTest extends DuskTestCase
{
    public function testBasicExample(): void
    {
        $className = [
            InventoryDashboardPageTestNew::class,
            LoginPageTestNew::class,
        ];

        collect($className)->each( function ($class) {
            $this->executeTestMethods($class);
        });
    }

    /**
     * @throws ReflectionException
     */
    public function executeTestMethods(string $className): void
    {
        $reflection = new ReflectionClass($className);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (str_starts_with($method->name, 'test')) {
                $newClass = new $className($method->name);
                $newClass->setParentTest($this);
                $newClass->setBrowser($this->browser());
                $this->visit('/');

                $newClass->{$method->name}();

                // display the test method name
                echo "Ran test: " . $className . '::' . $method->name . "\n";
            }
        }
    }
}
