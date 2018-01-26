<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim\actions;

use Yii;
use yii\base\Action;
use yii\di\Instance;
use yii\web\Response;
use xutl\tim\Tim;

/**
 * Class CallbackAction
 * @package xutl\tim\actions
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 1.0
 */
class CallbackAction extends Action
{
    /**
     * @var callable success callback with signature: `function($_GET, $POST)`
     */
    public $onComplete;

    /**
     * @var string|Tim
     */
    public $im = 'im';

    /**
     * Initializes the action.
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->controller->enableCsrfValidation = false;
        $this->im = Instance::ensure($this->im, Tim::className());
        Yii::$app->response->format = Response::FORMAT_JSON;
    }

    /**
     * 执行回调方法
     * @param string $SdkAppid APP在云通讯申请的Appid
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function run($SdkAppid)
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