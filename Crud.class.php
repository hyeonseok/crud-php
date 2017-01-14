<?php
class Crud {
	public function __construct($table, $filename = null) {
		if ($filename === null) {
			$filename = dirname(__FILE__) . '/Crud.sqlite3.db';
		}
		$this->db = new Sqlite3($filename);
		$this->table = $table;
		$query = "SELECT name FROM sqlite_master WHERE type='table' AND name='" . $this->table . "';";
		$result = $this->db->query($query);
		if ($result->fetchArray() !== false) {
			$this->table_exists = true;
		}
	}

	private function create_table($data) {
		$keys = array_keys($data);
		$keys_type_added = array_map(function($key) {
			return $key . ' TEXT';
		}, $keys);
		$keys_imploded = implode(', ', $keys_type_added);
		$this->db->exec('CREATE TABLE ' . $this->table . ' (id INTEGER PRIMARY KEY, time INTEGER, ' . $keys_imploded . ')');
		$this->table_exists = true;
	}

	private function add_column_if_not_exists($keys) {
		if (!isset($this->columns)) {
			$result = $this->db->query('PRAGMA table_info(' . $this->table . ')');
			$this->columns = array();
			while ($column = $result->fetchArray()) {
				$this->columns[] = $column['name'];
			}
		}
		foreach ($keys as $key) {
			if (in_array($key, $this->columns)) {
				continue;
			}
			$this->db->exec('ALTER TABLE ' . $this->table . ' ADD COLUMN ' . $key . ' TEXT');
			$this->columns[] = $key;
		}
	}

	private function read_by_search($column, $value) {
		if (!$this->table_exists) {
			return array();
		}
		if ($stmt = $this->db->prepare('SELECT * FROM ' . $this->table . ' ORDER BY time DESC WHERE ' . $column . ' LIKE :value')) {
			$stmt->bindValue(':value', '%' . $value . '%', SQLITE3_TEXT);
			$records = $stmt->execute();
		} else {
			die('Cound not find.' . PHP_EOL);
		}
		$result = array();
		while($record = $records->fetchArray(SQLITE3_ASSOC)) {
			$result[] = $record;
		}
		return $result;
	}

	private function read_by_id($id) {
		if (!$this->table_exists) {
			return array();
		}
		if ($stmt = $this->db->prepare('SELECT * FROM ' . $this->table . ' ORDER BY time DESC WHERE id = :id')) {
			$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
			$record = $stmt->execute();
		} else {
			die('Cound not find.' . PHP_EOL);
		}
		return $record->fetchArray(SQLITE3_ASSOC);
	}

	private function read_all() {
		if (!$this->table_exists) {
			return array();
		}
		if ($stmt = $this->db->prepare('SELECT * FROM ' . $this->table . ' ORDER BY time DESC')) {
			$records = $stmt->execute();
		} else {
			die('Cound not find.' . PHP_EOL);
		}
		$result = array();
		while($record = $records->fetchArray(SQLITE3_ASSOC)) {
			$result[] = $record;
		}
		return $result;
	}

	public function create($data) {
		if (!isset($this->table_exists)) {
			$this->create_table($data);
		}
		$this->add_column_if_not_exists(array_keys($data));
		$keys = array_keys($data);
		$keys_colon_added = array_map(function($key) {
			return ':' . $key;
		}, $keys);
		$keys_imploded = implode(', ', $keys_colon_added);
		if ($stmt = $this->db->prepare('INSERT INTO ' . $this->table . ' (time, ' . implode(', ', $keys) . ') VALUES (' . time() . ', ' . $keys_imploded . ')')) {
			foreach ($data as $key => $value) {
				$stmt->bindValue(':' . $key, $value, SQLITE3_TEXT);
			}
			$stmt->execute();
			$id = $this->db->lastInsertRowID();
		} else {
			die('Could not create.' . PHP_EOL);
		}
		return $id;
	}

	public function read($arg1 = null, $arg2 = null) {
		if ($arg2 != null) {
			$result = $this->read_by_search($arg1, $arg2);
		} else if ($arg1 != null) {
			$result = $this->read_by_id($arg1);
		} else {
			$result = $this->read_all();
		}
		return $result;
	}

	public function update($id, $data) {
		$colums = array();
		foreach (array_keys($data) as $key) {
			$colums[] = $key . ' = :' . $key;
		}
		if ($stmt = $this->db->prepare('UPDATE ' . $this->table . ' SET time = ' . time() . ', ' . implode(', ', $colums) . ' WHERE id = :id')) {
			$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
			foreach ($data as $key => $value) {
				$stmt->bindValue(':' . $key, $value, SQLITE3_TEXT);
			}
			$stmt->execute();
		} else {
			die('Cound not update.' . PHP_EOL);
		}
	}

	public function delete($id) {
		if ($stmt = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id')) {
			$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
			$stmt->execute();
		} else {
			die('Cound not delete.' . PHP_EOL);
		}
	}
}
?>
