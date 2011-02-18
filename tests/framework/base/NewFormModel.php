<?php
class NewFormModel extends CFormModel {
    public function behaviors() {
        return array(
            'newBeforeValidateBehavior' => array(
                'class' => 'NewBeforeValidateBehavior',
            ),
        );
    }
}