<?php
class NewBeforeValidateBehaviorException extends CException {}

class NewBeforeValidateBehavior extends CModelBehavior {
    public function beforeValidate($event) {
        throw new NewBeforeValidateBehaviorException();
    }
}
