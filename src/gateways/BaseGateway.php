<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 下午2:30
 */

namespace yii\messenger\gateways;


use yii\messenger\base\ChannelInterface;
use yii\messenger\base\GatewayErrorException;
use yii\messenger\base\GatewayInterface;
use yii\messenger\base\MessageInterface;
use yii\messenger\base\SendEvent;
use yii\messenger\base\SendResult;
use yii\messenger\Dispatcher;
use yii\base\Component;

abstract class BaseGateway extends Component implements GatewayInterface
{
    /**
     * @var float
     */
    public $timeout;
    /**
     * 网关的模板映射,各种网关针对模板都有特定的命名规则,需要指定映射
     * [
     *      'verifyCode'=>'10085',
     * ]
     * @var array
     */
    public $templates = [];
    /**
     * @var ChannelInterface
     */
    private $channel;

    /**
     * @param mixed $to
     * @param MessageInterface $message
     * @param array $config
     * @return SendResult
     * @throws GatewayErrorException
     */
    public abstract function sendInternal($to, MessageInterface $message, $config = []);

    public function setChannel($channel)
    {
        $this->channel = $channel;
    }


    /**
     * @inheritdoc
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param array|int|string $to
     * @param MessageInterface $message
     * @param array $config
     * @return bool|SendResult
     */
    public function send($to, MessageInterface $message, $config = [])
    {
        if (!$this->beforeSend($message)) {
            return false;
        }
        $result = $this->sendInternal($to, $message, $config);
        $this->afterSend($result, $message);
        return $result;
    }

    /**
     * 消息发送时
     * @param MessageInterface $message
     * @return bool
     */
    protected function beforeSend($message)
    {
        $event = new SendEvent();
        $event->sender = $this;
        $event->message = $message;
        $event->handled = true;
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->channel->getDispatcher();
        $dispatcher->trigger(Dispatcher::EVENT_BEFORE_SEND, $event);
        return $event->handled;
    }

    /**
     * 消息发送后
     * @param mixed $result
     * @param MessageInterface $message
     */
    protected function afterSend($result, $message)
    {
        $event = new SendEvent();
        $event->sender = $this;
        $event->message = $message;
        $event->sendResult = $result;
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->channel->getDispatcher();
        $dispatcher->trigger(Dispatcher::EVENT_AFTER_SEND, $event);
    }
}