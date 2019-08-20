<?php

declare(strict_types=1);

class GlobalNamespacedCommand extends CConsoleCommand
{
    public function actionIndex(): void
    {
        echo __CLASS__;
    }
}
