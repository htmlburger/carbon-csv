Carbon CSV
==========

Carbon CSV is a PHP library, that helps you easily parse CSV files.
Parse any CSV file to an easy to use associative array.

## Features

There are a number of helpful features that include:

* reduced memory usage and size, thanks to the [`SplFileObject`](http://php.net/manual/en/class.splfileobject.php) PHP class;
* skip row until (by index);
* skip specific columns (by index);
* column removal from parsed output (by index - single or multiple columns). If you wish to remove columns you don't need to work with;
* set custom column names, which will help you when using the parsed output.

## Installation

It is recommended to install the library through [Composer](https://getcomposer.org/).

```bash
composer require htmlburger/carbon-csv
```

## Usage

To parse a simple CSV file, use the following code:

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename.csv');
$rows = $csv->to_array();
```

This will parse the file and assign the result to the `$rows` variable.

<details><summary>Output</summary>

```
Array
(
    [0] => Array
        (
            [0] => John
            [1] => Doe
            [2] => Simple Company Name
            [3] => Street Name, 1234, City Name, Country Name
        )
    [1] => Array
        (
            [0] => Jane
            [1] => Doe
            [2] => Nice Company Name
            [3] => Street Name, 5678, City Name, Country Name
        )
)

```
</details>

### Settings

To change the delimiter, enclosure and escape characters for the CSV file, simply pass them as arguments after the file path.

Example:

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename.csv', ';', '|', '/');
$rows = $csv->to_array();
```

### Methods

```
Please note, that skipping methods work with zero based indexes.
```

#### skip_to_row

To skip to a specific row, simply pass the index of the row.

This will tell the parser to start reading from that row until the end of the file.

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename.csv');
$csv->skip_to_row(1);
$rows = $csv->to_array();
```

Contents before skipping to a specific row:

```
Array
(
    [0] => Array
        (
            [0] => John
            [1] => Doe
            [2] => Simple Company Name
            [3] => Street Name, 1234, City Name, Country Name
        )
    [1] => Array
        (
            [0] => Jane
            [1] => Doe
            [2] => Nice Company Name
            [3] => Street Name, 5678, City Name, Country Name
        )
)
```

Contents after skipping to a specific row:

```
Array
(
    [0] => Array
        (
            [0] => Jane
            [1] => Doe
            [2] => Nice Company Name
            [3] => Street Name, 5678, City Name, Country Name
        )
)

```

#### skip_to_column

To skip to a specific column, simply pass the index of the column.

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename.csv');
$csv->skip_to_column(2);
$rows = $csv->to_array();
```

Contents before skipping to a specific column:

```
Array
(
    [0] => Array
        (
            [0] => John
            [1] => Doe
            [2] => Simple Company Name
            [3] => Street Name, 1234, City Name, Country Name
        )
    [1] => Array
        (
            [0] => Jane
            [1] => Doe
            [2] => Nice Company Name
            [3] => Street Name, 5678, City Name, Country Name
        )
)
```

Contents after skipping to a specific column:

```
Array
(
    [0] => Array
        (
            [0] => Simple Company Name
            [1] => Street Name, 1234, City Name, Country Name
        )
    [1] => Array
        (
            [0] => Nice Company Name
            [1] => Street Name, 5678, City Name, Country Name
        )
)

```

#### skip_columns

To skip multiple columns, pass the indexes of those columns as an array.

**Important:** when skipping, indexes are reset to start from 0, so you don't need to remember which indexes are available.

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename.csv');
$csv->skip_columns(array(0, 2, 3));
$rows = $csv->to_array();
```

Contents before skipping columns:

```
Array
(
    [0] => Array
        (
            [0] => John
            [1] => Doe
            [2] => Simple Company Name
            [3] => Street Name, 1234, City Name, Country Name
        )
    [1] => Array
        (
            [0] => Jane
            [1] => Doe
            [2] => Nice Company Name
            [3] => Street Name, 5678, City Name, Country Name
        )
)
```

Contents after skipping columns:

```
Array
(
    [0] => Array
        (
            [0] => Doe
        )
    [1] => Array
        (
            [0] => Doe
        )
)

```

#### use_first_row_as_header

To use the first row from the CSV, simply call this method.

**Important:** if the `skip_to_row` is called prior to calling this method, the parser will use the row it's set to skip to, as the header row.

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename.csv');
$csv->use_first_row_as_header();
$rows = $csv->to_array();
```

Contents before assigning a header row:

```
Array
(
    [0] => Array
        (
            [0] => First Name
            [1] => Last Name
        )
    [1] => Array
        (
            [0] => John
            [1] => Doe
        )
    [2] => Array
        (
            [0] => Jane
            [1] => Dove
        )
)
```

Contents after assigning a header row:

```
Array
(
    [0] => Array
        (
            [First Name] => John
            [Last Name] => Doe
        )
    [1] => Array
        (
            [First Name] => Jane
            [Last Name] => Dove
        )
)
```

Since we're telling the parser to use the first row as a header row, it is assigned and skipped.

#### set_column_names

If you wish to use your own indexes for the columns, pass them using an array.

**Important:** you can use this method with `use_first_row_as_header`, so you can set the names of the columns based on the header row.

Example without `use_first_row_as_header` (using a file without a head row):

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename-no-head-rows.csv');
$csv->set_column_names([
    0 => 'first_name',
    1 => 'last_name',
    2 => 'company_name',
    3 => 'address',
]);
$rows = $csv->to_array();
```

Contents before setting custom column names:

```
Array
(
    [0] => Array
        (
            [0] => John
            [1] => Doe
            [2] => Simple Company Name
            [3] => Street Name, 1234, City Name, Country Name
        )
    [1] => Array
        (
            [0] => Jane
            [1] => Doe
            [2] => Nice Company Name
            [3] => Street Name, 5678, City Name, Country Name
        )
)
```

Contents after setting custom column names:

```
Array
(
    [0] => Array
        (
            [first_name] => John
            [last_name] => Doe
            [company_name] => Simple Company Name
            [address] => Street Name, 1234, City Name, Country Name
        )
    [1] => Array
        (
            [first_name] => Jane
            [last_name] => Doe
            [company_name] => Nice Company Name
            [address] => Street Name, 5678, City Name, Country Name
        )
)
```

----

Example with `use_first_row_as_header` (using a file with a head row):

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename-no-head-rows.csv');
$csv->use_first_row_as_header();
$csv->set_column_names([
    'First Name' => 'first_name',
    'Last Name' => 'last_name',
    'Company Name' => 'company_name',
    'Address' => 'address',
]);
$rows = $csv->to_array();
```

Contents before setting custom column names:

```
Array
(
    [0] => Array
        (
            [0] => First Name
            [1] => Last Name
            [2] => Company Name
            [3] => Address
        )
    [1] => Array
        (
            [0] => John
            [1] => Doe
            [2] => Simple Company Name
            [3] => Street Name, 1234, City Name, Country Name
        )
    [2] => Array
        (
            [0] => Jane
            [1] => Doe
            [2] => Nice Company Name
            [3] => Street Name, 5678, City Name, Country Name
        )
)
```

Contents after setting custom column names:

```
Array
(
    [0] => Array
        (
            [first_name] => John
            [last_name] => Doe
            [company_name] => Simple Company Name
            [address] => Street Name, 1234, City Name, Country Name
        )
    [1] => Array
        (
            [first_name] => Jane
            [last_name] => Doe
            [company_name] => Nice Company Name
            [address] => Street Name, 5678, City Name, Country Name
        )
)
```

#### count

Get the total number of rows in the CSV file (please note, that this skips the empty rows);

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename.csv');
$total_number_of_rows = $csv->count();
```

`$total_number_of_rows = $csv->count()` is equivalent to `count($csv->to_array())`.
