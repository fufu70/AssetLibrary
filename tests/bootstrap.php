<?php

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../vendor/yiisoft/yii/framework/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';

require_once($yiit);
// require all src files
$files = array_merge(
    glob(dirname(__FILE__).'/../src/*.php'),
    glob(dirname(__FILE__).'/../src/Action/*.php'),
    glob(dirname(__FILE__).'/../src/File/*.php'),
    glob(dirname(__FILE__) . '/../vendor/fufu70/reflection-class/src/*.php'),
    glob(dirname(__FILE__) . '/../vendor/fufu70/file-class/src/*.php'),
    glob(dirname(__FILE__) . '/../vendor/fufu70/file-class/src/file/*.php')
);

foreach ($files as $file) {
    require_once($file);   
}

Yii::createWebApplication($config);