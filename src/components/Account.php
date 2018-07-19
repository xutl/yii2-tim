<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim\components;

use xutl\tim\BaseClient;

/**
 * Class Account
 * @package xutl\tim\components
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 1.0
 */
class Account extends BaseClient
{

    /**
     * 独立模式账号导入接口
     * @param string $identifier 用户名，长度不超过 32 字节
     * @param string $nickname 用户昵称
     * @param string $faceUrl 用户头像URL。
     * @param integer $type 帐号类型，开发者默认无需填写，值0表示普通帐号，1表示机器人帐号。
     * @return mixed
     */
    public function import($identifier, $nickname = null, $faceUrl = null, $type = 0)
    {
        $params = [
            'Identifier' => strval($identifier),
            'Type' => $type
        ];
        if (!is_null($nickname)) $params['Nick'] = $nickname;
        if (!is_null($faceUrl)) $params['FaceUrl'] = $faceUrl;
        return $this->sendRequest('im_open_login_svc/account_import', $params);
    }

    /**
     * 批量导入账户
     * @param array $accounts 用户名，单个用户名长度不超过 32 字节，单次最多导入100个用户名
     * @return mixed
     */
    public function multiImport(array $accounts)
    {
        return $this->sendRequest('im_open_login_svc/multiaccount_import', [
            'Accounts' => $accounts,
        ]);
    }

    /**
     * 帐号登录态失效接口
     * @param string $identifier 用户名
     * @return mixed
     */
    public function kick($identifier)
    {
        return $this->sendRequest('im_open_login_svc/kick', [
            'Identifier' => strval($identifier),
        ]);
    }

    /**
     * 获取账户在线状态
     * @param array|string $accounts
     * @return mixed
     */
    public function state($accounts)
    {
        if (!is_array($accounts)) {
            $accounts = [strval($accounts)];
        }
        return $this->sendRequest('openim/querystate', [
            'To_Account' => $accounts,
        ]);
    }

	/**
     * 资料设置接口
     * @param string $identifier 用户名
     * @return mixed
     */
    public function profileSet($identifier, $items)
    {
        return $this->sendRequest('profile/portrait_set', [
            'From_Account' => strval($identifier),
            'ProfileItem' => $items,
        ]);
    }
}