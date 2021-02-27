<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Local as LocalFunctions;
use is\Model\Components\Local as LocalComponent;
use is\Parents\Data;

class Content extends LocalComponent {
	
	public $content;
	
	public function getContent() {
		return $this -> data['content'];
	}
	
	public function setContent() {
		$this -> content = LocalFunctions::readFile($this -> data['file']);
	}
	
	public function parseContent() {
		$this -> content = Parser::fromJson($this -> content);
	}
	
	public function addContent($content = null) {
		//$this -> setData($content ? $content : $this -> content, 'content');
		$this -> setData('content', $content ? $content : $this -> content);
	}
	
	public function joinContent() {
		$this -> mergeData(['content' => $this -> content], true);
	}
	
	public function resetContent() {
		$this -> content = null;
	}
	
	public function readContent() {
		$this -> setContent();
		$this -> parseContent();
		$this -> joinContent();
		$this -> resetContent();
	}
	
}

?>