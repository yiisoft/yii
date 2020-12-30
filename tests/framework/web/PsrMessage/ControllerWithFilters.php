<?php

declare(strict_types=1);

namespace yii1\tests\framework\web\PsrMessage;

use CController;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;

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
