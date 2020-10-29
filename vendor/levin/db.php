<?php
/**
 * Класс обертка для Zend_DB
 *
 * В классе реализованы методы для совместимости со старым движком
 * Постепенно от них надо избавляться
 *
 * @author levin
 */
class DB
{
	// Экземпляр класса
	static $instance;

	// Параметры доступа
	public static $host;
	public static $port;
	public static $username;
	public static $password;
	public static $dbname;


	/**
	 * Возвращает ссылку на созданный экземпляр класса DB
	 * Если созданного экземпляра нет - создает
	 *
	 * Реализует паттерн Singleton
	 *
	 * @param $id_language
	 * @return resource
	 */
	public static function get_instance($host = NULL, $port = 3306, $username = NULL, $password = NULL, $dbname = NULL)
	{
		if (!self::$instance)
		{
			self::$host     = $host;
			self::$username = $username;
			self::$password = $password;
			self::$dbname   = $dbname;
			self::$port     = $port;

			self::Connect();
		}

		return self::$instance;
	}
	/**
	 * Устанавливает соединение с базой данных
	 */
	private static function Connect()
	{
		self::$instance = Zend_Db::factory('Pdo_Mysql', array('host'     => self::$host,
															  'port'     => self::$port,
                                                              'username' => self::$username,
                                                              'password' => self::$password,
                                                              'dbname'   => self::$dbname)
                                                            );

            self::$instance->query('SET CHARACTER SET utf8');
            self::$instance->query('SET NAMES utf8');
	}


	/**
	 * Метод проверяет существование таблицы
	 *
	 * @author Levin Pavel
	 * @param $tablename
	 * @return bool
	 */
	static function tableExists($tablename)
	{
		$db = self::get_instance();

		$tables = $db->listTables();

		if (in_array($tablename, $tables))
		{
			return true;
		}

		return false;
	}


	/**
	 * Метод определяет существование столбца в таблице
	 *
	 * @author Levin Pavel
	 * @param $fieldname
	 * @param $tablename
	 * @return boolean
	 */
	static function fieldExists($field, $table)
	{
		$params = array();
		$params['field']  = $field;
		$params['table']  = $table;
		$params['dbname'] = self::$dbname;

		$sql = 'SELECT
				  COUNT(*)
				FROM information_schema.columns
				WHERE table_schema = :dbname
				    AND table_name = :table
				    AND column_name = :field';

		return (bool)(int)DB::executeScalar($sql, $params);
	}


	/**
	 * Возвращает список таблиц в которых присутствует указанное поле
	 *
	 * Если передан список таблиц - то поиск происходит только по ним
	 *
	 * @param string $column
	 * @param array  $tables
	 * @return array
	 */
	static function getTablesWithColumn($column, $tables = array())
	{
		// Если указан список таблиц
		if (count($tables))
		{
			$where_addon = ' AND table_name IN ' . self::prepareForIn($tables);
		}
		else
		{
			$where_addon = '';
		}

		$params = array();
		$params['column'] = $column;
		$params['dbname'] = self::$dbname;

		$sql = 'SELECT
				  table_name
				FROM information_schema.columns
				WHERE table_schema = :dbname
				    AND column_name = :column' . $where_addon;

		return DB::getArrayScalar($sql, $params);
	}


	/**
	 * Изменяет SQL запрос для выборки количества результирующих записей
	 *
	 * Конкретно: выбираемые данные заменяеюся на COUNT(*),
	 * удаляются limit, order
	 *
	 * @author Levin Pavel
	 * @param Zend_Db_Select $select
	 * @return Zend_Db_Select
	 */
	static function prepareItemsCount(Zend_Db_Select $select)
	{
		$select = clone($select);

		$select->reset('columns');
		$select->columns('*');
		$select->reset('order');
		$select->reset('limitcount');
		$select->reset('limitoffset');

		$select = DB::get_instance()->select()->from($select, '')->columns('COUNT(*)');

		//die($select);

		return $select;
	}


	/**
	 * Возвращает количество записей в выборке без учета лимита
	 *
	 * @param Zend_Db_Select $select
	 * @return integer
	 */
	static function getCount(Zend_Db_Select $select)
	{
		if (count($select->getPart('from')) > 0)
		{
			return DB::get_instance()->fetchOne(DB::prepareItemsCount($select));
		}
		else
		{
			return 0;
		}
	}


	/**
	 * Возвращает массив данных
	 * Аналогичен getArrayWithID, кроме того что содержимым каждой записи как и ключом будет первое значение
	 *
	 * @param string $sql
	 * @return array
	 */
	static function getArrayScalar($sql, $params = array())
	{
		$data = self::get_instance()->fetchAssoc($sql, $params);

		foreach ($data as $key => $item)
		{
			$data[$key] = $key;
		}

		return $data;
	}


	/**
	 * Обертка для fetchAssoc
	 *
	 * @author Levin Pavel
	 * @param $sql
	 */
	static function getArrayWithID($sql, $params = array())
	{
		return self::get_instance()->fetchAssoc($sql, $params);
	}



	/**
	 * Обертка для fetchAll
	 */
	static function getArray($sql, $params = array())
	{
		$db = self::get_instance();

		return $db->fetchAll($sql, $params);
	}

    /**
     * выполняет запрос и загонят данные в массив
     */
    static function GetArrayDict($sql, $params=array())
    {
        $db = self::get_instance();
        $stmt = $db->query($sql, $params);
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $data = array();

        foreach($result as $key=>$item)
        {
            $data[$item[0]] = $item[1];
        }

        return $data;
    }


	/**
	 * Обертка для fetchOne
	 *
	 * @param unknown_type $sql
	 */
	static function executeScalar($sql, $params = array())
	{
		return self::get_instance()->fetchOne($sql, $params);
	}


	/**
	 * Обертка для fetchRow
	 *
	 * @param unknown_type $sql
	 */
	static function getRow($sql, $params = array())
	{
		return self::get_instance()->fetchRow($sql, $params);
	}

	/**
	 * Обертка для insert
	 *
	 * @param unknown_type $sql
	 */
	static function InsertQuery($rec, $tablename, $removePKField = true)
	{
		if ($removePKField && isset($rec['id_' . $tablename]))
		{
			unset($rec['id_' . $tablename]);
		}

		self::get_instance()->insert($tablename, $rec);

		return self::get_instance()->lastInsertId();
	}


	/**
	 * Обертка для query
	 *
	 * @param unknown_type $sql
	 */
	static function Query($query, $params=array())
	{
		self::get_instance()->query($query, $params);
	}


	/**
	 * Возвращает имя первичного ключа для таблицы
	 *
	 * @param $tablename
	 * @return string
	 */
	static function getPrimaryKeyName($tablename)
	{
		return DB::get_instance()->fetchOne('SHOW FULL COLUMNS FROM ' . $tablename . ' WHERE `Key` = \'PRI\'');
	}


	/**
	 * Обертка для Update
	 *
	 * @param $rec
	 * @param $table_name
	 * @param $id
	 * @param $condition
	 */
	static function UpdateQuery($rec, $table_name, $id, $condition = '')
	{
		DB::get_instance()->update($table_name, $rec, 'id_' . $table_name . '=' . $id . $condition);
	}


	/**
	 * Обертка для quote
	 *
	 * @param $string
	 */
	static function quote($string)
	{
		return DB::get_instance()->quote($string);
	}


    /**
     * Обновляет вложенные множества в соответствие со списками смежности
     *
     * @param $table            имя таблицы с деревом
     * @param $primary_key		имя первичного ключа с таблицы
     * @param $parent_key       имя родительского ключа
     * @param $id_parent		идентификатор текущей родительской вершины
     * @param $first_free_value идентификатор первого свободного значения (левого или правого)
     * @return integer
     */
    static function updateNestedSet($table, $primary_key, $parent_key = 'id_parent', $id_parent = 0, $first_free_value = 0)
    {
    	if(!is_numeric($id_parent)||!is_numeric($first_free_value))
    	{
    		return false;
    	}

    	$nodes = DB::getArray('select ' . $primary_key . ' from ' . $table . ' where ' . $parent_key . '=' . $id_parent . ' ORDER by sort_ord');

    	for($i = 0; $i < count($nodes); $i++)
    	{
    		$node = $nodes[$i];

    		$k_item = $node[$primary_key];

    		$i_right = self::updateNestedSet($table, $primary_key, $parent_key, $k_item, $first_free_value+1);

    		//echo $i_right . '<hr />';

    		if($i_right === false)
    		{
    			return false;
    		}

    		self::Query("update " . $table . " set i_left=".$first_free_value.",i_right=".$i_right." where " . $primary_key . "=".$k_item);

    		$first_free_value = $i_right+1;
    	}

    	return $first_free_value;
    }


	/**
	* возвращает кусок sql запроса выборки поля (select a, b, FIELDNAME!, c ...
	* в формате конвертации даты в русский вид (dd.mm.yyyy)
	* @param string $fieldName имя поля бд для конвертации
	* @param string $asName имя для добавления в виде '... AS $asName' если null (по умолч.) - не добавляетчя
	* @return string
	*/
	static function DateSQL($fieldName, $asName = null)
	{
        $fieldName = ' DATE_FORMAT(' . $fieldName . ',\'%d.%m.%Y\')';

		if(strlen(trim($asName)))
        {
            $fieldName .= ' AS ' . $asName;
        }

		return $fieldName;
	}


	/**
	* возвращает кусок sql запроса выборки поля (select a, b, FIELDNAME!, c ...
	* в формате конвертации даты в русский вид И ВРЕМЯ (dd.mm.yyyy HH:mm)
	* @param string $fieldName имя поля бд для конвертации
	* @param string $asName имя для добавления в виде '... AS $asName' если null (по умолч.) - не добавляетчя
	* @return string
	*/
	static function DateTimeSQL($fieldName, $asName = null)
	{
		$fieldName = " DATE_FORMAT($fieldName,'%d.%m.%Y %H:%i')";

		if (strlen(trim($asName)))
        {
            $fieldName .= ' AS ' . $asName;
        }

		return $fieldName;
	}


	/**
	 * Подготавливает набор данных для использования в запросе в операторе IN
	 *
	 * @param array $data
	 * @return string
	 */
	static function prepareForIn($data)
	{
		$in = '';

		if (count($data) > 0)
		{
			$in = implode("','", $data);
		}

		return "('" . $in . "')";
	}
}