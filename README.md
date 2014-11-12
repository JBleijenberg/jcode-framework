J!Code Framework
===============


## Database interaction

### Connection configuration
By default J!Code Framework supports MySQL through PDO.<br/>
You can define your database in the ```apllication.json``` file. <br/>
```json
{
    "application": {
        ...
        "database": {
            "adapter": "mysql",
            "host": "127.0.0.1",
            "name": "dbname",
            "user": "username",
            "password": "password"
        },
        ...
    }
}
```


### Setup scripts
J!Code Framework automatically detects setup scripts withing the Setup/ folder of any module.<br/>
Modules are easily upgraded by the versionnumber, set in the config file.

```json
{
    "module": {
        "name": "Jcode_Core",
        "version": "2.0.1",
        "active": true,
        "code": "core"
    }
}
```
When a module is loaded for the first time, the framework checks if there is an installscript present (install-x.x.x.php).<br/>
This is the first setupscript that will run.<br/>

When this script ran successfully, the framework searches for any subsequent upgrade scripts.<br/>
The version number in the installscript will be used to search for upgrades. So if you have an installscript with the name ```install-1.0.0.php```<br/>
the framework will look for an updatescript called ```upgrade-1.0.0-x.x.x.php```, where x.x.x is the next release<br/>
This process will continue to run when x.x.x matches the version number set in your module.json.

If setupscripts are added with versionnumbers higher than the version set in your config.json, nothing will happen until you edit your config.json to a higher versionnumber.

By default the class ```\Jcode\Application\Model\Setup``` is used.<br/>
You can set an alternative class, by defining it in your config.json file:
```json
{
    "module": {
        "name": "Jcode_Core",
        "version": "2.0.1",
        "active": true,
        "code": "core",
        "setup": {
          "class": "Manufacturer\\Module\\Model\\SetupClass"
        }
    }
}
```
By extending the default setup class, your scripts will run normally but you can add your own methods to run during setup of your module.

#### Adding tables
Within the setupscripts you are able to create tables on the fly.
```php
use \Jcode\Db\Adapter\Mysql\Table\Column as Column;

$adapter = $this->getAdapter();
$table = $adapter->getTable('test_table');

$table->addColumn('id', Column::TYPE_INT, 11, [
    'primary' => true,
    'comment' => 'Increment ID',
    'unsigned' => true,
    'not_null' => true,
]);

$table->addColumn('some_value', Column::TYPE_TEXT, null, [
    'comment' => 'Contains some value',
    'not_null' => true,
]);

$adapter->createTable($table);
```


```addColumn()``` requires 4 parameters:
<ul>
<li> Column name (string) </li>
<li> Clolumn Type as defined in \Jcode\Db\Adapter\Mysql\Db\Table\Column(string) </li>
<li> Length (int|null)</li>
<li> Options (primary, default, comment, zerofill, unsigned, not_null, auto_increment) (array)</li>
</ul>


#### Altering tables
It's also possible to alter a table using the setupscripts.

```PHP
$table->alterColumn('some_value', [
    'name' => 'derp_integer_value',
    'comment' => false,
    'type' => Column::TYPE_INT,
    'length' => 11,
    'not_null' => false,
]);
```

The first value is the (original) name of the column you want to change.
The second value accepts an array of the properties you want to change.

### Accessing database
You can get data from the database by using models.<br/>
Each tabel is linked to a model by defining the table in the _construct.

```PHP
<?php
namespace Jcode\Core\Model;

class Test extends \Jcode\Application\Model
{

    protected function _construct()
    {
        parent::_init('test_table', 'id');
    }
}
```

With the ```_init()``` method you set the tablename and primary key.
This model is later used to store each result in.

To query into the table, you need to initialize the model's resource.

```PHP
$resource = $this->_dc->get('Jcode\Core\Model\Test')->getResource();
```
Without any arguments, this would produce the following query (printable by calling ```$resource->getQuery()```):
```mysql
SELECT * FROM test_table;
```

With the resource model it's now possible to add arguments to the query:
<br/><br/>
#### Add columns to select statement
By default * is selected from the main table.
You can change this by calling:

```
$resource->addColumnToSelect('<columnname>');
```
Or:
```
$resource->addColumnsToSelect([<column1>,<column2>,..]);
```

By calling this method ```'*'``` will be replaced with the added columns.
<br/><br/>
#### Add filters to select statement
By using ```addFilter()``` you can add ```WHERE()``` arguments to your selectquery.
The first value requires the column name and the second requires an array or string.
When you pass a string, the filter will default to ```['eq' => '<value>']```

The maintable (the table defined in the model) will always be selected ```AS main_table```
```
$resource->addFilter('<columnname>', '<value>');
```
Or:
```
$resource->addFilter('<columnname>', ['eq' => '<value>']);
```
<br/>
This will result in:
```mysql
SELECT * FROM test_table AS main_table WHERE <columnname> = <value>
```
<br/>
You are able to use multiple filters, resulting in WHERE .. AND statment:

```
$resource->addFilter('<column1>', '<value2>');
$resource->addFilter('<column2>', '<value2>');
```
<br/>
This will result in:
```mysql
SELECT * FROM test_table AS main_table WHERE <column1> = <value1> AND <column2> = <value2>
```
<br/>
Using OR statement:
```
$resource->addFilter('<column1>', ['eq' => ['<value1>','<value2>']]);
```
<br/>
This will result in:
```mysql
SELECT * FROM test_table AS main_table WHERE (<column1> = <value1 OR <column1> = <value2>)
```
#### Using joins
With ```addJoin()``` you can join another table with your select.

```
$resource->addJoin(['<tablename>' => '<alias>'], '<alias>.<field> = main_table.<field>');
```
<br/>
This will result in:
```mysql
SELECT main_table.*, alias.* FROM test_table AS main_table INNER JOIN tablename AS alias ON alias.field = main_table.field;
```
Adding a filter afterwards:
```
$resource->addFilter('<alias.field>', '<value>');
```
<br/>
This will result in:
```mysql
SELECT main_table.*, alias.* FROM test_table AS main_table INNER JOIN tablename AS alias ON alias.field = main_table.field WHERE alias.field = 'value';
```
<br/><br/>
#### Ordering your results.
It is easy to add (multiple) order(s) to your select query:
```
$resource->addOrder('<column>', '<ASC|DESC>');
```
<br/><br/>
#### Add offset and/or limit to your query:
With ```addLimit()``` you are able to paginize your results.
This method accepts 2 parameters: ```$offset``` and ```$limit```.
When only the first parameter is passed, then it will be treated as ```$limit``` without an offset.
```
$resource->addLimit(<offset>, <limit>);
```
<br/><br/>
#### Group by
It's just as easy to group your results by the given column:
```
$resource->addBroupBy('<columnname>');
```

### Lazy Loading
J!Code Framework makes use of lazy loading.
This means you can manipulate the resource object anywhere and anytime.
The resource model will only execute your query after you're trying to get data from the resource.
Getting data is done by calling ```$resource->getItems();``` or by iteraring through the resource model:
```php
foreach ($resource as $item) {
  // $item is a result from the selection.
}
```
