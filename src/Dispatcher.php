<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/2/5
 * Time: 下午8:58
 */

namespace yii\messenger;

use Yii;
use yii\messenger\base\ChannelInterface;
use yii\messenger\base\MessageInterface;
use yii\messenger\channels\BaseChannel;
use yii\messenger\channels\JobQueueChannel;
use yii\base\Component;
use yii\di\Instance;

/**
 * 消息分发器,该类是实际的messenger组件
 * @package yii\messenger
 */
class Dispatcher extends Component
{
    /**
     * @event 单条消息在将被网关发送前事件，事件的sender为gateway
     */
    const EVENT_BEFORE_SEND = 'beforeSend';
    /**
     * @event 单条消息被网关发送后事件，事件的sender为gateway，注意该事件在消息发送后，但并不意味着消息被网关成功发送。
     * 发送成功只是针对网关接收到该消息的发送请求。实际发送成功需要网关通知。
     */
    const EVENT_AFTER_SEND = 'afterSend';
    /**
     * @event 网关发送消息时错误事件，不于beforeSend，afterSend，对于消息来说，发送失败是针对通道的，因此事件的sender为通道。
     */
    const EVENT_SEND_ERROR = 'sendError';
    /**
     * @var ChannelInterface[]
     */
    public $channels = [];
    /**
     * @var Messenger
     */
    protected $messenger;
    /**
     * 指示时,是否发送到消息队列,如果启用该设置,需要设置queue组件.这时消息发送不是直接调用网关发送,则是保存到队列中.
     * @var bool
     */
    public $sendToQueue = false;
    /**
     * @var mixed 消息队列
     */
    public $queue;

    public function __construct(array $config = [])
    {
        if (isset($config['facade'])) {
            $this->setMessenger($config['facade']);
            unset($config['facade']);
        }
        // connect messenger and dispatcher
        $this->getMessenger();
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        foreach ($this->channels as $name => $channel){
            if(!$channel instanceof ChannelInterface){
                $this->channels[$name] = $this->buildChannel($channel);
            }
        }
    }

    public function buildChannel($channel)
    {
        /** @var BaseChannel $result */
        $result = Instance::ensure($channel,ChannelInterface::class);
        $result->setDispatcher($this);
        return $result;
    }

    public function setMessenger($messenger)
    {
        $this->messenger = $messenger;
        $this->messenger->dispatcher = $this;
    }

    public function getMessenger()
    {
        if($this->messenger === null){
            $this->setMessenger(Messenger::createInternal());
        }
        return $this->messenger;
    }

    /**
     * 消息分发
     * @param MessageInterface[] $messages
     * @return int;
     * @throws \Exception
     */
    public function dispatch($messages)
    {
        $counts = 0;
        foreach ($this->channels as $channel){
            try{
                if ($this->sendToQueue) {
                    if ($channel instanceof JobQueueChannel) {
                        $counts += $channel->collect($messages);
                    }
                    continue;
                } elseif (!$channel instanceof JobQueueChannel) {
                    $counts += $channel->collect($messages);
                }
            }catch (\Exception $e){
                throw $e;
            }
        }
        return $counts;
    }
}