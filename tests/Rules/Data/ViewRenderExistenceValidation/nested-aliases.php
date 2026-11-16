<?php

/** @var \yii\base\View $view */

$view->render('@ext/views/main');
$view->render('@ext/gii/views/default/index');
$view->render('@debug/toolbar/views/panel');

$view->render('@ext/gii/views/default/missing');
$view->render('@ext/giix/views/missing');
$view->render('@debug/toolbar/views/missing');
$view->render('@debug/views/missing');
