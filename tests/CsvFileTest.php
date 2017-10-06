<?php

use PHPUnit\Framework\TestCase;

use \Carbon_CSV\CsvFile as CsvFile;
use \Carbon_CSV\Exception;

class CsvParserTest extends TestCase {
	function test_it_can_be_constructed_from_a_file() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv');
		$this->assertEquals(5, count($csv));
	}
	/**
	 * @expectedException \Carbon_CSV\Exception
	 */
	function test_it_throws_exception_when_constructed_with_a_missing_file() {
		new CsvFile(__DIR__ . '/no-such-file-71e37259-48d0-416a-b640-2beeb23aa38f.tmp');
	}

	function get_expected_result_indexed() {
		return [
			[0 => 'John', 1 => 'Doe', 2 => 'Funny Company Name', 3 => 'Some Address 2, 12345, Country A'],
			[0 => 'Jane', 1 => 'Dove', 2 => 'Nice Company Name', 3 => 'That Address 3, 456, Country B'],
			[0 => 'John', 1 => 'Smith', 2 => 'Nice Company Name', 3 => ''],
			[0 => 'Jane', 1 => 'Smith', 2 => 'Funny Company Name', 3 => 'This Address 4, City, Country C'],
		];
	}

	function get_expected_result_custom_columns() {
		return [
			['first_name' => 'John', 'last_name' => 'Doe', 'company_name' => 'Funny Company Name', 'address' => 'Some Address 2, 12345, Country A'],
			['first_name' => 'Jane', 'last_name' => 'Dove', 'company_name' => 'Nice Company Name', 'address' => 'That Address 3, 456, Country B'],
			['first_name' => 'John', 'last_name' => 'Smith', 'company_name' => 'Nice Company Name', 'address' => ''],
			['first_name' => 'Jane', 'last_name' => 'Smith', 'company_name' => 'Funny Company Name', 'address' => 'This Address 4, City, Country C'],
		];
	}

	function get_expected_result_actual_columns() {
		return [
			['First Name' => 'John', 'Last Name' => 'Doe', 'Company Name' => 'Funny Company Name', 'Address' => 'Some Address 2, 12345, Country A'],
			['First Name' => 'Jane', 'Last Name' => 'Dove', 'Company Name' => 'Nice Company Name', 'Address' => 'That Address 3, 456, Country B'],
			['First Name' => 'John', 'Last Name' => 'Smith', 'Company Name' => 'Nice Company Name', 'Address' => ''],
			['First Name' => 'Jane', 'Last Name' => 'Smith', 'Company Name' => 'Funny Company Name', 'Address' => 'This Address 4, City, Country C'],
		];
	}

	/**
	 * @expectedException \Carbon_CSV\Exception
	 * @expectedExceptionMessage Empty file.
	 */
	function test_it_throws_exception_when_constructed_with_an_empty_file() {
		$csv = new CsvFile(__DIR__ . '/sample-data/empty.csv');
	}

	function test_without_setup() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info-no-head-row.csv');
		$this->assertEquals($this->get_expected_result_indexed(), $csv->to_array());
	}

	function test_iterator() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv');
		$csv->skip_to_row(1);
		$csv->set_column_names([
			0 => 'first_name',
			1 => 'last_name',
			2 => 'company_name',
			3 => 'address',
		]);

		$i = 0;
		foreach ($csv as $row_number => $row) {
			$this->assertEquals($i++, $row_number);
		}
	}

	function test_setup_head_rows() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info-no-head-row.csv');

		$csv->set_column_names([
			0 => 'first_name',
			1 => 'last_name',
			2 => 'company_name',
			3 => 'address',
		]);

		$this->assertEquals($this->get_expected_result_custom_columns(), $csv->to_array());
	}

	function test_with_head_row() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv');
		$csv->use_first_row_as_header();

		$this->assertEquals($this->get_expected_result_actual_columns(), $csv->to_array());
	}

	function test_with_head_row_and_custom_mappings() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv');
		$csv->use_first_row_as_header();
		$csv->set_column_names([
			'First Name' => 'first_name',
			'Last Name' => 'last_name',
			'Company Name' => 'company_name',
			'Address' => 'address',
		]);

		$this->assertEquals($this->get_expected_result_custom_columns(), $csv->to_array());
	}

	function test_with_head_row_and_skip_to_initial_row() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv');
		$csv->use_first_row_as_header();
		$csv->skip_to_row(0);

		$this->assertEquals($this->get_expected_result_actual_columns(), $csv->to_array());
	}

	function test_skip_rows() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv');
		$csv->skip_to_row(1);
		$csv->set_column_names([
			0 => 'first_name',
			1 => 'last_name',
			2 => 'company_name',
			3 => 'address',
		]);

		$this->assertEquals($this->get_expected_result_custom_columns(), $csv->to_array());
	}

	function test_use_first_row_after_skipping_rows() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info-with-empty-rows-before-actual-content.csv');
		$csv->skip_to_row(3);
		$csv->use_first_row_as_header();

		$this->assertEquals($this->get_expected_result_actual_columns(), $csv->to_array());
	}

	function test_separator_is_respected() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info-semicolon-separator.csv', ';');
		$csv->use_first_row_as_header();

		$this->assertEquals([
			['First Name' => 'John', 'Last Name' => 'Doe', 'Company Name' => 'Funny Company Name', 'Address' => 'Some Address 2; 12345; Country A'],
			['First Name' => 'Jane', 'Last Name' => 'Dove', 'Company Name' => 'Nice Company Name', 'Address' => 'That Address 3; 456; Country B'],
			['First Name' => 'John', 'Last Name' => 'Smith', 'Company Name' => 'Nice Company Name', 'Address' => ''],
			['First Name' => 'Jane', 'Last Name' => 'Smith', 'Company Name' => 'Funny Company Name', 'Address' => 'This Address 4; City; Country C'],
		], $csv->to_array());
	}

	function test_skip_to_column() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info-no-head-row.csv');
		$csv->skip_to_column(1);

		$this->assertEquals( [
			[0 => 'Doe', 1 => 'Funny Company Name', 2 => 'Some Address 2, 12345, Country A'],
			[0 => 'Dove', 1 => 'Nice Company Name', 2 => 'That Address 3, 456, Country B'],
			[0 => 'Smith', 1 => 'Nice Company Name', 2 => ''],
			[0 => 'Smith', 1 => 'Funny Company Name', 2 => 'This Address 4, City, Country C'],
		], $csv->to_array() );
	}

	function test_skip_to_first_column_and_use_first_row_as_header() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv');
		$csv->skip_to_column(1);
		$csv->use_first_row_as_header();

		$this->assertEquals( [
			['Last Name' => 'Doe', 'Company Name' => 'Funny Company Name', 'Address' => 'Some Address 2, 12345, Country A'],
			['Last Name' => 'Dove', 'Company Name' => 'Nice Company Name', 'Address' => 'That Address 3, 456, Country B'],
			['Last Name' => 'Smith', 'Company Name' => 'Nice Company Name', 'Address' => ''],
			['Last Name' => 'Smith', 'Company Name' => 'Funny Company Name', 'Address' => 'This Address 4, City, Country C'],
		], $csv->to_array() );
	}

	function test_skip_to_column_outside_of_column_index_range() {
		$this->expectException(Exception::class);

		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv' );
		$csv->skip_to_column(999);
		$csv->to_array();
	}

	function test_exclude_multiple_columns() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info-no-head-row.csv');
		$csv->skip_columns(array(0, 2, 3));

		$this->assertEquals([
			[
				0 => 'Doe'
			],
			[
				0 => 'Dove'
			],
			[
				0 => 'Smith'
			],
			[
				0 => 'Smith'
			]
		], $csv->to_array());
	}

	function test_custom_header_with_exclude_multiple_columns() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv' );
		$csv->use_first_row_as_header();
		$csv->skip_columns(array(0, 2, 3));
		$csv->set_column_names([
			'First Name' => 'first_name',
			'Last Name' => 'last_name',
			'Company Name' => 'company_name',
			'Address' => 'address'
		]);

		$this->assertEquals( [
			[
				'last_name' => 'Doe'
			],
			[
				'last_name' => 'Dove'
			],
			[
				'last_name' => 'Smith'
			],
			[
				'last_name' => 'Smith'
			]
		], $csv->to_array() );
	}

	function test_throws_exception_when_using_non_number_characters_for_skip_to_column() {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Only numbers are allowed for skip to column.');

		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv' );
		$csv->skip_to_column('a');
	}
	function test_non_utf8_encoded_file() {
		$csv = new CsvFile(__DIR__ . '/sample-data/cp1251.csv');
		$csv->set_encoding('cp-1251');
		$csv->use_first_row_as_header();
		$csv->set_column_names([
			'Име' => 'name',
			'Възраст' => 'age',
		]);
		$this->assertEquals( [
			[
				'name' => 'Хоумър',
				'age' => '31',
			],
		], $csv->to_array() );
	}

	function test_missing_column_causes_exception() {
		$this->expectException(Exception::class);

		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv');
		$csv->use_first_row_as_header();
		$csv->set_column_names([
			'Last Name Typo' => 'lname',
			'Address' => 'address',
		]);
	}

	function test_map_partial_columns() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info.csv');
		$csv->use_first_row_as_header();
		$csv->set_column_names([
			'Last Name' => 'lname',
			'Address' => 'address',
		]);
		$this->assertEquals( [
			[
				'lname' => 'Doe',
				'address' => 'Some Address 2, 12345, Country A',
			],
			[
				'lname' => 'Dove',
				'address' => 'That Address 3, 456, Country B',
			],
			[
				'lname' => 'Smith',
				'address' => '',
			],
			[
				'lname' => 'Smith',
				'address' => 'This Address 4, City, Country C',
			],
		], $csv->to_array() );
	}

	function test_map_partial_indecies_columns() {
		$csv = new CsvFile(__DIR__ . '/sample-data/info-no-head-row.csv');
		$csv->set_column_names([
			1 => 'lname',
			3 => 'address',
		]);
		$this->assertEquals( [
			[
				'lname' => 'Doe',
				'address' => 'Some Address 2, 12345, Country A',
			],
			[
				'lname' => 'Dove',
				'address' => 'That Address 3, 456, Country B',
			],
			[
				'lname' => 'Smith',
				'address' => '',
			],
			[
				'lname' => 'Smith',
				'address' => 'This Address 4, City, Country C',
			],
		], $csv->to_array() );
	}
}
