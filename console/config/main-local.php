<?php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'user' => [
            'class' => 'common\models\User',
        ],
    ],
];
