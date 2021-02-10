<?php

namespace is\Model\Data;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Local as LocalFunctions;
use is\Model\Components\Local;
use is\Parents\Data;

class LocalData extends Data {
	
	private $local;
	
	public function __construct(Local $instance) {
		$this -> parseData($instance);
	}
	
	public function parseData(Local $instance) {
		$this -> parse($instance);
		$this -> data = $this -> local -> data['content'];
	}
	
	public function joinData(Local $instance) {
		$this -> parse($instance);
		$this -> mergeData($this -> local -> data['content'], true);
	}
	
	private function parse(Local $instance) {
		
		$this -> local = $instance;
		
		if (!empty($this -> local -> data['file'])) {
			$this -> local -> setContent();
			$this -> local -> data['content'] = Parser::fromJson($this -> local -> data['content']);
		} else {
			$this -> local -> data['content'] = [];
		}
		
	}
	
}

?>