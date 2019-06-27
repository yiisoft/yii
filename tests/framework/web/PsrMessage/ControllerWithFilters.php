<?php

declare(strict_types=1);

namespace yii1\tests\framework\web\PsrMessage;

use CController;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmptyResponse;

class ControllerWithFilters extends CController
{
    public function filters(): array
    {
        return ['accessControl'];
    }

    public function accessRules(): array
    {
        return [
            ['allow', 'users' => ['*']],
        ];
    }

    public function actionIndex(): ResponseInterface
    {
        return new EmptyResponse();
    }
}
