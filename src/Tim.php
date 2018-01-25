<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use xutl\tim\components\Signature;
use Yii;
use yii\base\Exception;
use yii\caching\Cache;
use yii\di\Instance;
use yii\di\ServiceLocator;
use yii\base\InvalidConfigException;

/**
 * 云通信服务类
 * @property Signature $signature 签名处理
 *
 * @package xutl\tim
 */
class Tim extends ServiceLocator
{
    /**
     * @var int AppId
     */
    public $appId;

    /**
     * @var string 账户类型
     */
    public $accountType;

    /**
     * @var string 管理员账户
     */
    public $identifier;

    /**
     * @var string 私钥
     */
    public $privateKey;

    /**
     * @var string 公钥
     */
    public $publicKey;

    /**
     * @var string|Cache
     */
    public $cache = 'cache';

    /**
     * Tim constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->preInit($config);
        parent::__construct($config);
    }

    /**
     * 预处理组件
     * @param array $config
     */
    public function preInit(&$config)
    {
        // merge core components with custom components
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        }
    }

    /**
     * 获取CDN实例
     * @return object|Signature
     * @throws InvalidConfigException
     */
    public function getSignature()
    {
        return $this->get('signature');
    }

    /**
     * Returns the configuration of aliyun components.
     * @see set()
     */
    public function coreComponents()
    {
        return [
            'signature' => ['class' => 'xutl\tim\components\Signature'],
        ];
    }
}