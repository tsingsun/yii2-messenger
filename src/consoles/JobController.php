<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/2
 * Time: 下午6:32
 */

namespace yii\messenger\consoles;

use yii\queue\ErrorEvent;
use yii\queue\redis\Command;
use yii\queue\redis\Queue;

/**
 * 针对MessageInterface消息队列控制
 *
 * @package yak\message\console
 */
class JobController extends Command
{
    public function init()
    {
        parent::init();
        if(!is_object($this->queue)){
            $this->queue = \Yii::$app->get($this->queue ?? 'queue');
        }
        $this->onError();
    }

    protected function onError()
    {
        $this->queue->on(Queue::EVENT_AFTER_ERROR, [$this, 'errorHandle']);
    }

    /**
     * @param ErrorEvent $event
     */
    protected function errorHandle($event)
    {
        $event->retry = true;
    }

}