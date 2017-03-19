<?php

namespace site_demo\entry\dev\controllers;

use lib\models\demo\domain\TbDemo;

class SiteController extends \CController
{
    public function actionIndex()
    {
        $arrDemo = TbDemo::model()->findAll(array(
            'select' => '*, FORMAT((num_a+num_b+num_c)/3, 2) as $$avg'
        ));

        $stat = TbDemo::model()->find(array(
            'select' => 'min(num_a) as $$num_a_min'
                        .',max(num_a) as $$num_a_max'
                        .',min(num_b) as $$num_b_min'
                        .',max(num_b) as $$num_b_max'
                        .',min(num_c) as $$num_c_min'
                        .',max(num_c) as $$num_c_max'
                        .',FORMAT((min(num_a)+min(num_b)+min(num_c))/3,2) as $$min_avg'
                        .',FORMAT((max(num_a)+max(num_b)+max(num_c))/3,2) as $$max_avg',
        ));

        $this->render('index', array(
            'arrDemo' => $arrDemo,
            'stat'    => $stat,
        ));
    }
}
