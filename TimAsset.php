<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim;

use yii\web\AssetBundle;

/**
 * Class TimAsset
 * @package xutl\tim
 */
class TimAsset extends AssetBundle
{
    public $sourcePath = '@xutl/tim/assets';

    public $css = [

    ];

    public $js = [
        'js/json2.min.js',
        'js/webim.js',
        'js/im_base.js',
        'js/im_group_notice.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}