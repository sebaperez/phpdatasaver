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
		[ "name" => "column1", "type" => "int", "options" => "auto_increment primary key" ],
		[ "name" => "column2", "type" => "varchar(255)" ],
		[ "name" => "column3", "type" => "int" ]
	]
]);
```

It will create both a new database and table if not exist


## Insert data into the table ##

```
$table->insert([ "column2" => "value", "column3" => 2 ]);
```

## Insert or update data into the table ##

```
$table->insertOrUpdate([ "column3" => 1, "column1" => 1 ]);
```

It is mandatory to declare all primary keys in the statement in order to properly execute an update


## Retrieve data from table ##

```
$table->select("column2, column3 where column1 > 1");
```

It uses a **pseudo query**. As this module already knows the table you are refering to, you can omit the "select" and "from table" from the query.
Join or other kind of operations between tables are not supported. It will return an array with all matches as associative arrays.

## Truncate table ##

```
$table->truncate();
```

