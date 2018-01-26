<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim\components;

use xutl\tim\Client;

/**
 * Class User
 * @package xutl\tim\components
 */
class Account extends Client
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
        $response = $this->post('im_open_login_svc/account_import', $params)->send();
        return $response->data;
    }

    /**
     * 批量导入账户
     * @param array $accounts 用户名，单个用户名长度不超过 32 字节，单次最多导入100个用户名
     * @return mixed
     */
    public function multiImport(array $accounts)
    {
        $response = $this->post('im_open_login_svc/multiaccount_import', [
            'Accounts' => $accounts,
        ])->send();
        return $response->data;
    }

    /**
     * 帐号登录态失效接口
     * @param string $identifier 用户名
     * @return mixed
     */
    public function kick($identifier)
    {
        $response = $this->post('im_open_login_svc/kick', [
            'Identifier' => strval($identifier),
        ])->send();
        return $response->data;
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
        $response = $this->post('openim/querystate', [
            'To_Account' => $accounts,
        ])->send();

        return $response->data;
    }
}