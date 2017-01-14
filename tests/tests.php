<?php
function tests($actual, $expected) {
    if ($actual === $expected) {
        echo('PASS' . PHP_EOL);
    } else {
        echo('FAIL' . PHP_EOL);
        print_r($actual);
        print_r($expected);
    }
}

$filename = dirname(__FILE__) . '/Crud.sqlite3.db';

if (file_exists($filename)) {
    unlink($filename);
}

require_once('../Crud.class.php');
$crud = new Crud('crud', $filename);

echo('#1 ');
tests(file_exists($filename), true);

echo('#2 ');
tests($crud->create(array(
	'var1' => 'text1',
	'var2' => 'text2',
)), 1);

echo('#3 ');
$record = $crud->read(1);
tests($record['var1'], 'text1');

echo('#4 ');
$crud->update(1, array('var1' => 'text3'));
$record = $crud->read(1);
tests($record['var1'], 'text3');

echo('#5 ');
tests($crud->create(array(
	'var1' => 'text1',
	'var2' => 'text2',
)), 2);

echo('#6 ');
$record = $crud->read();
tests(count($record), 2);

echo('#7 ');
$record = $crud->read('var1', 'text3');
tests($record[0]['id'], 1);

echo('#8 ');
$crud->delete(1);
tests($crud->read(1), false);

echo('#9 ');
$crud2 = new Crud('crud2', $filename);
tests($crud2->create(array('foo' => 'bar')), 1);

echo('#10 ');
$record = $crud->read();
tests(count($record), 1);

echo('#11 ');
$record = $crud->read('var1', 'te');
tests(count($record), 1);

echo('#12 ');
tests($crud2->create(array('bar' => 'foo')), 2);

echo('#13 ');
$read_none_exist_table = new Crud('read_none_exist_table', $filename);
tests($read_none_exist_table->read(), array());

unlink($filename);
?>
