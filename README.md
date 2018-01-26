# yii2-tim
适用于Yii2的腾讯云通信

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require xutl/yii2-tim:~1.0
```

or add

```
"xutl/yii2-tim": "~1.0"
```

to the `require` section of your `composer.json` file.

配置
----

To use this extension, you have to configure the Connection class in your application configuration:

```php
return [
    //....
    'components' => [
        'im' => [
            'class' => 'xutl\tim\Tim',
            'appId' => '123456',
            'accountType' => '123456',
            'identifier' => 'webmaster',
            'privateKey' => '@common/keys/im_private.key',
            'publicKey' => '@common/keys/im_public.key',
        ],
    ]
];
```

使用
----

```php

/** var Tim $im */
$im = Yii::$app->im->getAccount();
$res = $im->import('被导入的账号');
print_r($res);
```