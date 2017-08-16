<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/10
 * Time: 下午8:00
 */

namespace yii\messenger\jobs;


use yii\messenger\base\ChannelErrorException;
use yii\messenger\base\MessageInterface;
use yii\messenger\base\SendEvent;
use yii\messenger\Dispatcher;
use yii\messenger\Messenger;
use yii\queue\Job;

/**
 * Class SendMessageJob
 *
 * @package yak\message\jobs
 */
class SendMessageJob implements Job
{
    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * SendMessageJob constructor.
     * @param MessageInterface $message
     */
    function __construct($message)
    {
        $this->message = $message;
    }

    public function execute($queue)
    {
        //不再进行存入消息列队的操作
        Messenger::instance()->dispatcher->sendToQueue = false;
        Messenger::instance()->dispatcher->on(Dispatcher::EVENT_SEND_ERROR,[$this,'handleSendErrorEvent']);
        Messenger::instance()->message($this->message)->send();
    }

    /**
     * @param SendEvent $event
     * @throws ChannelErrorException
     */
    public function handleSendErrorEvent($event){
        throw new ChannelErrorException($event->sendResult->error);
    }


}