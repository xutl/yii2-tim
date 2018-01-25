<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim\components;

use Yii;
use xutl\tim\Tim;
use yii\di\Instance;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Class Signature
 * @package xutl\tim\components
 */
class Signature extends Component
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
     * @var string 私钥
     */
    public $privateKey;

    /**
     * @var string 公钥
     */
    public $publicKey;

    /**
     * @var string|Tim
     */
    public $im = 'im';

    /**
     * @var bool 是否初始化OpenSSL
     */
    private $initOpenSSL = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->im = Instance::ensure($this->im, Tim::className());
        if (empty ($this->appId)) {
            $this->appId = $this->im->appId;
        }
        if (empty ($this->accountType)) {
            $this->accountType = $this->im->accountType;
        }
        if (empty ($this->privateKey)) {
            $this->privateKey = $this->im->privateKey;
        }
        if (empty ($this->publicKey)) {
            $this->publicKey = $this->im->publicKey;
        }
    }

    /**
     * 初始化OpenSSL
     * @throws InvalidConfigException
     */
    public function initOpenSSL()
    {
        if (!$this->initOpenSSL) {
            if (!extension_loaded('openssl')) {
                trigger_error('need openssl extension', E_USER_ERROR);
            }
            if (!in_array('sha256', openssl_get_md_methods(), true)) {
                trigger_error('need openssl support sha256', E_USER_ERROR);
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
        }
    }

    /**
     * 生成用户签名
     * @param string $identifier 用户名
     * @param int $expireTime 签名有效期默认3600秒
     * @return string 生成的UserSig 失败时为false
     * @throws Exception
     */
    public function make($identifier, $expireTime = 3600)
    {
        $json = [
            'TLS.account_type' => $this->accountType,
            'TLS.identifier' => (string)$identifier,
            'TLS.appid_at_3rd' => (string)$this->appId,
            'TLS.sdk_appid' => (string)$this->appId,
            'TLS.expire_after' => (string)$expireTime,
            'TLS.version' => '201512300000',
            'TLS.time' => (string)time()
        ];
        $cacheKey = [__CLASS__, 'identifier' => $identifier];
        if (($signatureContent = $this->im->cache->get($cacheKey)) === false) {
            $content = $this->genSignatureContent($json);
            $signature = $this->makeSignature($content);
            $json['TLS.sig'] = base64_encode($signature);
            if ($json['TLS.sig'] === false) {
                throw new Exception('base64_encode error.');
            }
            $json_text = json_encode($json);
            if ($json_text === false) {
                throw new Exception('json_encode error.');
            }
            $compressed = gzcompress($json_text);
            if ($compressed === false) {
                throw new Exception('gzcompress error.');
            }
            $signatureContent = $this->base64Encode($compressed);
            $this->im->cache->set($cacheKey, $signatureContent, $expireTime);
        }
        return $signatureContent;
    }

    /**
     * 验证用户签名
     * @param string $signature 用户签名
     * @param string $identifier 用户标识符
     * @param int $initTime 签名生成时间
     * @param int $expireTime 过期时间，如：3600秒
     * @param string $errorMsg 失败时的错误信息
     * @return boolean 验证是否成功
     */
    public function verify($signature, $identifier, &$initTime, &$expireTime, &$errorMsg)
    {
        try {
            $errorMsg = '';
            $decodedSig = $this->base64Decode($signature);
            $uncompressedSig = gzuncompress($decodedSig);
            if ($uncompressedSig === false) {
                throw new Exception('gzuncompress error');
            }
            $json = json_decode($uncompressedSig, true);
            if ($json == false) {
                throw new Exception('json_decode error');
            }
            $json = (array)$json;
            if ($json['TLS.identifier'] !== $identifier) {
                throw new Exception("identifier error sigid:{$json['TLS.identifier']} id:{$identifier}");
            }
            if ($json['TLS.sdk_appid'] != $this->appId) {
                throw new Exception("appid error sigappid:{$json['TLS.appid']} thisappid:{$this->appId}");
            }
            $content = $this->genSignatureContent($json);
            $signature = base64_decode($json['TLS.sig']);
            if ($signature == false) {
                throw new Exception('sig json_decode error');
            }
            $succ = $this->verifySignature($content, $signature);
            if (!$succ) {
                throw new Exception('verify failed');
            }
            $initTime = $json['TLS.time'];
            $expireTime = $json['TLS.expire_after'];
            return true;
        } catch (Exception $ex) {
            $errorMsg = $ex->getMessage();
            return false;
        }
    }

    /**
     * ECDSA-SHA256签名
     * @param string $data 需要签名的数据
     * @return string|bool 返回签名 失败时返回false
     * @throws Exception
     */
    private function makeSignature($data)
    {
        $signature = '';
        $this->initOpenSSL();
        if (!openssl_sign($data, $signature, $this->privateKey, 'sha256')) {
            throw new Exception(openssl_error_string());
        }
        return $signature;
    }

    /**
     * 验证ECDSA-SHA256签名
     * @param string $data 需要验证的数据原文
     * @param string $sig 需要验证的签名
     * @return int 1验证成功 0验证失败
     * @throws Exception
     */
    private function verifySignature($data, $sig)
    {
        $this->initOpenSSL();
        $ret = openssl_verify($data, $sig, $this->publicKey, 'sha256');
        if ($ret == -1) {
            throw new Exception(openssl_error_string());
        }
        return $ret;
    }

    /**
     * 根据json内容生成需要签名的buf串
     * @param array $json 票据json对象
     * @return string|bool 按标准格式生成的用于签名的字符串失败时返回false
     * @throws Exception
     */
    private function genSignatureContent(array $json)
    {
        static $members = [
            'TLS.appid_at_3rd',
            'TLS.account_type',
            'TLS.identifier',
            'TLS.sdk_appid',
            'TLS.time',
            'TLS.expire_after'
        ];
        $content = '';
        foreach ($members as $member) {
            if (!isset($json[$member])) {
                throw new Exception('json need ' . $member);
            }
            $content .= "{$member}:{$json[$member]}\n";
        }
        return $content;
    }

    /**
     * 用于url的base64encode
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $string 需要编码的数据
     * @return string 编码后的base64串，失败返回false
     * @throws Exception
     */
    private function base64Encode($string)
    {
        $replace = ['+' => '*', '/' => '-', '=' => '_'];
        $base64 = base64_encode($string);
        if ($base64 === false) {
            throw new Exception('base64_encode error');
        }
        return str_replace(array_keys($replace), array_values($replace), $base64);
    }

    /**
     * 用于url的base64decode
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $base64 需要解码的base64串
     * @return string 解码后的数据，失败返回false
     * @throws Exception
     */
    private function base64Decode($base64)
    {
        $replace = ['+' => '*', '/' => '-', '=' => '_'];
        $string = str_replace(array_values($replace), array_keys($replace), $base64);
        $result = base64_decode($string);
        if ($result == false) {
            throw new Exception('base64_decode error');
        }
        return $result;
    }
}