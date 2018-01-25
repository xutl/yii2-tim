<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use yii\di\Instance;
use yii\httpclient\RequestEvent;

/**
 * Class Client
 * @package xutl\tim
 */
class Client extends \yii\httpclient\Client
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
     * @var string 网关地址
     */
    public $baseUrl = 'https://console.tim.qq.com/v4';

    /**
     * @var string|Tim
     */
    private $tim = 'tim';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->tim = Instance::ensure($this->tim, Tim::className());
        if (empty ($this->appId)) {
            $this->appId = $this->tim->accountType;
        }
        if (empty ($this->accountType)) {
            $this->accountType = $this->tim->accountType;
        }
        $this->requestConfig['format'] = Client::FORMAT_JSON;
        $this->responseConfig['format'] = Client::FORMAT_JSON;
        $this->on(Client::EVENT_BEFORE_SEND, [$this, 'RequestEvent']);
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
     * 请求事件
     * @param RequestEvent $event
     * @return void
     * @throws \yii\base\Exception
     */
    public function RequestEvent(RequestEvent $event)
    {
        $commonParams = [
            'identifier' => $this->identifier,
            'sdkappid' => $this->appId,
            'random' => uniqid(),
            'contenttype' => 'json',
            'usersig' => $this->tim->signature->make($this->identifier)
        ];
        $url = $this->composeUrl($event->request->getUrl(), $commonParams);
        $event->request->setUrl($url);
    }

}