Carbon CSV
==========

Carbon CSV is a PHP library aimed at simplifying CSV parsing.

It provides simple interface to ease mapping columns via a header row, or custom column names. 

## Installation

```bash
composer require htmlburger/carbon-csv
```

## Usage

Suppose that you have the following CSV:

| First Name | Last Name | Company Name                    | Address                             |
|------------|-----------|---------------------------------|-------------------------------------|
| Homer      | Simpson   | Springfield Nuclear Power Plant |  742 Evergreen Terrace, Springfield |
| Ned        | Flanders  | The Leftorium                   | 744 Evergreen Terrace, Springfield  |

Here is how you could iterate through the rows:

```php
use \Carbon_CSV\CsvFile;
use \Carbon_CSV\Exception as CsvException;

try {
    $csv = new CsvFile('path-to-file/filename.csv');
    $csv->use_first_row_as_header();

    foreach ($csv as $row) {
        print_r($row);
    }
} catch (CsvException $e) {
    exit("Couldn't parse CSV file: " . $e->getMessage()); 
}
```

Would produce the following output: 

```
Array
(
    [First Name] => Homer
    [Last Name] => Simpson
    [Company Name] => Springfield Nuclear Power Plant
    [Address] => 742 Evergreen Terrace, Springfield
)
Array
(
    [First Name] => Ned
    [Last Name] => Flanders
    [Company Name] => The Leftorium
    [Address] => 744 Evergreen Terrace, Springfield
)
```

Alternatively, you could also provide your own column names:

```php
use \Carbon_CSV\CsvFile;
use \Carbon_CSV\Exception as CsvException;

try {
    $csv = new CsvFile('path-to-file/filename.csv');
    $csv->use_first_row_as_header();
    $csv->set_column_names([
        'First Name'   => 'fname',
        'Last Name'    => 'lname',
        'Company Name' => 'company',
        'Address'      => 'address',
    ]);

    foreach ($csv as $row) {
        print_r($row);
    }
} catch (CsvException $e) {
    exit("Couldn't parse CSV file: " . $e->getMessage()); 
}
```

Would produce the following output: 

```
Array
(
    [fname] => Homer
    [lname] => Simpson
    [company] => Springfield Nuclear Power Plant
    [address] => 742 Evergreen Terrace, Springfield
)
Array
(
    [fname] => Ned
    [lname] => Flanders
    [company] => The Leftorium
    [address] => 744 Evergreen Terrace, Springfield
)
```

**MacOS encoding**

When working with files created on a Mac device, you should set the `auto_detect_line_endings` PHP variable to `1`.

```
ini_set( 'auto_detect_line_endings', 1 );
```

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

Methods for skipping rows or columns work with zero based indexes.

#### `skip_to_row(int $row_index)`

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

#### `skip_to_column(int $col_index)`

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

#### `skip_columns(array $col_indexes)`

To skip multiple columns, pass the indexes of those columns as an array.

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

#### `use_first_row_as_header()`

To use the first row from the CSV, simply call this method.

**Note:** if `skip_to_row` is called prior to calling `use_first_row_as_header`, the parser will use the new first row as a header. 

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

#### `set_column_names(array $columns_mapping)`

If you wish to use your own indexes for the columns, pass them using an array.

**Note:** you can use `set_column_names` in conjunction with `use_first_row_as_header`, so you can set the names of the columns based on the header row.

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

#### `set_encoding($encoding)`

Set the encoding of the CSV file.
This is needed, so it can be properly converted to utf-8

Example:

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename.csv');
$csv->set_encoding('windows-1251');
$total_number_of_rows = $csv->count();
```

#### `count()`

Get the total number of rows in the CSV file (this skips the empty rows):

```php
use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

$csv = new CsvFile('path-to-file/filename.csv');
$total_number_of_rows = $csv->count();
```

`$total_number_of_rows = $csv->count()` is equivalent to `count($csv->to_array())`.
