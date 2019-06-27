<?php

declare(strict_types=1);

namespace yii1\tests\framework\web\PsrMessage;

use CController;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmptyResponse;

class ControllerWithoutFilters extends CController
{
    public function actionIndex(): ResponseInterface
    {
        return new EmptyResponse();
    }
}
