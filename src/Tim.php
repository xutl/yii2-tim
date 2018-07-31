<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use yii\base\Component;
use yii\caching\Cache;
use yii\di\Instance;
use yii\base\InvalidConfigException;
use XuTL\QCloud\Tim\Tim as BaseTIM;

/**
 * 云通信服务类
 *
 * @package xutl\tim
 */
class Tim extends Component
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
     * @var BaseTIM
     */
    private $client;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->client = new BaseTIM($this->appId, $this->accountType, $this->privateKey, $this->publicKey, $this->identifier);
        $this->cache = Instance::ensure($this->cache, Cache::class);
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->client, $method], $parameters);
    }
}