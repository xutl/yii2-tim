<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;

/**
 * Class Tim
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
    public $adminUser;

    /**
     * @var string 私钥
     */
    public $privateKey;

    /**
     * @var string 公钥
     */
    public $publicKey;

    /**
     * @var string 网关地址
     */
    public $baseUrl = 'https://console.tim.qq.com/v4';

    /**
     * @var Client
     */
    public $_httpClient;

    /**
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

        if (empty ($this->adminUser)) {
            throw new InvalidConfigException ('The "adminUser" property must be set.');
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
        $this->publicKey = openssl_pkey_get_public("file://" .$publicKey);
        if ($this->publicKey === false) {
            throw new InvalidConfigException(openssl_error_string());
        }
    }

    /**
     * 请求Api接口
     * @param $url
     * @param string $method
     * @param array $params
     * @param array $headers
     * @return object
     */
    public function api($url, $method = 'POST', array $params = [], array $headers = [])
    {
        $commonParams = [
            'identifier' => $this->adminUser,
            'sdkappid' => $this->appId,
            'random' => $this->generateRandom(),
            'contenttype' => 'json',
            'usersig' => $this->genSig($this->adminUser)
        ];
        $url = $this->composeUrl($url, $commonParams);
        /** @var \yii\httpclient\Response $response */
        try {
            return $this->sendRequest($method, $url, $params, $headers);
        } catch (\Exception $e) {
            sleep(10);
            return $this->sendRequest($method, $url, $params, $headers);
        }
    }

    /**
     * 将用户导入到IM中
     * @param array $params
     * @return array
     */
    public function accountImport(array $params)
    {
        return $this->api('im_open_login_svc/account_import', 'POST', $params);
    }

    /**
     * 创建群
     * @param array $params
     * @return array
     */
    public function createGroup(array $params)
    {
        return $this->api('group_open_http_svc/create_group', 'POST', $params);
    }

    /**
     * 合并URL和参数
     * @param string $url base URL.
     * @param array $params GET params.
     * @return string composed URL.
     */
    protected function composeUrl($url, array $params = [])
    {
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }
        //$url .= http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        foreach ($params as $key => $val) {
            $url .= '&' . $key . '=' . $val;
        }
        return $url;
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

    /**
     * 根据json内容生成需要签名的buf串
     * @param array $json 票据json对象
     * @return string|bool 按标准格式生成的用于签名的字符串失败时返回false
     * @throws Exception
     */
    private function genSignContent(array $json)
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
     * ECDSA-SHA256签名
     * @param string $data 需要签名的数据
     * @return string|bool 返回签名 失败时返回false
     * @throws Exception
     */
    private function sign($data)
    {
        $signature = '';
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
    private function verify($data, $sig)
    {
        $ret = openssl_verify($data, $sig, $this->publicKey, 'sha256');
        if ($ret == -1) {
            throw new Exception(openssl_error_string());
        }
        return $ret;
    }

    /**
     * 生成用户签名
     * @param string $identifier 用户名
     * @param int $expire 签名有效期默认3600秒
     * @return string 生成的UserSig 失败时为false
     * @throws Exception
     */
    public function genSig($identifier, $expire = 3600)
    {
        $json = [
            'TLS.account_type' => $this->accountType,
            'TLS.identifier' => (string)$identifier,
            'TLS.appid_at_3rd' => (string)$this->appId,
            'TLS.sdk_appid' => (string)$this->appId,
            'TLS.expire_after' => (string)$expire,
            'TLS.version' => '201512300000',
            'TLS.time' => (string)time()
        ];
        $content = $this->genSignContent($json);
        $signature = $this->sign($content);
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
        return $this->base64Encode($compressed);
    }

    /**
     * 验证用户签名
     * @param string $sig usersig
     * @param string $identifier 需要验证用户名
     * @param int $initTime usersig中的生成时间
     * @param int $expireTime 如：3600秒
     * @param string $errorMsg 失败时的错误信息
     * @return boolean 验证是否成功
     * @throws Exception
     */
    public function verifySig($sig, $identifier, &$initTime, &$expireTime, &$errorMsg)
    {
        try {
            $errorMsg = '';
            $decodedSig = $this->base64Decode($sig);
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
            $content = $this->genSignContent($json);
            $signature = base64_decode($json['TLS.sig']);
            if ($signature == false) {
                throw new Exception('sig json_decode error');
            }
            $succ = $this->verify($content, $signature);
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
     * Sends HTTP request.
     * @param string $method request type.
     * @param string $url request URL.
     * @param array $params request params.
     * @param array $headers additional request headers.
     * @return object response.
     * @throws Exception on failure.
     */
    protected function sendRequest($method, $url, array $params = [], array $headers = [])
    {
        $response = $request = $this->getHttpClient()->createRequest()
            ->setUrl($url)
            ->setMethod($method)
            ->setHeaders($headers)
            ->setData($params)
            ->send();
        if (!$response->isOk) {
            throw new Exception ('Http request failed.');
        }
        return $response->data;
    }

    /**
     * 生成一个整型随机数
     *
     * @param $length
     *
     * @return string
     */
    private function generateRandom($length = 32)
    {
        $sets = '1234567890';
        $all = str_split($sets);
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= $all[array_rand($all)];
        }
        $random = str_shuffle($random);
        return $random;
    }

    /**
     * 获取Http Client
     * @return Client
     */
    private function getHttpClient()
    {
        if (!is_object($this->_httpClient)) {
            $this->_httpClient = new Client([
                'baseUrl' => $this->baseUrl,
                'requestConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
                'responseConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
            ]);
        }
        return $this->_httpClient;
    }
}