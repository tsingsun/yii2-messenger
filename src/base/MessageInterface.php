<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/4
 * Time: 下午3:45
 */

namespace yii\messenger\base;


interface MessageInterface
{
    const TEXT_MESSAGE = 'text';
    const VOICE_MESSAGE = 'voice';

    /**
     * 消息ID，消息初始化时，会产生一个ID
     * @return string
     */
    public function getId();
    /**
     * 消息发送目标
     * @return string|array
     */
    public function getTo();
    /**
     * 消息类型.
     *
     * @return string
     */
    public function getMessageType();

    /**
     * 消息内容
     *
     *
     * @param GatewayInterface|null $gateway
     *
     * @return string
     */
    public function getContent(GatewayInterface $gateway = null);

    /**
     * 消息模板,如果存在的话,网关通常有指定模板的能力.
     *
     * @param GatewayInterface|null $gateway
     *
     * @return string
     */
    public function getTemplate(GatewayInterface $gateway = null);

    /**
     * 额外的消息的数据,经常是网关的个性化需求数据
     *
     * @param GatewayInterface|null $gateway
     *
     * @return array
     */
    public function getExtra(GatewayInterface $gateway = null);

    /**
     *
     * 消息支持的渠道,消息可以指定哪几种渠道进行发送,如果未指定,则默认所有渠道
     *
     * @return array
     */
    public function getChannels();
}