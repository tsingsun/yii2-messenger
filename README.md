yak-message
============================

消息是各系统常用的基础模块,本项目旨在实现消息的通用性.

在一个系统中消息分为很多种,如短信消息,邮件消息,微信消息.从基于协议的角度上看,短信有短信,邮件,微信都有各自的协议,如邮件的协议有RTF2312,
微信我们可以认为他的接口协议就是通信协议,只是非国际标准.

从单一种协议上来看,短信具有多种服务商来支持,比如腾讯云,阿里云都有各自的接口来实现短信的发送.

基于这两种现实,可以对系统进行如果划分出通道(channel),网关(gateway)这两个概念来,通道对应消息协议,网关对应在某种协议下的服务接口.
为什么不直接把通道称之为协议,主要是邮件实际上是由多种协议构成,希望能留一些扩展点.

在一些短信的网关的实现上，还借签了[easy-sms](https://github.com/overtrue/easy-sms)。这个库已经实现现在主流的短信网关实现。

INSTALLATION
-------

### 通过 Composer 进行安装

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this project template using the following command:

~~~
composer require --prefer-dist tsingsun/yii2-messenger
~~~

### 消息格式

消息基于都是继承自BaseMessage,在类中定义的一些通用属性,如to,content等,我们就具体消息进行展开

* 短信 - SmsMessage

| 属性 | 通用属性 | 类型 | 说明|
|-----|--------|-----|----|
|to|是|string|接收人|
|template|是|string|模板名,模板名是系统定义的名称,请见[[模板支持]]|
|content |是|string|内容,这与模板是互斥的,使用了模板,内容项就无效|
|sign|否|string|签名是国内短信消息规范,可在网关配置文件中做统一配置,也可单独定义,|
|extra|是|array|额外数据,该数据项的获取器可传递网关参数,由网关自己控制所需要数据.

* 邮件 - EmailMessage

邮件支持基础的信息定义

| 属性 | 通用属性 | 类型 | 说明|
|-----|--------|-----|----|
|charset|否|string|邮件编码格式,可采用[email=>name]来定义|
|from|否|string/array|发件人,可采用[email=>name]来定义|
|to|否|string/array|发件人,可采用[email=>name]来定义|
|cc|否|string/array|发件人,可采用[email=>name]来定义|
|bcc|否|string/array|发件人,可采用[email=>name]来定义|
|replyTo|否|string/array|发件人,可采用[email=>name]来定义|
|template|是|string|模板名,模板名是系统定义的名称,请见[[模板支持]]|
|content |是|string|内容,这与模板是互斥的,使用了模板,内容项就无效|
|isHtml|否|bool|是否为HTML格式的邮件|
|extra|是|array|额外数据,该数据项的获取器可传递网关参数,由网关自己控制所需要数据.

> 收件人写法可以有多种: to:string; to:["email",....];to:["email"=>"name",....]

* 其他说明
> 各种消息定义了基础的消息类针对特定的通道,实际业务中,还会再继承,如定单的短信通知,OrderSmsMessage等来针对特定的消息

* 模板支持

目前很多消息工具都支持模板化,短信的模板是依赖于网关定义,在配置文件中的templates中定义
```php
'templates'=>[
    'verifyCode'=>'10085',
],
```
key值应该考虑为全局性的,因为后期会使用通用消息而非通道特定消息来进行多通道发送,因此同类型的模板名保持一致,以减轻转化工作

### 开始使用

消息类的使用提供组件调用,由于消息是相对较全局的概念,因此不同于module项目,Messenger模块表现为Yii->$app下的一级组件.需要配置在主项目下
```php
'messenger'=>[
    'class'=>'yak\message\components\Dispatcher',
    'channels'=>[
        [
            //短信通道
            'class'=>'yak\message\components\channels\SmsChannel',
            //发送策略
            'strategy'=>[
                'class'=>'yak\message\components\strategies\OrderStrategy',
            ],
            'gateways'=>[
                'qcloud'
            ],
            'gatewayProviders'=>[
                //腾讯云
                'qcloud'=>[
                    'class'=> 'yak\message\components\gateways\QCloudSmsGateway',
                    'appId'=>'',
                    'appKey'=>'',
                    'sign'=>'',
                    'templates'=>[
                        'verifyCode'=>'10085',
                    ],
                ],
            ],
        ],
        [
            //短信通道
            'class'=>'yak\message\components\channels\EmailChannel',
            //发送策略
            'strategy'=>[
                'class'=>'yak\message\components\strategies\OrderStrategy',
            ],
            'gateways'=>[
                'swiftmailer'
            ],
            'gatewayProviders'=>[
                //swiftmailer
                'swiftmailer'=>[
                    'class'=> 'yak\message\components\gateways\SwiftmailerGateway',
                    'templates'=>[
                        //支持别名
                        'register'=>'@yak/message/views/layouts/email-register',
                    ],
                ],
            ],
        ]
    ],
],
```
> 邮件的模板文件的全路径方式有控制器要求，一般采用别名方式

* 组件方式 - Messenger
```PHP
    $messenger = Yii->$app->get('messenger')->getMessenger(); //
    #messenger = Messenger::instance();//简写
    //$msg....实例化
    #messenger->message($msg)->send();
```

### 事件

消息的发送机制队列栈,并且存在多通道方式,因此在send方法中,并没有返回值供业务端.当需要进一步跟踪消息状态时,需要利用事件机制.
提供了beforeSend,afterSend事件,这两个事件在单网关单消息发送过程中会触发.

```php
    //配置文件方式,callable
    'on beforeSend'=>['classname','methodname'],
    //代码方式
    /** @var Dispatcher $dispatcher */
    $dispatcher = \Yii::$app->get('message');
    $dispatcher->on(Dispatcher::EVENT_AFTER_SEND, function ($event) {....});
    $dispatcher->on(Dispatcher::EVENT_BEFORE_SEND, function ($event) {....});
```
事件的event为[[SendEvent]实例,提供了在不同的数据
* beforeSend

单条消息发送前触发,可以处理消息保存,规则验证等.
```php
function onBeforeSend($event)
{
    $gateway = $event->sender;
    $message = $event->message;
    //可以在发送前保存数据,将消息送达状态设计为false
    //消息保存时,需要注意保存时的去重
    $id = saveMessage($message);
    $message->extra['id'] = $id;
}
```

* afterSend

消息发送后触发,可以处理消息状态,异常日志等.
```php
function onAfterSend($event)
{
    $gateway = $event->sender;
    $message = $event->message;
    $result = $event->sendResult;
    if($result->status){
        updateMessage($message->extra['id'])
    }else{
        //某个网关发送异常日志
    }
}
```
> 网关的返回接收请求成功并不等于消息到达，这中间涉及到网关的系统边界，我们这边成功是指网关接收到消息的发送请求，并响应。

* sendError
当消息在每个通道中处理失败时，表示为该通道下的网关都发送失败，则触发该事件。

### 消息扩展及通道化 --待完善
### 消息持久化 --待设计与实现

### 发送策略

目前只是很简单的处理了顺序及随机策略,基于可扩展的策略配置,后期再考虑特定策略

### 队列处理

支持消息的发布与订阅端。
可以在消息源端设定为将业务端消息推送入队列，而不是实际发送。这样业务端就成为发布者。
订阅者从队列中接收消息，执行实际的消息处理及发送。

* 消息源配置
    sendToQueue = true,这样就可以将消息转发至消息队列,
* 消息队列处理后台:
    sendToQueue = false,这样就可以将消息交还给实际的处理Job,
    
内置了基于Job的消息处理。基于Job的通道将消息都处理为SendMessageJob，由订阅端执行该任务。具体的实现支持为[yii-queue](https://github.com/yiisoft/yii2-queue)
如果采用制定化消息，请自己实现消息的订阅。

### 其他通道
* 微信--待实现
 
### 其他网关
* 各主流的短信网关,我们之前采用过的网关,融联云,移动
* 微信网关
