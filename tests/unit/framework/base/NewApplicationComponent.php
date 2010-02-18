<?php
class NewApplicationComponent extends CApplicationComponent {
	private $_text=NULL;
	public function getText($text=NULL) {
		if(NULL===$text) {
			return $this->_text;
		}
		return $text;
	}
	public function setText($val) {
		$this->_text = $val;
	}
}
