<?php

// Following array is being used in CHtmlTest::providerValue() method, but it's moved here into separate file because
// PHP 5.2 cannot parse lambda function declarations, even if such code won't be executed at all. This is the only
// workaround to this PHP peculiarity (there are no opportunities to control PHP parser statically a.k.a.
// "compile"-time control).

return array(
	array(array('k1'=>'v1','k2'=>'v2','v3','v4'),function($model) { return $model['k2']; },null,'v2'),
	array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),function($model) { return $model->k2; },null,'v2'),
);
