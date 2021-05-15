PHP DataSaver
=============

# Description

You can easily define a MySQL table structure and fill up with data. This is great for simple scripts that need to save data quickly

# Build status

![Build Status](https://api.travis-ci.com/sebaperez/phpdatasaver.svg?branch=master&status=created)

# Usage

Simply import the **DataSaver\Table** module


## Table definition ##

```
$table = \DataSaver\Table::model([
	"db" => $dbName,
	"name" => $tableName,
	"columns" => [
		[ "name" => $key, "type" => "int", "options" => "auto_increment primary key" ],
		[ "name" => $name, "type" => "varchar(255)" ]
	]
]);
```

It will create a both a new database and table if not exists


## Insert data into the table ##

```
$table->insert([ "key" => "value" ]);
```

## Retrieve data from table ##

```
$table->select("key1, key2 where key1 > 1");
```

It uses a **pseudo query**. As the script already knows the table you are refering to, you can omit the "select" and "from table" from the query.
You cannot do joins or other kind of operations between tables. It will return an associative array with all matches.
