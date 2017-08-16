<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/3
 * Time: 上午9:23
 */

namespace yiiunit\extensions\messenger\src\console;

use yii\db\Exception;
use yii\messenger\Dispatcher;
use yii\messenger\messages\EmailMessage;
use yii\messenger\consoles\JobController;
use yii\messenger\jobs\SendMessageJob;
use yii\messenger\Messenger;
use yiiunit\extensions\messenger\TestCase;
use Yii;

class JobControllerTest extends TestCase
{
    /** @var  JobController */
    private $controller;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->controller = Yii::$app->createController('job')[0];
    }

    public function testInfo()
    {
        $this->controller->runAction('info');
    }

    private function push()
    {
        $msg = new EmailMessage([
            'to' => '21997272@qq.com',
            'subject'=>'test',
            'template' => 'verifyCode',
        ]);
        $job = new SendMessageJob($msg);
        $id = $this->controller->queue->push($job);
        return $id;
    }

    public function testRun()
    {
        $this->push();
        $this->controller->runAction('run');
    }

    public function testHandleError()
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = \Yii::$app->get(Messenger::COMPONENT_NAME);
        $dispatcher->on(Dispatcher::EVENT_BEFORE_SEND, function ($event){
            throw new Exception('abc');
        });
        $id = $this->push();
        $val = $this->controller->runAction('run');
        $this->invoke($this->controller->queue,'delete',[$id]);
    }


}
