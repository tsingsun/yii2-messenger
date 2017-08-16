<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/4
 * Time: 下午3:44
 */

namespace yii\messenger\base;

/**
 * 消息网关接口,每个消息网关实现一种消息的发送方式,如每一种sms的服务商都是单狡的网关
 * 通道和网关的区别,sms消息是一种通道,一种通道具有多种网关来实现,而微信的消息,是一种通道,而微信API则是一种网关.
 * @package yii\messenger\base
 */
interface GatewayInterface
{
    /**
     * @param ChannelInterface $channel
     * @return void
     */
    public function setChannel($channel);
    /**
     * 网关需要提供模板映射,以使业务系统的消息模板代码能对应到各个的网关中,如验证码标识verifyCode
     * 在腾讯的代码10085,而在阿里的代码为39995.在腾讯的网关中就需要做如下配置
     * [
     *      'templates'=>[
     *          'verifyCode'=>'10085'
     *      ]
     * ]
     *
     * @return array
     */
    public function getTemplates();
    /**
     * 格式化为最终gateway所需要的消息,作用在 [[send()]]前
     *
     * @param MessageInterface $message
     * @return mixed
     */
    public function formatMessage($message);

    /**
     * Send a short message.
     *
     * @param int|string|array $to 接收人
     * @param MessageInterface $message 消息
     * @param array $config 指定的配置
     *
     * @return SendResult
     */
    public function send($to, MessageInterface $message, $config = []);
}