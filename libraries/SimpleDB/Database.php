<?php

namespace SimpleDB;

class Database
{
	protected $separator = ';';
	protected $types = ['int', 'str', 'date', 'bool'];

	private $data;
	private $primaryKey;
	private $headers;
	private $container;
	private $totalColumn;
	private $totalRow;
	private $affectedRows;

	public function __construct($data, $primaryKey = null)
	{
		$this->data = $data;
		$this->primaryKey = $primaryKey;
		$this->read();
	}

	public function __destruct()
	{
	}

	public function create($fields = [])
	{
		if (!\is_array($fields)) {
			return;
		}

		$this->headers = [];
		$this->container = [];

		foreach ($fields as $field => $type) {
			if (!\in_array($type, $this->types)) {
				return;
			}

			$this->headers[$field] = $type;
		}

		return $this->commit();
	}

	public function insert($data)
	{
		$newItem = [];

		foreach ($this->headers as $name => $type) {
			$newItem[$name] = (isset($data[$name])) ? $data[$name] : '';
		}

		if ($this->primaryKey != null) {
			// Primary key auto increment
			if (!isset($data[$this->primaryKey])) {
				$newItem[$this->primaryKey] = 1;

				foreach ($this->container as $row) {
					if ($row[$this->primaryKey] >= $newItem[$this->primaryKey]) {
						$newItem[$this->primaryKey] = $row[$this->primaryKey] + 1;
					}
				}
			}
			// Prevent duplicated primary key
			else {
				foreach ($this->container as $row) {
					if ($row[$this->primaryKey] == $data[$this->primaryKey]) {
						return $this->commit();
					}
				}
			}
		}

		array_push($this->container, $newItem);

		return $this->commit();
	}

	public function select($column = '*', $needle = '*', $orderBy = '', $sort = 'asc')
	{
		$this->affectedRows = $this->totalRow;

		if (\in_array($orderBy, array_keys($this->headers))) {
			$this->container = $this->sort($this->container, $orderBy, $sort);
		}

		if ($needle == '*') {
			return $this->container;
		}

		$result = [];

		if ($column == '*') {
			foreach ($this->container as $row) {
				if (preg_match('/' . $this->getNeedle($needle) . '/i', implode('', $row))) {
					array_push($result, $row);
				}
			}
		} else {
			foreach ($this->container as $row) {
				if (preg_match('/' . $this->getNeedle($needle) . '/i', $row[$column])) {
					array_push($result, $row);
				}
			}
		}

		$this->affectedRows = \count($result);

		return $result;
	}

	public function update($column, $needle, $data)
	{
		$this->affectedRows = 0;

		for ($i = 0; $i < $this->totalRow; ++$i) {
			if (isset($this->container[$i][$column]) && preg_match('/' . $this->getNeedle($needle) . '/i', $this->container[$i][$column])) {
				++$this->affectedRows;

				foreach ($this->headers as $name => $type) {
					if (isset($data[$name])) {
						$this->container[$i][$name] = $data[$name];
					}
				}
			}
		}

		return $this->commit();
	}

	public function delete($column, $needle)
	{
		$tmp = [];

		for ($i = 0; $i < $this->totalRow; ++$i) {
			if (!isset($this->container[$i][$column]) || !preg_match('/' . $this->getNeedle($needle) . '/i', $this->container[$i][$column])) {
				array_push($tmp, $this->container[$i]);
			}
		}

		$this->affectedRows = \count($this->container) - \count($tmp);
		$this->container = $tmp;

		return $this->commit();
	}

	public function affectedRows()
	{
		return $this->affectedRows;
	}

	public function sort($array, $index, $order = 'asc')
	{
		if (\is_array($array) && \count($array) > 0) {
			foreach (array_keys($array) as $key) {
				$temp[$key] = $array[$key][$index];
			}

			($order == 'asc') ? asort($temp) : arsort($temp);

			foreach (array_keys($temp) as $key) {
				(is_numeric($key)) ? $sorted[] = $array[$key] : $sorted[$key] = $array[$key];
			}

			return $sorted;
		}

		return $array;
	}

	public function getLastId()
	{
		if (empty($this->container)) {
			return 0;
		}

		return $this->container[$this->totalRow - 1][$this->primaryKey];
	}

	private function getNeedle($s)
	{
		if (substr($s, 0, 1) == '=') {
			return '^' . preg_replace('/([.+^$?\[\]])/', '\\\$1', substr($s, 1)) . '$';
		}

		return $s;
	}

	private function parse($value, $type)
	{
		switch ($type) {
			case 'int':
				if (!preg_match('/^[0-9]+$/', $value)) {
					return 0;
				}
				break;

			case 'date':
				if (strtoupper($value) == 'NOW()') {
					return date('Y-m-d H:i:s');
				}

				if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
					return null;
				}
				break;

			case 'bool':
				return ($value) ? 'yes' : 'no';
		}

		return $value;
	}

	private function cast($value, $type)
	{
		switch ($type) {
			case 'int':
				return (int) $value;

			case 'bool':
				return (strtoupper($value) == 'YES') ? true : false;
		}

		return $value;
	}

	private function read()
	{
		if (!is_writable($this->data)) {
			return;
		}

		$this->container = [];
		$item = [];

		$fp = fopen($this->data, 'r');

		if ($fp) {
			$headers = fgetcsv($fp, 2048, $this->separator);
			$this->totalColumn = \count($headers);

			$this->headers = [];

			foreach ($headers as $column) {
				$this->headers[substr($column, 0, strpos($column, '('))] = substr($column, strpos($column, '(') + 1, -1);
			}

			$keys = array_keys($this->headers);

			while ($buffer = fgetcsv($fp, 2048, $this->separator)) {
				for ($i = 0; $i < $this->totalColumn; ++$i) {
					$item[$keys[$i]] = $this->cast(str_replace('\\"', '"', $buffer[$i]), $this->headers[$keys[$i]]);
				}

				array_push($this->container, $item);
			}
			fclose($fp);
		}

		$this->totalRow = \count($this->container);
	}

	private function commit()
	{
		reset($this->container);

		$keys = array_keys($this->headers);

		$out = '';

		foreach ($this->headers as $name => $type) {
			$out .= $name . '(' . $type . ')' . $this->separator;
		}

		$out = rtrim($out, $this->separator) . "\n";

		if (!empty($this->container)) {
			foreach ($this->container as $row) {
				$item = [];

				for ($i = 0; $i < $this->totalColumn; ++$i) {
					$item[$keys[$i]] = str_replace('"', '\\"', $this->parse($row[$keys[$i]], $this->headers[$keys[$i]]));
				}

				$out .= '"' . implode('"' . $this->separator . '"', $item) . "\"\n";
			}
		}

		if ($this->primaryKey) {
			$this->container = $this->sort($this->container, $this->primaryKey);
		}

		$this->totalRow = \count($this->container);

		return $this->write($out);
	}

	private function write($s)
	{
		$fp = @fopen($this->data, 'w');

		if ($fp) {
			flock($fp, 2);
			fwrite($fp, $s);
			flock($fp, 3);
			fclose($fp);

			return true;
		}

		return false;
	}
}
