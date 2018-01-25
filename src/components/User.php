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
class User extends Client
{


    public function Import($identifier, $nickname, $faceUrl, $type)
    {
        $response = $this->post('im_open_login_svc/account_import', [
            'Identifier' => $identifier,
            'Nick' => $nickname,
            'FaceUrl' => $faceUrl,
            'Type' => $type,
        ]);
        return $response->data;
    }

    public function MultiImport($accounts = [])
    {
        $response = $this->post('im_open_login_svc/multiaccount_import', [
            'Accounts' => $accounts,
        ]);
        return $response->data;
    }

    public function Kick($identifier)
    {
        $response = $this->post('im_open_login_svc/kick', [
            'Identifier' => $identifier,
        ]);
        return $response->data;
    }
}