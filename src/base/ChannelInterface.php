<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/4
 * Time: 下午4:06
 */

namespace yii\messenger\base;

use yii\messenger\Dispatcher;
use yii\messenger\Messenger;

/**
 * 消息通道接口
 * @package yii\messenger\base
 */
interface ChannelInterface
{
    /**
     * 短信通道
     */
    const CHANNEL_SMS = 'SMS';
    /**
     * 邮件
     */
    const CHANNEL_EMAIL = 'email';
    /**
     * 微信
     */
    const CHANNEL_WECHAT = 'wechat';
    /**
     * 队列方式,当Messenger @see Messenger 启用队列方式,消息当不再使用原通道,而是使用队列通道,保存到以
     * 原通道命名的队列
     */
    const CHANNEL_QUEUE = 'queue';
    /**
     * 渠道类型,目前包含3种类型,sms,wechat,email
     * @return string
     */
    public function getChannelType();
    /**
     * @return GatewayInterface[] 该通道下采用的网关
     */
    public function getGateways();

    /**
     * 获取通道的发送策略
     * @return StrategyInterface
     */
    public function getStrategy();
    /**
     * 消息过滤,从一组消息中,过滤出本通道内
     * @param MessageInterface[] $messages
     * @return $int success handle messages count
     */
    public function collect($messages);

    /**
     * 发送消息
     * @return int success count
     */
    public function send();

    /**
     * @param Dispatcher $dispatcher
     */
    public function setDispatcher($dispatcher);

    /**
     * @return Dispatcher
     */
    public function getDispatcher();
}