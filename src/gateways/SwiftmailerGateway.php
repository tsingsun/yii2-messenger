<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/8
 * Time: 上午10:03
 */

namespace yii\messenger\gateways;


use yii\messenger\base\MessageInterface;
use yii\messenger\base\SendResult;
use yii\messenger\messages\EmailMessage;
use Yii;
use yii\swiftmailer\Mailer;

/**
 * email channel的邮件发送组件,使用swiftmailer组件进行邮件发送
 *
 * @package yii\messenger\gateways
 */
class SwiftmailerGateway extends BaseGateway
{
    /**
     * @var Mailer
     */
    private $mailer;

    public function init()
    {
        parent::init();
        if (Yii::$app->mailer instanceof Mailer) {
            $this->mailer = Yii::$app->mailer;
        }
    }

    /**
     * @param EmailMessage $message
     * @see \yii\mail\MessageInterface
     * @return \yii\mail\MessageInterface
     */
    public function formatMessage($message)
    {
        $template = $message->getTemplate($this);
        if ($template) {
            $view = [
                ($message->isHtml ? 'html' : 'text') => $template
            ];
            //都不启用layout
            $this->mailer->htmlLayout = false;
            $this->mailer->textLayout = false;
            $mailer = $this->mailer->compose($view, $message->toArray());
        } else {
            if ($message->isHtml) {
                $mailer = $this->mailer->compose()->setHtmlBody($message->content);
            } else {
                $mailer = $this->mailer->compose()->setTextBody($message->content);
            }
        }

        $mailer->setTo($message->to)->setSubject($message->subject);

        if ($message->cc) {
            $mailer->setCc($message->cc);
        }
        if ($message->bcc) {
            $mailer->setBcc($message->cc);
        }
        if ($message->replyTo) {
            $mailer->setReplyTo($message->replyTo);
        }
        return $mailer;
    }

    public function sendInternal($to, MessageInterface $message, $config = [])
    {
        $mailer = $this->formatMessage(EmailMessage::parseMessage($message));
        $ret = new SendResult();
        $ret->status = $mailer->send();
        return $ret;
    }


}