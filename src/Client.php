<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\httpclient\RequestEvent;
use yii\web\ServerErrorHttpException;

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
    private $im = 'im';

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
        if (empty ($this->identifier)) {
            $this->identifier = $this->im->identifier;
        }
        $this->requestConfig['format'] = Client::FORMAT_JSON;
        $this->responseConfig['format'] = Client::FORMAT_JSON;
        $this->on(Client::EVENT_BEFORE_SEND, [$this, 'RequestEvent']);
        $this->on(Client::EVENT_AFTER_SEND, [$this, 'ResponseEvent']);
    }

    /**
     * Composes URL from base URL and GET params.
     * @param string $url base URL.
     * @param array $params GET params.
     * @return string composed URL.
     */
    protected function composeUrl($url, array $params = [])
    {
        if (!empty($params)) {
            if (strpos($url, '?') === false) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= http_build_query($params, '', '&', PHP_QUERY_RFC3986);
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
        $params = [
            'identifier' => $this->identifier,
            'sdkappid' => $this->appId,
            'random' => uniqid(),
            'contenttype' => 'json',
            'usersig' => $this->im->signature->make($this->identifier)
        ];
        $url = $this->composeUrl($event->request->getUrl(), $params);
        $event->request->setUrl($url);
    }

    /**
     * 响应事件
     * @param RequestEvent $event
     * @throws ServerErrorHttpException
     */
    public function ResponseEvent(RequestEvent $event)
    {
        if ($event->response->isOk) {
            if (isset($event->response->data['ActionStatus']) && $event->response->data['ActionStatus'] == 'FAIL') {
                throw new ServerErrorHttpException($event->response->data['ErrorInfo'], $event->response->data['ErrorCode']);
            } else if (isset($event->response->data['ActionStatus']) && $event->response->data['ActionStatus'] == 'OK') {
                //$event->response->setData($event->response->data['QueryResult']);
            }
        }
    }
}