<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 下午4:12
 */

namespace yii\messenger\channels;


use yii\messenger\base\ChannelInterface;
use yii\messenger\base\SendResult;
use yii\messenger\jobs\SendMessageJob;
use yii\di\Instance;
use yii\queue\Queue;

/**
 * 采用队列的方式,将消息存入队列
 * @package yii\messenger\channels
 */
class JobQueueChannel extends BaseChannel
{
    /**
     * @var string|Queue
     */
    public $queue = 'queue';

    public function init()
    {
        parent::init();
        if (!is_object($this->queue)) {
            $this->queue = Instance::ensure($this->queue, Queue::className());
        }
    }

    public function getChannelType()
    {
        return ChannelInterface::CHANNEL_QUEUE;
    }

    public function collect($messages)
    {
        \Yii::trace('collect message for queue');
        foreach ($messages as $msg) {
            $this->messages[] = new SendMessageJob($msg);
        }
        return $this->send();
    }

    public function sendSingle($message)
    {
        $val = $this->queue->push($message);
        if(!$val){
            return false;
        }
        $ret = new SendResult();
        $ret->status = true;
        $ret->gatewayId = $val;
        return $ret;
    }


}