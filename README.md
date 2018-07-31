# yii2-tim
适用于Yii2的腾讯云通信

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require xutl/yii2-tim:~3.0
```

or add

```
"xutl/yii2-tim": "~3.0"
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
            'administrator' => 'webmaster',
            'privateKey' => '私钥字符串一行',
            'publicKey' => '公钥字符串一行',
        ],
    ]
];
```

使用
----

```php

/** var Tim $im */
$im = Yii::$app->im->getAccount('test');
$res = $im->kick();
print_r($res);
```
