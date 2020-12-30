<?php

declare(strict_types=1);

namespace yii1\tests\framework\web\PsrMessage;

use CController;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;

class ControllerWithoutFilters extends CController
{
    public function actionIndex(): ResponseInterface
    {
        return new EmptyResponse();
    }
}
