<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
 * Class DBForge
 *
 */
class DBForge {

    /**
     * LavaLust Super Object
     * @var object
     */
    protected $_lava;

    /**
     * Table Columns
     * @var array
     */
    protected $fields = [];

    /**
     * Keys (primary + indexes)
     * @var array
     */
    protected $keys = [];

    /**
     * Primary Keys
     * @var array
     */
    protected $primary_key = [];

    /**
     * Foreign Keys
     * @var array
     */
    protected $foreign_keys = [];

    /**
     * Valid MySQL column types
     * @var array
     */
    protected $valid_types = [
        'INT', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT',
        'DECIMAL', 'FLOAT', 'DOUBLE', 'REAL',
        'CHAR', 'VARCHAR', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT',
        'DATE', 'DATETIME', 'TIMESTAMP', 'TIME', 'YEAR',
        'TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB',
        'ENUM', 'SET', 'BOOLEAN', 'BOOL', 'BIT', 'JSON',
    ];

    /**
     * Numeric types (support UNSIGNED)
     * @var array
     */
    protected $numeric_types = [
        'INT', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT',
        'DECIMAL', 'FLOAT', 'DOUBLE', 'REAL',
    ];

    // ----------------------------------------------------------------
    // Constructor
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->_lava = lava_instance();
        $this->_lava->call->database();
    }

    // ----------------------------------------------------------------
    // Field & Key Definitions
    // ----------------------------------------------------------------

    /**
     * Add one or more field definitions.
     *
     * Supported keys per field:
     *   type, constraint, unsigned, auto_increment, unique,
     *   null (bool), default, after
     *
     * @param  array $fields
     * @return $this
     */
    public function add_field(array $fields): static
    {
        foreach ($fields as $field => $details) {
            if ( ! is_string($field) || trim($field) === '') {
                $this->_trigger_error("Invalid field name provided.");
            }

            $type_base = strtoupper(preg_replace('/\(.*/', '', $details['type'] ?? ''));
            if ( ! in_array($type_base, $this->valid_types, true)) {
                $this->_trigger_error("Invalid column type '{$details['type']}' for field '$field'.");
            }

            $this->fields[$field] = $details;
        }

        return $this;
    }

    /**
     * Define a key (primary, unique index, or regular index).
     *
     * @param  string|array $key      Column name(s)
     * @param  bool         $primary  TRUE = primary key
     * @param  bool         $unique   TRUE = unique index
     * @param  string|null  $name     Optional index name
     * @return $this
     */
    public function add_key($key, bool $primary = false, bool $unique = false, ?string $name = null): static
    {
        $columns = (array) $key;

        if ($primary) {
            foreach ($columns as $col) {
                if ( ! in_array($col, $this->primary_key, true)) {
                    $this->primary_key[] = $col;
                }
            }
        } else {
            $this->keys[] = [
                'columns' => $columns,
                'unique'  => $unique,
                'name'    => $name ?? implode('_', $columns) . ($unique ? '_unique' : '_idx'),
            ];
        }

        return $this;
    }

    /**
     * Add a foreign key constraint.
     *
     * @param  string $field
     * @param  string $reference_table
     * @param  string $reference_field
     * @param  string $on_delete
     * @param  string $on_update
     * @return $this
     */
    public function add_foreign_key(
        string $field,
        string $reference_table,
        string $reference_field,
        string $on_delete = 'CASCADE',
        string $on_update = 'CASCADE'
    ): static {
        $allowed_actions = ['CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION', 'SET DEFAULT'];

        $on_delete = strtoupper($on_delete);
        $on_update = strtoupper($on_update);

        if ( ! in_array($on_delete, $allowed_actions, true)) {
            $this->_trigger_error("Invalid ON DELETE action: '$on_delete'.");
        }
        if ( ! in_array($on_update, $allowed_actions, true)) {
            $this->_trigger_error("Invalid ON UPDATE action: '$on_update'.");
        }

        $this->foreign_keys[] = [
            'field'           => $field,
            'reference_table' => $reference_table,
            'reference_field' => $reference_field,
            'on_delete'       => $on_delete,
            'on_update'       => $on_update,
        ];

        return $this;
    }

    // ----------------------------------------------------------------
    // Table Operations
    // ----------------------------------------------------------------

    /**
     * Create a new table.
     *
     * @param  string $table_name
     * @param  bool   $if_not_exists
     * @return bool
     */
    public function create_table(string $table_name, bool $if_not_exists = true): bool
    {
        $this->_validate_identifier($table_name);

        if (empty($this->fields)) {
            $this->_trigger_error("Cannot create table '$table_name' with no fields defined.");
        }

        $columns = [];

        foreach ($this->fields as $field => $details) {
            $columns[] = $this->_build_column_sql($field, $details);
        }

        if ( ! empty($this->primary_key)) {
            $pk_cols   = implode(', ', array_map([$this, '_quote'], $this->primary_key));
            $columns[] = "PRIMARY KEY ($pk_cols)";
        }

        foreach ($this->keys as $index) {
            $idx_type  = $index['unique'] ? 'UNIQUE INDEX' : 'INDEX';
            $idx_name  = $this->_quote($index['name']);
            $idx_cols  = implode(', ', array_map([$this, '_quote'], $index['columns']));
            $columns[] = "$idx_type $idx_name ($idx_cols)";
        }

        foreach ($this->foreign_keys as $fk) {
            $columns[] = sprintf(
                'FOREIGN KEY (%s) REFERENCES %s(%s) ON DELETE %s ON UPDATE %s',
                $this->_quote($fk['field']),
                $this->_quote($fk['reference_table']),
                $this->_quote($fk['reference_field']),
                $fk['on_delete'],
                $fk['on_update']
            );
        }

        $exists_clause = $if_not_exists ? 'IF NOT EXISTS ' : '';
        $sql = "CREATE TABLE {$exists_clause}" . $this->_quote($table_name)
             . " (\n    " . implode(",\n    ", $columns) . "\n)";

        $this->execute($sql);
        $this->reset();

        $this->_output("Table '$table_name' created successfully.");
        return true;
    }

    /**
     * Drop a table.
     *
     * @param  string $table_name
     * @param  bool   $if_exists
     * @return bool
     */
    public function drop_table(string $table_name, bool $if_exists = true): bool
    {
        $this->_validate_identifier($table_name);

        $exists_clause = $if_exists ? 'IF EXISTS ' : '';
        $sql = "DROP TABLE {$exists_clause}" . $this->_quote($table_name);

        $this->execute($sql);
        $this->_output("Table '$table_name' dropped successfully.");
        return true;
    }

    /**
     * Rename a table.
     *
     * @param  string $old_name
     * @param  string $new_name
     * @return bool
     */
    public function rename_table(string $old_name, string $new_name): bool
    {
        $this->_validate_identifier($old_name);
        $this->_validate_identifier($new_name);

        $sql = "ALTER TABLE " . $this->_quote($old_name) . " RENAME TO " . $this->_quote($new_name);
        $this->execute($sql);
        $this->_output("Table '$old_name' renamed to '$new_name'.");
        return true;
    }

    // ----------------------------------------------------------------
    // Column Alteration
    // ----------------------------------------------------------------

    /**
     * Add a column to an existing table.
     *
     * @param  string $table_name
     * @param  array  $field  ['column_name' => [...details...]]
     * @return bool
     */
    public function add_column(string $table_name, array $field): bool
    {
        $this->_validate_identifier($table_name);

        foreach ($field as $name => $details) {
            $this->_validate_identifier($name);
            $col_sql = $this->_build_column_sql($name, $details);
            $after   = isset($details['after']) ? ' AFTER ' . $this->_quote($details['after']) : '';
            $sql     = "ALTER TABLE " . $this->_quote($table_name) . " ADD COLUMN $col_sql$after";
            $this->execute($sql);
            $this->_output("Column '$name' added to '$table_name'.");
        }

        return true;
    }

    /**
     * Drop a column from a table.
     *
     * @param  string $table_name
     * @param  string $column_name
     * @return bool
     */
    public function drop_column(string $table_name, string $column_name): bool
    {
        $this->_validate_identifier($table_name);
        $this->_validate_identifier($column_name);

        $sql = "ALTER TABLE " . $this->_quote($table_name)
             . " DROP COLUMN " . $this->_quote($column_name);

        $this->execute($sql);
        $this->_output("Column '$column_name' dropped from '$table_name'.");
        return true;
    }

    /**
     * Modify an existing column definition.
     *
     * @param  string $table_name
     * @param  array  $field  ['column_name' => [...details...]]
     * @return bool
     */
    public function modify_column(string $table_name, array $field): bool
    {
        $this->_validate_identifier($table_name);

        foreach ($field as $name => $details) {
            $this->_validate_identifier($name);
            $col_sql = $this->_build_column_sql($name, $details);
            $sql     = "ALTER TABLE " . $this->_quote($table_name) . " MODIFY COLUMN $col_sql";
            $this->execute($sql);
            $this->_output("Column '$name' modified in '$table_name'.");
        }

        return true;
    }

    // ----------------------------------------------------------------
    // Introspection
    // ----------------------------------------------------------------

    /**
     * Check if a table exists.
     *
     * @param  string $table_name
     * @return bool
     */
    public function table_exists(string $table_name): bool
    {
        $this->_validate_identifier($table_name);

        $stmt = $this->_lava->db->raw(
            "SELECT COUNT(*) FROM information_schema.tables 
             WHERE table_schema = DATABASE() AND table_name = :table",
            ['table' => $table_name]
        );

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if a column exists in a table.
     *
     * @param  string $table_name
     * @param  string $column_name
     * @return bool
     */
    public function column_exists(string $table_name, string $column_name): bool
    {
        $this->_validate_identifier($table_name);
        $this->_validate_identifier($column_name);

        $stmt = $this->_lava->db->raw(
            "SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column",
            ['table' => $table_name, 'column' => $column_name]
        );

        return (int) $stmt->fetchColumn() > 0;
    }

    // ----------------------------------------------------------------
    // Internal Helpers
    // ----------------------------------------------------------------

    /**
     * Build a single column's SQL definition string.
     *
     * @param  string $field
     * @param  array  $details
     * @return string
     */
    protected function _build_column_sql(string $field, array $details): string
    {
        $type_upper = strtoupper(preg_replace('/\(.*/', '', $details['type']));
        $col        = $this->_quote($field) . " {$details['type']}";

        if (isset($details['constraint'])) {
            // Only append constraint if not already embedded in the type string
            if (strpos($details['type'], '(') === false) {
                $col .= "({$details['constraint']})";
            }
        }

        if (in_array($type_upper, $this->numeric_types, true)) {
            $col .= ( ! empty($details['unsigned'])) ? ' UNSIGNED' : ' SIGNED';
        }

        // NULL / NOT NULL  (default: NOT NULL)
        if (isset($details['null']) && $details['null'] === true) {
            $col .= ' NULL';
        } else {
            $col .= ' NOT NULL';
        }

        // DEFAULT value
        if (array_key_exists('default', $details)) {
            $default = $details['default'];
            if (is_null($default)) {
                $col .= ' DEFAULT NULL';
            } elseif (is_bool($default)) {
                $col .= ' DEFAULT ' . ($default ? '1' : '0');
            } elseif (is_numeric($default)) {
                $col .= ' DEFAULT ' . $default;
            } elseif (in_array(strtoupper($default), ['CURRENT_TIMESTAMP', 'NOW()'], true)) {
                $col .= ' DEFAULT ' . strtoupper($default);
            } else {
                $col .= " DEFAULT '" . addslashes($default) . "'";
            }
        }

        if ( ! empty($details['auto_increment'])) {
            $col .= ' AUTO_INCREMENT';
        }

        if ( ! empty($details['unique'])) {
            $col .= ' UNIQUE';
        }

        return $col;
    }

    /**
     * Backtick-quote an identifier, guarding against injection.
     *
     * @param  string $identifier
     * @return string
     */
    protected function _quote(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * Validate that an identifier contains only safe characters.
     *
     * @param  string $identifier
     * @return void
     */
    protected function _validate_identifier(string $identifier): void
    {
        if ( ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            $this->_trigger_error("Invalid identifier: '$identifier'. Only letters, digits, and underscores are allowed.");
        }
    }

    /**
     * Execute a raw SQL query.
     *
     * @param  string $sql
     * @return mixed
     */
    protected function execute(string $sql)
    {
        return $this->_lava->db->raw($sql);
    }

    /**
     * Reset all pending definitions.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->fields       = [];
        $this->keys         = [];
        $this->primary_key  = [];
        $this->foreign_keys = [];
    }

    /**
     * Output a message — HTML <br> in web, plain text in CLI.
     *
     * @param  string $text
     * @return void
     */
    protected function _output(string $text): void
    {
        if (defined('IS_CLI') && IS_CLI) {
            echo $text . PHP_EOL;
        } else {
            echo htmlspecialchars($text) . '<br>' . PHP_EOL;
        }
    }

    /**
     * Trigger a fatal error — exit in CLI, throw in web.
     *
     * @param  string $message
     * @return never
     */
    protected function _trigger_error(string $message): never
    {
        if (defined('IS_CLI') && IS_CLI) {
            echo "\033[31m✘ DBForge Error: $message\033[0m" . PHP_EOL;
            exit(1);
        }

        throw new RuntimeException("DBForge Error: $message");
    }
}