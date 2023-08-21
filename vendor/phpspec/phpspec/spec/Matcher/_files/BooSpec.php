<?php

if (!defined ('DESCRIBE_BOO')) {
	define('DESCRIBE_BOO', 'describeBoo');
	
	class DescribeBoo extends \PHPSpec\Context {
		public function itShouldBeTrue() {
		    $x = 'just so that it is not empty';
		}
		public function itShouldBeFalse() {
		    $x = 'just so that it is not empty';
		}
	}
}

