<?php

namespace is\Masters\Drivers;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

use is\Masters\Drivers\Master\Errors;

abstract class Master extends Errors {
	
	/*
	это фактически интерфейс драйвера
	
	работаем с подготовленными запросами
	пока это происходит так
	заполняем данные в публичные свойства
	затем остается вызвать метод launch, который сформирует эти данные в готовый запрос, записав его в строку prepare
	и по этой строке соединится с базой данных
	возвращенные данные будут записаны в массив $data
	
	идея прав такова, что доступ будет назначаться только к тем полям и записям базы, которые разрешены
	но это будет происходить снаружи, т.е. не в драйвере
	то же самое касается фильтрации, сортировки и обрезки значений
	кстати, возвращенные данные должны быть перенесены и записаны в коллекцию
	
	чтение - здесь все понятно
	запись - имеется ввиду запись и перезапись существующих
	добавление - только новая запись, если такая запись уже есть, то она не перезаписывается
	удаление - здесь тоже все понятно
	
	чтобы создать и подключить свой собственный драйвер, нужно создать класс, наследующий данный класс
	и поместить его в пространство имен is\Masters\Drivers
	подключить данный файл (или поместить его в папку фреймворка, что не рекомендуется)
	а затем, если вы работаете с ядром, проинициализировать его в настройках ядра
	
	в дальнейшем мы добавим классы по работе через PDO и, возможно, подключим сторонние библиотеки в проект
	
	protected $prepare;
	protected $settings;
	
	настройка 'all' => true/false в 'settings'
	разрешает чтение ВСЕХ полей, даже запрещенных
	это должно быть особенно актуально для администраторов, где нужно видеть ВСЕ записи
	
	public $query; // тип запроса в базу данных - чтение, запись, добавление, удаление
	public $collection; // раздел базы данных
	
	public $id; // имя или имена записей в базе данных
	public $name; // имя или имена записей в базе данных
	public $type; // тип или типы записей в базе данных
	public $parents; // родитель или родители записей в базе данных
	public $owner; // владелец или владельцы записей в базе данных
	
	public $ctime; // дата и время (в формате unix) создания записи в базе данных
	public $mtime; // дата и время (в формате unix) последнего изменения записи в базе данных
	public $dtime; // дата и время (в формате unix) удаления записи в базе данных
	
	public $limit; // установить возвращаемое количество записей в базе данных
	*/
	
	abstract public function connect();
	abstract public function close();
	abstract public function read();
	//abstract public function write($data);
	//abstract public function create($data);
	//abstract public function delete($data);
	//abstract public function match($data); // проверка существования записи
	//abstract public function backup($data); // сохранение резервной копии базы данных или отдельных разделов
	//abstract public function restore($data); // восстановление базы данных или отдельных разделов из резервной копии
	
	public function launch() {
		
		if (!$this -> collection) {
			return;
		}
		
		$this -> rights_query = $this -> setRights();
		
		if ($this -> query === 'read') {
			$this -> prepareRead();
			//$this -> read();
		}
		
		if ($this -> query === 'write') {
			$this -> prepareWrite();
			// данные для записи берутся из массива $this -> data
			// где каждое значение - одна запись, но не объект entry, а массив
			// не создает новую запись, а изменяет существующую
			// не работает без данных
			//$this -> write();
		}
		
		if ($this -> query === 'create') {
			$this -> prepareCreate();
			// данные для записи берутся из массива $this -> data
			// где каждое значение - одна запись, но не объект entry, а массив
			// создает новую запись, существующие не трогает
			// работает как с данными, так и без данных
			//$this -> create();
		}
		
		if ($this -> query === 'delete') {
			$this -> prepareDelete();
			// удаляет существующие записи
			// работает только без данных
			//$this -> delete();
		}
		
		// ЕЩЕ НУЖНО СДЕЛАТЬ ФИЛЬТРАЦИЮ И ОТБОР ПО УКАЗАННЫМ QUERY ДАННЫМ
		// ЕЩЕ НУЖНО createFileFromInfo
		// ДЛЯ ПОДГОТОВКИ ФАЙЛА К ЗАПИСИ
		
		//echo $name . '<br>';
		//echo print_r($query, 1) . '<br>';
		//echo '<pre>';
		//echo print_r($prepared, 1) . '<br>';
		//echo '</pre>';
		
	}
	
	public function prepareRead() {
		
		$this -> hash();
		$this -> resetData();
		
		if ($this -> cache) {
			$this -> readCache();
		}
		
		if (!$this -> cached) {
			$this -> read();
			if ($this -> cache) {
				$this -> writeCache();
			}
		}
		
		if (!is_array($this -> data)) {
			$this -> resetData();
		}
		
	}
	
	public function prepareWrite() {
		
		$this -> prepareItem();
		
		if (
			!$this -> data ||
			!$this -> match()
		) {
			return;
		}
		
		if ($this -> write()) {
			$this -> data = null;
		} else {
			$this -> setError($this -> data['name']);
			// logging('error write item name [' . item['name'] . '] with parents [' . Strings::join($item['parents'], ':') . '] to collection [' . $this -> collection . ']');
		}
		
	}
	
	public function prepareCreate() {
		
		$this -> prepareItem();
		
		if (
			!$this -> data ||
			$this -> match()
		) {
			return;
		}
		
		if ($this -> create()) {
			$this -> data = null;
		} else {
			$this -> setError($this -> data['name']);
			// logging('error write item name [' . item['name'] . '] with parents [' . Strings::join($item['parents'], ':') . '] to collection [' . $this -> collection . ']');
		}
		
	}
	
	public function prepareDelete() {
		
		$this -> prepareItem();
		
		if (
			!$this -> data ||
			!$this -> match()
		) {
			return;
		}
		
		if ($this -> delete()) {
			$this -> data = null;
		} else {
			$this -> setError($this -> data['name']);
			// logging('error write item name [' . item['name'] . '] with parents [' . Strings::join($item['parents'], ':') . '] to collection [' . $this -> collection . ']');
		}
		
	}
	
	public function prepareItem() {
		
		$item = $this -> data;
		$this -> data = null;
		
		if (!System::typeIterable($item)) {
			return;
		}
		
		if (System::type($item, 'object')) {
			$item = json_decode(json_encode($item), true);
		}
		
		// если нет имени, то мы ничего не сможем сделать с этой записью
		
		if (!$item['name']) {
			return;
		}
		
		// проверка прав
		// и результат
		
		$this -> data = $this -> verify($item);
		
	}
	
}

?>