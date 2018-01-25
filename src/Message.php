<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use yii\base\BaseObject;

/**
 * Class Message
 * @package xutl\tim
 */
class Message extends BaseObject
{
    const TYPE_COMMON = 0b0;//普通消息
    const TYPE_LOVE = 0b1;//点赞消息
    const TYPE_TIP = 0b10;//提示消息
    const TYPE_RED_PACKET = 0b11;//红包消息

    /**
     * Message type.
     *
     * @var string
     */
    public $type;

    /**
     * Message id.
     *
     * @var int
     */
    public $id;

    /**
     * Message target user open id.
     *
     * @var string
     */
    public $to;

    /**
     * Message sender open id.
     *
     * @var string
     */
    public $from;

    /**
     * Message attributes.
     *
     * @var array
     */
    public $properties = [];
}