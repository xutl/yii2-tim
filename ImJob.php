<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

/**
 * Class ImJob.
 */
class ImJob extends BaseObject implements RetryableJobInterface
{

    const ACTION_ACCOUNT_CREATE = 'im_open_login_svc/account_import';//创建用户
    const ACTION_ACCOUNT_KICK = 'im_open_login_svc/kick';//T下线
    const ACTION_PORTRAIT_SET = 'profile/portrait_set';//个人资料设置
    const ACTION_GROUP_CREATE = 'group_open_http_svc/create_group';//创建群组
    const ACTION_GROUP_UPDATE = 'group_open_http_svc/modify_group_base_info';//修改群组基础资料
    const ACTION_GROUP_DELETE = 'group_open_http_svc/destroy_group';//解散群组
    const ACTION_GROUP_ADD_MEMBER = 'group_open_http_svc/add_group_member';//增加群组成员
    const ACTION_GROUP_DELETE_MEMBER = 'group_open_http_svc/delete_group_member';//删除群组成员
    const ACTION_GROUP_EDIT_MEMBER = 'group_open_http_svc/modify_group_member_info';

    /**
     * @var string 操作名称
     */
    public $action;

    /**
     * 接口参数
     * @var array
     */
    public $params;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        Yii::$app->im->api($this->action, 'POST', $this->params);
    }

    /**
     * @inheritdoc
     */
    public function getTtr()
    {
        return 60;
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < 3;
    }
}