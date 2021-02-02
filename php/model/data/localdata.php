<?php

namespace is\Model\Data;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Local as LocalFunctions;

use is\Parents;

class LocalData extends Parents\Data {
	
	private $local;
	
	public function __construct(Parents\Local $instance) {
		$this -> localData($instance);
	}
	
	public function localData(Parents\Local $instance) {
		$this -> local = $instance;
		$this -> parse();
		$this -> data = $this -> local -> data['content'];
	}
	
	public function joinData(Parents\Local $instance) {
		$this -> local = $instance;
		$this -> parse();
		$this -> mergeData($this -> local -> data['content']);
	}
	
	private function parse() {
		if (!empty($this -> local -> data['file'])) {
			$this -> local -> setContent();
			$this -> local -> data['content'] = Parser::fromJson($this -> local -> data['content']);
		} else {
			$this -> local -> data['content'] = [];
		}
	}
	
}

?>