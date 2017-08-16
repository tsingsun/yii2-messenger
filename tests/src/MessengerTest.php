<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 下午1:46
 */

namespace yiiunit\extensions\messenger\src;

use yii\messenger\Dispatcher;
use yii\messenger\Messenger;
use yiiunit\extensions\messenger\TestCase;

class MessengerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testInstance()
    {
        $ins = Messenger::instance();
        $this->assertNotEmpty($ins->dispatcher, '请确认配置文件是否正确');
        $this->assertInstanceOf(Messenger::className(), $ins);
    }
}
