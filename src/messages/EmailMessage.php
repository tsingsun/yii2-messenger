<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/8
 * Time: 上午11:22
 */

namespace yii\messenger\messages;


use yii\messenger\base\ChannelInterface;
use yii\messenger\base\MessageInterface;
use yii\helpers\ArrayHelper;

/**
 * Class EmailMessage,邮件通道的消息类
 * 消息类目前还不支持layout方式
 *
 * @property string $charset 编码
 * @property string $subject 主题
 * @property string|array $from 发件人 可采用[email=>name]来定义
 * @property string|array $to 收件人 可采用[email=>name]来定义
 * @property string|array $cc 抄送 可采用[email=>name]来定义
 * @property string|array $bcc 密送 可采用[email=>name]来定义
 * @property string|array $replyTo 回复至 可采用[email=>name]来定义
 * @property bool $isHtml 是否HTML格式的邮件
 * @package yii\messenger\messages
 */
class EmailMessage extends BaseMessage
{
    public function attributes()
    {
        $attribute = ['charset', 'from', 'subject', 'cc', 'bcc', 'replyTo', 'isHtml'];
        return array_merge(parent::attributes(), $attribute);
    }

    public function getChannels()
    {
        return [
            ChannelInterface::CHANNEL_EMAIL
        ];
    }

    /**
     * 邮件格式
     * @return bool 默认为false
     */
    public function getIsHtml()
    {
        return isset($this->_attributes['isHtml']) && $this->_attributes['isHtml'] == true;
    }

    /**
     * 将通用消息转化为具体通道相关的消息.如转化为SmsMessage以被短信通道所使用.
     * @param MessageInterface $message
     * @return static
     */
    public static function parseMessage($message)
    {
        if($message instanceof EmailMessage){
            return $message;
        }
        if(method_exists($message,'resolveEmailMessage')){
            return call_user_func_array([$message,'resolveEmailMessage'],null);
        }
        $msg = new EmailMessage(ArrayHelper::toArray($message));
        return $msg;
    }

}