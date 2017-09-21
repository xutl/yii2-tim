<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\web\NotFoundHttpException;

/**
 * Class ImCallbackAction
 * @package xutl\tim
 */
class ImCallbackAction extends Action
{
    /**
     * @var callable success callback with signature: `function($_GET, $POST)`
     */
    public $onComplete;

    /**
     * 客户端平台
     * @var string RESTAPI、Web、Android、iOS、Windows、Mac、Unkown。
     */
    public $optPlatform;

    /**
     * Initializes the action and ensures the temp path exists.
     */
    public function init()
    {
        parent::init();
        $this->controller->enableCsrfValidation = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
    }

    /**
     * @param integer $SdkAppid APP在云通信中分配到的ID
     * @param string $CallbackCommand 回调命令字
     * @param string $contenttype 固定为json
     * @param string $ClientIP 客户端IP地址
     * @param string $OptPlatform 客户端平台。对应不同的平台类型，可能的取值有：
     * RESTAPI（使用REST API发送请求）、Web（使用Web SDK发送请求）、
     * Android、iOS、Windows、Mac、Unkown（使用未知类型的设备发送请求）。
     * @return array
     */
    public function run($SdkAppid, $CallbackCommand, $contenttype, $ClientIP, $OptPlatform)
    {
        if ($SdkAppid != Yii::$app->im->appId) {
            return ['ActionStatus' => 'Error', 'ErrorInfo' => 'Appid is not correct', 'ErrorCode' => 404];
        }
        if ($this->onComplete && (call_user_func($this->onComplete, Yii::$app->request->get(), Yii::$app->request->getBodyParams())) != false) {
            return ['ActionStatus' => 'OK', 'ErrorInfo' => '', 'ErrorCode' => 0];
        } else {
            return ['ActionStatus' => 'Error', 'ErrorInfo' => 'Server Error.', 'ErrorCode' => 500];
        }
    }
}