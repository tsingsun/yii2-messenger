<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 下午2:58
 */

namespace yii\messenger\messages;

use ArrayAccess;
use yii\messenger\base\GatewayInterface;
use yii\messenger\base\MessageInterface;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Object;

/**
 * 消息抽象类,所有消息应该继承该类
 * @property string $id 消息的ID,可以不设置
 * @property string|array $to 接收者
 * @property string $type 消息类型
 * @property string $content 消息内容,通常为文本内容
 * @property string $template 消息模板
 * @property array extra 其他的消息数据
 * @package yii\messenger\messages
 */
abstract class BaseMessage extends Object implements MessageInterface, ArrayAccess, Arrayable
{
    use ArrayableTrait;
    /**
     * 消息数据.
     *
     * @var array
     */
    protected $_attributes = [];

    public function __construct($data = [], array $config = [])
    {
        $attribute = $this->attributes();
        foreach ($data as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            } elseif (is_int($name) && in_array($value, $attribute)) {
                $this->_attributes[$value] = null;
            } elseif (in_array($name, $attribute)) {
                $this->_attributes[$name] = $value;
            } else {
                //归入extra
                $this->_attributes['extra'][$name] = $value;
            }
        }
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        if (!isset($this->_attributes['id']) || $this->_attributes['id']){
            $this->_attributes['id'] = uniqid('',true);
        }
    }

    public function getId()
    {
        return $this->_attributes['id'] ?? null;
    }

    public function getTo()
    {
        return $this->_attributes['to'] ?? null;
    }

    public function getMessageType()
    {
        return $this->_attributes['type'] ?? null;
    }

    public function getContent(GatewayInterface $gateway = null)
    {
        return $this->_attributes['content'] ?? null;
    }

    public function getTemplate(GatewayInterface $gateway = null)
    {
        if ($gateway === null) {
            return $this->_attributes['template'] ?? '';
        }
        $tmpl = $gateway->getTemplates();
        $template = $this->_attributes['template'] ?? '';
        return $tmpl[$template] ?? false;
    }

    public function getExtra(GatewayInterface $gateway = null)
    {
        return $this->_attributes['extra'] ?? null;
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes($names = null, $except = [])
    {
        $values = [];
        foreach ($names as $name) {
            $values[$name] = $this->$name;
        }
        foreach ($except as $name) {
            unset($values[$name]);
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $item)
    {
        $this->$offset = $item;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = $this->attributes();

        return array_combine($fields, $fields);
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        } elseif (in_array($name, $this->attributes())) {
            return null;
        } else {
            return parent::__get($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if (array_key_exists($name, $this->attributes())) {
            return isset($this->_attributes[$name]);
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (in_array($name, $this->attributes())) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function __unset($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            $this->_attributes[$name] = null;
        } else {
            parent::__unset($name);
        }
    }

    public function attributes()
    {
        return ['id', 'to', 'type', 'content', 'template', 'extra'];
    }
}