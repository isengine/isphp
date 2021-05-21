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
	public $area;
	public $styles;
	public $link;
	public $custom;
	public $content;
	
	private $settings;
	private $print;
	
	public function __construct($data, $print = null) {
		
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
				'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'
			],
			'hreflink' => [
				'area', 'a', 'base', 'link'
			],
			'allow' => [
				'tag', 'classes', 'id', 'data', 'area', 'styles', 'link', 'custom', 'content'
			],
			'string' => [
				'tag', 'id', 'link', 'content'
			],
			'keys' => [
				'data', 'area', 'styles', 'custom'
			]
		];
		
		if ($print) {
			$this -> print();
		}
		
	}
	
	public function reset() {
		foreach ($this -> settings['allow'] as $item) {
			if ($item !== 'tag') {
				$this -> $item = null;
			}
		}
		unset($item);
	}
	
	public function print() {
		$this -> open();
		$this -> content();
		$this -> close();
	}
	
	public function get() {
		return $this -> open(true) . $this -> content(true) . $this -> close(true);
	}
	
	public function open($return = null) {
		
		$print = '<' . $this -> tag;
		
		if ($this -> link) {
			$print .= ' ' . (Objects::match($this -> settings['hreflink'], $this -> tag) ? 'href' : 'src') . '="' . $this -> link . '"';
		}
		
		if ($this -> id) {
			$print .= ' id="' . $this -> id . '"';
		}
		
		if (System::typeIterable($this -> classes)) {
			$print .= ' class="' . Strings::join($this -> classes, ' ') . '"';
		}
		
		if (System::typeIterable($this -> data)) {
			$print .= Strings::combine($this -> data, '" data-', '="', ' data-', '"');
		}
		
		if (System::typeIterable($this -> area)) {
			$print .= Strings::combine($this -> area, '" area-', '="', ' area-', '"');
		}
		
		if (System::typeIterable($this -> styles)) {
			$print .= Strings::combine($this -> styles, ';', ':', ' style="', '"');
		}
		
		if (System::typeIterable($this -> custom)) {
			$print .= Strings::combine($this -> custom, '" ', '="', ' ', '"');
		}
		
		$print .= '>';
		
		if ($return) {
			return $print;
		}
		
		echo $print;
		unset($print);
		
	}
	
	public function close($return = null) {
		
		$print = null;
		
		if (!Objects::match($this -> settings['autoclose'], $this -> tag)) {
			$print = '</' . $this -> tag . '>';
		}
		
		if ($return) {
			return $print;
		}
		
		echo $print;
		unset($print);
		
	}
	
	public function content($return = null) {
		
		$print = $this -> content;
		
		if ($return) {
			return $print;
		}
		
		echo $print;
		unset($print);
		
	}
	
	public function add($name, $first = null, $second = null) {
		
		if (!Objects::match($this -> settings['allow'], $name)) {
			return;
		}
		
		$key = $second ? $first : null;
		$data = $key ? $second : $first;
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
	
	public function addArea($first, $second = null) {
		$this -> add('area', $first, $second);
	}
	
	public function addStyle($first, $second = null) {
		$this -> add('styles', $first, $second);
	}
	
	public function addLink($data) {
		$this -> add('link', $data);
	}
	
	public function addCustom($first, $second = null) {
		$this -> add('custom', $first, $second);
	}
	
	public function addContent($data) {
		$this -> add('content', $data);
	}
	
}

?>