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
 * @mixin \XuTL\QCloud\Tim\Tim
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
    public $administrator;

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
        $this->client = new BaseTIM($this->appId, $this->accountType, $this->privateKey, $this->publicKey, $this->administrator);
        $this->cache = Instance::ensure($this->cache, Cache::class);
    }

    /**
     * 获取用户登录签名
     * @param string $identifier
     * @param int $expire 默认有效期30天
     * @return mixed|string
     */
    public function getLoginSignature($identifier, $expire = 2592000)
    {
        $cacheKey = [__CLASS__, 'identifier' => $identifier];
        if (($signatureContent = $this->cache->get($cacheKey)) === false) {
            $signatureContent = $this->client->getLoginSignature($identifier, $expire);
            $this->cache->set($cacheKey, $signatureContent, 259200);
        }
        return $signatureContent;
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