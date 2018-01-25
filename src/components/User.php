<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim\components;

use yii\base\Component;

class User extends Component
{


    public function Import($identifier, $nickname, $faceUrl, $type)
    {
        return $this->api('im_open_login_svc/account_import', 'POST', [
            'Identifier' => $identifier,
            'Nick' => $nickname,
            'FaceUrl' => $faceUrl,
            'Type' => $type,
        ]);
    }

    public function MultiImport(){
        return $this->api('im_open_login_svc/multiaccount_import', 'POST', [
            'Accounts' => $accounts,
        ]);
    }

    public function Kick(){
        return $this->api('im_open_login_svc/kick', 'POST', [
            'Identifier' => $identifier,
        ]);
    }
}