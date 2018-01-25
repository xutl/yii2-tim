<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use Yii;
use yii\di\ServiceLocator;
use yii\base\InvalidConfigException;

/**
 * 云通信服务类
 * @package xutl\tim
 */
class Tim extends ServiceLocator
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!extension_loaded('openssl')) {
            trigger_error('need openssl extension', E_USER_ERROR);
        }
        if (!in_array('sha256', openssl_get_md_methods(), true)) {
            trigger_error('need openssl support sha256', E_USER_ERROR);
        }
        if (empty ($this->appId)) {
            throw new InvalidConfigException ('The "appId" property must be set.');
        }
        if (empty ($this->accountType)) {
            throw new InvalidConfigException ('The "accountType" property must be set.');
        }
        if (empty ($this->identifier)) {
            throw new InvalidConfigException ('The "identifier" property must be set.');
        }
        if (empty ($this->privateKey)) {
            throw new InvalidConfigException ('The "adminUser" property must be set.');
        }
        if (empty ($this->publicKey)) {
            throw new InvalidConfigException ('The "adminUser" property must be set.');
        }
        $privateKey = Yii::getAlias($this->privateKey);
        $this->privateKey = openssl_pkey_get_private("file://" . $privateKey);
        if ($this->privateKey === false) {
            throw new InvalidConfigException(openssl_error_string());
        }
        $publicKey = Yii::getAlias($this->publicKey);
        $this->publicKey = openssl_pkey_get_public("file://" . $publicKey);
        if ($this->publicKey === false) {
            throw new InvalidConfigException(openssl_error_string());
        }
        $this->_identifierSign = $this->genSig($this->identifier);
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
     * Returns the configuration of aliyun components.
     * @see set()
     */
    public function coreComponents()
    {
        return [
            'cdn' => ['class' => 'xutl\aliyun\components\Cdn'],
            'cloudAuth' => ['class' => 'xutl\aliyun\components\CloudAuth'],
            'cloudPhoto' => ['class' => 'xutl\aliyun\components\CloudPhoto'],
            'cloudPush' => ['class' => 'xutl\aliyun\components\CloudPush'],
            'dm' => ['class' => 'xutl\aliyun\components\Dm'],
            'dns' => ['class' => 'xutl\aliyun\components\Dns'],
            'domain' => ['class' => 'xutl\aliyun\components\Domain'],
            'green' => ['class' => 'xutl\aliyun\components\Green'],
            'httpDns' => ['class' => 'xutl\aliyun\components\Dns'],
            'jaq' => ['class' => 'xutl\aliyun\components\Jaq'],
            'live' => ['class' => 'xutl\aliyun\components\Live'],
            'mts' => ['class' => 'xutl\aliyun\components\Mts'],
            'slb' => ['class' => 'xutl\aliyun\components\Slb'],
            'scdn' => ['class' => 'xutl\aliyun\components\Scdn'],
            'sms' => ['class' => 'xutl\aliyun\components\Sms'],
            'vod' => ['class' => 'xutl\aliyun\components\Vod'],
            'vpc' => ['class' => 'xutl\aliyun\components\Vpc'],
        ];
    }
}