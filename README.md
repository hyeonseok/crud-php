# crud-sqlite3

Simple crud library on Sqlite3. Highly portable, no need for initial setting.

## usage

	require_once('Crud.class.php');
	$article = new Crud('article');

This will create table for article.

	$id = $article->create(array(
		'title' => '...',
		'body' => '...',
		'date' => '...'
	));

One article is created and id number is returned.

	$article_data = $article->read($id);

You can access created data with id.

	$article_list = $article->read();

Get all articles.

	$article->update(array(
		'author' => '...'
	));

You can add additional column later.

	$article->delete($id);

Article is deleted.

	$comment = new Crud('comment');

Create new table as many as you want.

You can specify database filename when you create instance. If the filename is missing, it will create '.ht.sqlite3.db' at the same location of library file.

	$log = new Crud('log', '../log.db');

## license

MIT.
