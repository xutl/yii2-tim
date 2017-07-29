<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use yii\base\Object;

class Message extends Object
{
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