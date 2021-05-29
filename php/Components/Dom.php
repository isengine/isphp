<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;

// можно добавить textVariables

class Dom {
	
	public $tag;
	public $classes;
	public $id;
	public $data;
	public $aria;
	public $styles;
	public $link;
	public $custom;
	public $content;
	
	private $settings;
	private $print;
	
	public function __construct($data, $display = null) {
		
		$data = Parser::fromString($data);
		
		$this -> tag = $data[0];
		
		if ($data[1]) {
			$this -> classes[] = $data[1];
		}
		if ($data[2]) {
			$this -> id = $data[2];
		}
		
		$this -> settings = [
			'autoclose' => [
				'aria', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'
			],
			'hreflink' => [
				'aria', 'a', 'base', 'link'
			],
			'allow' => [
				'tag', 'classes', 'id', 'data', 'aria', 'styles', 'link', 'custom', 'content'
			],
			'string' => [
				'tag', 'id', 'link', 'content'
			],
			'keys' => [
				'data', 'aria', 'styles', 'custom'
			]
		];
		
		if ($display) {
			$this -> print();
		}
		
	}
	
	public function reset() {
		$this -> print = null;
		foreach ($this -> settings['allow'] as $item) {
			if ($item !== 'tag') {
				$this -> $item = null;
			}
		}
		unset($item);
	}
	
	public function print() {
		$this -> print = null;
		$this -> open();
		$this -> content();
		$this -> close();
		echo $this -> print;
	}
	
	public function get() {
		$this -> print = null;
		$this -> open();
		$this -> content();
		$this -> close();
		return $this -> print;
	}
	
	public function open($display = null) {
		
		if (!$this -> tag) {
			return;
		}
		
		$print = '<' . $this -> tag;
		
		if ($this -> link) {
			$print .= ' ' . (Objects::match($this -> settings['hreflink'], $this -> tag) ? 'href' : 'src') . '="' . $this -> link . '"';
		}
		
		if ($this -> id) {
			$print .= ' id="' . $this -> id . '"';
		}
		
		if (System::typeIterable($this -> classes)) {
			$print .= ' class="' . Strings::except(Strings::join($this -> classes, ' '), '"') . '"';
		}
		
		if (System::typeIterable($this -> data)) {
			$print .= Strings::combineMask($this -> data, ' data-{k}="{i}"', null, null, '"');
		}
		
		if (System::typeIterable($this -> aria)) {
			$print .= Strings::combineMask($this -> aria, ' aria-{k}="{i}"', null, null, '"');
		}
		
		if (System::typeIterable($this -> styles)) {
			$print .= Strings::combineMask($this -> styles, '{k}:{i};', ' style="', '"', '"');
		}
		
		if (System::typeIterable($this -> custom)) {
			$print .= Strings::combineMask($this -> custom, ' {k}="{i}"', null, null, '"');
		}
		
		$print .= '>';
		
		if ($display) {
			echo $print;
		}
		
		$this -> print .= $print;
		return $print;
		unset($print);
		
	}
	
	public function close($display = null) {
		
		if (!$this -> tag) {
			return;
		}
		
		$print = null;
		
		if (!Objects::match($this -> settings['autoclose'], $this -> tag)) {
			$print = '</' . $this -> tag . '>';
		}
		
		if ($display) {
			echo $print;
		}
		
		$this -> print .= $print;
		return $print;
		unset($print);
		
	}
	
	public function content($display = null) {
		
		if (!$this -> tag) {
			return;
		}
		
		$print = $this -> content;
		
		if ($display) {
			echo $print;
		}
		
		$this -> print .= $print;
		return $print;
		unset($print);
		
	}
	
	public function add($name, $first = null, $second = null, $precise = null) {
		
		if (!Objects::match($this -> settings['allow'], $name)) {
			return;
		}
		
		$key = System::set($second) || $precise ? $first : null;
		$data = System::set($key) || $precise ? $second : $first;
		$array = System::typeIterable($data);
		
		if (Objects::match($this -> settings['string'], $name)) {
			
			if ($array) {
				return;
			}
			
			$this -> $name = $data;
			
		} elseif (Objects::match($this -> settings['keys'], $name)) {
			if ($key && !$array) {
				$this -> $name[$key] = $data;
			} elseif (!$key && $array) {
				$this -> $name = Objects::merge($this -> $name ? $this -> $name : [], $data);
			} else {
				return;
			}
			
		} else {
			
			if ($array) {
				$this -> $name = Objects::merge($this -> $name ? $this -> $name : [], $data);
			} else {
				$this -> $name[] = $data;
			}
			
		}
		
	}
	
	public function addTag($data) {
		$this -> add('tag', $data);
	}
	public function addClass($data) {
		$this -> add('classes', $data);
	}
	public function addId($data) {
		$this -> add('id', $data);
	}
	public function addData($first, $second = null) {
		$this -> add('data', $first, $second);
	}
	public function addAria($first, $second = null) {
		$this -> add('aria', $first, $second);
	}
	public function addStyle($first, $second = null) {
		$this -> add('styles', $first, $second);
	}
	public function addLink($data) {
		$this -> add('link', $data);
	}
	public function addCustom($first, $second = null) {
		$this -> add('custom', $first, $second, true);
	}
	public function addContent($data) {
		$this -> add('content', $data);
	}
	
	public function setTag($data) {
		$this -> tag = null;
		$this -> add('tag', $data);
	}
	public function setClass($data) {
		$this -> classes = null;
		$this -> add('classes', $data);
	}
	public function setId($data) {
		$this -> id = null;
		$this -> add('id', $data);
	}
	public function setData($first, $second = null) {
		$this -> data = null;
		$this -> add('data', $first, $second);
	}
	public function setAria($first, $second = null) {
		$this -> aria = null;
		$this -> add('aria', $first, $second);
	}
	public function setStyle($first, $second = null) {
		$this -> styles = null;
		$this -> add('styles', $first, $second);
	}
	public function setLink($data) {
		$this -> link = null;
		$this -> add('link', $data);
	}
	public function setCustom($first, $second = null) {
		$this -> custom = null;
		$this -> add('custom', $first, $second, true);
	}
	public function setContent($data) {
		$this -> content = null;
		$this -> add('content', $data);
	}
	
	public function resetTag() {
		$this -> tag = null;
	}
	public function resetClass() {
		$this -> classes = null;
	}
	public function resetId() {
		$this -> id = null;
	}
	public function resetData() {
		$this -> data = null;
	}
	public function resetAria() {
		$this -> aria = null;
	}
	public function resetStyle() {
		$this -> styles = null;
	}
	public function resetLink() {
		$this -> link = null;
	}
	public function resetCustom() {
		$this -> custom = null;
	}
	public function resetContent() {
		$this -> content = null;
	}
	
	public function getTag() {
		return $this -> tag;
	}
	public function getClass() {
		return $this -> classes;
	}
	public function getId() {
		return $this -> id;
	}
	public function getLink() {
		return $this -> link;
	}
	
	public function leaveFirstClass() {
		$this -> classes = [ Objects::first($this -> classes, 'value') ];
	}
	
}

?>