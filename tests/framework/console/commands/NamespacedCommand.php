<?php

declare(strict_types=1);

namespace yii1\tests\framework\console\commands;

use CConsoleCommand;

class NamespacedCommand extends CConsoleCommand
{
    public function actionIndex(): void
    {
        echo __CLASS__;
    }
}
