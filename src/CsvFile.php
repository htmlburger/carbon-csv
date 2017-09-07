<?php

namespace Carbon_CSV;
use \SplFileObject as File;

/**
 * Enhanced CSV file object
 */
class CsvFile extends File implements \Countable {
	private $file_path;
	private $encoding = 'utf-8';
	private $is_head_row = false;
	/**
	 * Current row.
	 */
	private $row_counter = 0;
	private $column_names;
	private $uses_column_names = false;
	private $offset_row = 0;
	private $start_column = 0;
	private $columns_to_skip = array();

	function __construct($file_path, $delimiter = ',', $enclosure = '"', $escape = "\\") {
		if (!file_exists($file_path)) {
			throw new Exception("File $file_path does not exist. ");
		}

		if (filesize($file_path) === 0) {
			throw new Exception("Empty file. ");
		}

		$this->file_path = $file_path;
		parent::__construct($file_path, 'r');
		$this->setFlags(File::READ_CSV | File::READ_AHEAD | File::SKIP_EMPTY | File::DROP_NEW_LINE);
		$this->setCsvControl($delimiter, $enclosure, $escape);
	}

	/**
	 * Read number of lines in CSV
	 * @return int number of lines
	 */
	function count() {
		return count($this->to_array());
	}

	public function to_array() {
		$rows = [];
		foreach ($this as $row) {
			$rows[] = $row;
		}

		return $rows;
	}

	public function rewind() {
		$this->seek($this->offset_row);
	}

	/**
	 * Override the key function in order to allow shifting in indecies according
	 * to the current offset.
	 */
	public function key() {
		return $this->row_counter - 1;
	}

	public function current() {
		$this->row_counter++;
		$row = parent::current();

		$row_keys = array_keys($row);
		if (!in_array($this->start_column, $row_keys)) {
			throw new Exception(sprintf('Start column must be between %d and %d.', min($row_keys), max($row_keys)));
		}

		$formatted_row = $this->format_row($row);

		return $formatted_row;
	}

	private function remove_columns($old_row) {
		$new_row = array();

		$index = 0;
		foreach ($old_row as $column_name => $column_value) {
			if (!in_array($index, $this->columns_to_skip)) {
				$new_row[$column_name] = $column_value;
			}

			$index++;
		}

		return $new_row;
	}

	private function format_row($row) {
		$row = array_combine(
			$this->get_column_names($row),
			$row
		);

		// don't remove columns from the head row
		// we remove columns after the row is combined with the header columns
		if (!$this->is_head_row) {
			$row = $this->remove_columns($row);
		}

		if (!$this->uses_column_names) {
			$row = array_values($row);
		}

		return $row;
	}

	private function get_column_names($row) {
		if (!empty($this->column_names)) {
			return $this->column_names;
		}

		return array_keys($row);
	}

	public function set_column_names($mapping) {
		$this->uses_column_names = true;

		if (empty($this->column_names)) {
			$this->column_names = $mapping;
		} else {
			$this->column_names = array_combine(
				array_flip($this->column_names),
				$mapping
			);
		}
	}

	public function use_first_row_as_header() {
		if ($this->row_counter !== 0) {
			throw new \LogicException("Column mapping can't be changed after CSV processing has been started");
		}

		$this->uses_column_names = true;

		$this->is_head_row = true;
		$this->column_names = $this->current();
		$this->is_head_row = false;

		// Start processing from the second row(since the first one isn't part of the data)
		$this->offset_row++;
		$this->rewind();
	}

	public function skip_to_row($row) {
		$this->offset_row = $row;
		$this->rewind();
	}

	public function skip_columns($indexes) {
		$this->set_columns_to_skip($indexes);
	}

	public function skip_to_column($column_index) {
		if (!is_int($column_index)) {
			throw new Exception('Only numbers are allowed for skip to column.');
		}

		if ($column_index < 0) {
			throw new Exception('Please use numbers larger than zero.');
		}

		$this->start_column = $column_index;

		// this is to handle the strange case, when the user wants to start from the first column (which happens by default)
		if ($column_index === 0) {
			$last_column_index = 0;
		} else {
			$last_column_index = $column_index - 1;
		}

		$this->set_columns_to_skip(range(0, $last_column_index));
	}

	private function set_columns_to_skip($columns) {
		$this->columns_to_skip = array_unique(array_merge($columns, $this->columns_to_skip));
	}
}
