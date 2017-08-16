<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 上午11:25
 */

namespace yii\messenger\messages;


use yii\messenger\base\ChannelInterface;
use yii\messenger\base\MessageInterface;
use yii\helpers\ArrayHelper;

/**
 * 短信网关的消息类
 *
 * @property string $nationCode 国家代码
 * @property string $sign 签名是国内短信消息规范,可在网关配置文件中做统一配置,也可单独定义
 * @package yii\messenger\messages
 */
class SmsMessage extends BaseMessage
{

    public function attributes()
    {
        $attribute = ['nationCode', 'sign'];
        return array_merge(parent::attributes(), $attribute);
    }

    public function getChannels()
    {
        return [
            ChannelInterface::CHANNEL_SMS
        ];
    }

    /**
     * 将通用消息转化为具体通道相关的消息.如转化为SmsMessage以被短信通道所使用.
     * @param MessageInterface $message
     * @return static
     */
    public static function parseMessage($message)
    {
        if($message instanceof SmsMessage){
            return $message;
        }
        if(method_exists($message,'resolveSmsMessage')){
            return call_user_func_array([$message,'resolveSmsMessage'],null);
        }
        $msg = new SmsMessage(ArrayHelper::toArray($message));
        return $msg;
    }

    public function getNationCode()
    {
        return $this->_attributes['nationCode'] ?? '86';
    }
}