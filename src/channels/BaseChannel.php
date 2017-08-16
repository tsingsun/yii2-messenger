<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 下午1:59
 */

namespace yii\messenger\channels;


use yii\messenger\base\ChannelInterface;
use yii\messenger\base\GatewayErrorException;
use yii\messenger\base\GatewayInterface;
use yii\messenger\base\SendEvent;
use yii\messenger\base\SendResult;
use yii\messenger\base\StrategyInterface;
use yii\messenger\base\MessageInterface;
use yii\base\Component;
use Yii;
use yii\di\Instance;
use yii\messenger\Dispatcher;

abstract class BaseChannel extends Component implements ChannelInterface
{
    /**
     * @var array 当前组件所使用的网关
     */
    public $gateways = [];
    /**
     * @var GatewayInterface[] 网关服务
     */
    public $gatewayProviders = [];
    /***
     * @var MessageInterface[] 消息
     */
    public $messages = [];
    /**
     * @var StrategyInterface
     */
    public $strategy;
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function init()
    {
        parent::init();
        foreach ($this->gatewayProviders as &$gatewayProvider) {
            /** @var GatewayInterface $gatewayProvider */
            $gatewayProvider = Instance::ensure($gatewayProvider, GatewayInterface::class);
            $gatewayProvider->setChannel($this);
        }
        if ($this->strategy) {
            $this->strategy = Instance::ensure($this->strategy, StrategyInterface::class);
        }
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }


    public function getGateways()
    {
        return $this->gateways;
    }

    /**
     * @param MessageInterface[] $messages
     * @return int message handled count
     */
    public function collect($messages)
    {
        foreach ($messages as $message) {
            if (empty($message->getChannels())) {
                //未指定通道,则采用全部
                $this->messages[] = $message;
            } elseif (in_array($this->getChannelType(), $message->getChannels())) {
                $this->messages[] = $message;
            }
        }
        return $this->send();
    }

    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * 通道发送消息
     *
     * 在发送过程中，如果该通道下所有的网关都发送失败，则触发EVENT_SEND_ERROR事件。
     *
     * @return int
     */
    public function send()
    {
        $ret = 0;
        foreach ($this->messages as $key => $msg) {
            $val = $this->sendSingle($msg);
            if(!$val){
                //false，被beforeSend阻止
            } elseif(!$val->status){
                //发送失败
                $event = new SendEvent();
                $event->sender = $this;
                $event->message = $msg;
                $event->sendResult = $val;
                $this->dispatcher->trigger(Dispatcher::EVENT_SEND_ERROR,$event);
            }else{
                $ret++;
            }
            unset($this->messages[$key]);
        }
        return $ret;
    }

    /**
     * @param MessageInterface $message
     * @return SendResult
     */
    public function sendSingle($message)
    {
        $gatewayKeys = array_values($this->getGateways());
        if ($this->getStrategy()) {
            $gatewayKeys = $this->getStrategy()->apply($this->gatewayProviders);
        }
        $providers = $this->gatewayProviders;
        foreach ($gatewayKeys as $key) {
            $gateway = $providers[$key];
            try {
                return $gateway->send($message->getTo(), $message);
            } catch (GatewayErrorException $e) {
                Yii::error("gateway $key send error: {$e->getMessage()}");
                continue;
            }
        }
        $result = new SendResult();
        $result->status = false;
        $result->error = 'message send failure';
        return $result;
    }
}