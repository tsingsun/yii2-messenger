<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/2/5
 * Time: 下午8:42
 */

namespace yii\messenger;

use yii\messenger\base\MessageInterface;
use Yii;
use yii\base\Component;

/**
 * Messenger是YAK 平台的消息组件,通过该组件实现消息的发送.
 * @package yii\messenger
 */
class Messenger extends Component
{
    const COMPONENT_NAME = 'messenger';
    /**
     * @var MessageInterface[]
     */
    protected $messages = [];

    /**
     * @var Dispatcher
     */
    public $dispatcher;

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @return Messenger
     */
    public static function instance()
    {
        return Yii::$app->get(self::COMPONENT_NAME)->getMessenger();
    }

    /**
     * @internal
     * @return Messenger
     */
    public static function createInternal()
    {
        return new static();
    }

    public function setChannels($channel)
    {
        $this->dispatcher->channels[] = $channel;
    }

    /**
     * 发送消息
     *
     * @return int send|handle success count sum per channel
     */
    public function send()
    {
        $messages = $this->messages;
        $this->messages = [];
        if($this->dispatcher instanceof Dispatcher){
            return $this->dispatcher->dispatch($messages);
        }
        return 0;
    }

    /**
     * 将需要发送的消息加入待发送列表
     * @param MessageInterface $message
     * @return $this
     */
    public function message($message)
    {
        $this->messages[] = $message;
        return $this;
    }

}