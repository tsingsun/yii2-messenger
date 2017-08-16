<?php

namespace yiiunit\extensions\messenger;

use Yii;
use yii\di\Container;
use yii\helpers\ArrayHelper;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $queries;
    protected $data;
    protected $config;

    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        if (file_exists(__DIR__ . '/config/console-local.php')) {
            $cf = ArrayHelper::merge(
                $c1 = require(__DIR__ . '/config/console.php'),
                $c2 = require(__DIR__ . '/config/console-local.php')
            );
        } else {
            $cf = require(__DIR__ . '/config/console.php');
        }

        new $appClass(ArrayHelper::merge($cf,[
            'vendorPath' => $this->getVendorPath(),
        ], $config));
        $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/yii';
    }

    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        if (file_exists(__DIR__ . '/config/console-local.php')) {
            $cf = ArrayHelper::merge(
                $c1 = require(__DIR__ . '/config/main.php'),
                $c2 = require(__DIR__ . '/config/main-local.php')
            );
        } else {
            $cf = require(__DIR__ . '/config/main.php');
        }

        foreach ($cf['bootstrap'] as $key => $item) {
            if ($item == 'log1' || $item == 'debug') {
                unset($cf['bootstrap'][$key]);
            }
        }
        unset($cf['modules']['debug']);
        new $appClass(ArrayHelper::merge($cf, [
            'id' => 'testapp',
            'vendorPath' => $this->getVendorPath(),
            'components' => [
                'request' => [
//                    'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                    'scriptFile' => __DIR__ . '/../web/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ]
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }

    /**
     * Invokes object method, even if it is private or protected.
     * @param object $object object.
     * @param string $method method name.
     * @param array $args method arguments
     * @return mixed method result
     */
    protected function invoke($object, $method, array $args = [])
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $methodReflection = $classReflection->getMethod($method);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($object, $args);
        $methodReflection->setAccessible(false);
        return $result;
    }

    protected function getVendorPath()
    {
        $vendor = dirname(dirname(__DIR__)) . '/vendor';
        if (!is_dir($vendor)) {
            $vendor = dirname(dirname(dirname(dirname(__DIR__))));
        }
        return $vendor;
    }
}
