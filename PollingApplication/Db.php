<?php
/**
 * @file
 * @author Mike Rodarte
 */

/**
 * Be part of the Polls namespace
 */

namespace Polls;

/**
 * Class Db
 *
 * Handle all necessary MySQL PDO operations.
 * There are many more things this class could do, but this is not an all-
 * inclusive class, just a basic database wrapper class.
 *
 * @package Polls
 * @author Mike Rodarte
 */
class Db {

  /**
   * @var \PDO
   */
  private $dbh;

  /**
   * @var string
   */
  private $database_name = '';

  /**
   * @var string
   */
  private $host = '';

  /**
   * @var string
   */
  private $message = '';

  /**
   * @var string
   */
  private $password = '';

  /**
   * @var \PDOStatement
   */
  private $stmt;

  /**
   * @var string
   */
  private $user = '';

  /**
   * Db constructor.
   * Create the PDO object for later use
   *
   * @param string $host Host name or IP address for the MySQL server
   * @param string $user User name for the database
   * @param string $password Password for the database
   * @param string $database The database name
   */
  public function __construct($host = '127.0.0.1', $user = 'root', $password = 'root', $database = 'custom') {
    $this->settings($host, $user, $password, $database);
    try {
      $this->dbh = new \PDO('mysql:host=' . $this->host . ';dbbname=' . $this->database_name, $this->user, $this->password, [\PDO::MYSQL_ATTR_FOUND_ROWS => true]);
      $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    catch (\Exception $ex) {
      $this->message = 'Error connecting to MySQL database';
    }
  } // end __construct

  /**
   * Set database parameters
   *
   * @param string $host
   * @param string $user
   * @param string $password
   * @param string $database
   */
  public function settings($host = '', $user = '', $password = '', $database = ''): void {
    $this->host = \is_string($host) ? $host : '';
    $this->user = \is_string($user) ? $user : '';
    $this->password = \is_string($password) ? $password : '';
    $this->database_name = \is_string($database) ? $database : '';
  } // end settings

  /**
   * Insert one row of values and return the insert ID
   *
   * @param string $table Table name
   * @param array $pairs Pairs of field => value to insert
   *
   * @return bool|int
   */
  public function insert($table = '', array $pairs = []) {
    // loosely validate input
    if (!\is_string($table) || empty($table) || !\is_array($pairs) || empty($pairs)) {
      return false;
    }

    $fields = array_keys($pairs);
    $values = array_values($pairs);

    if (!$this->validateScalar($fields, true)) {
      $this->message = 'The fields provided are not strings';

      return false;
    }
    if (!$this->validateScalar($values)) {
      $this->message = 'The values provided are not scalar';

      return false;
    }

    $separator = ', ';

    // build the query
    $sql = 'INSERT INTO ' . $table . PHP_EOL;
    $sql .= '  (' . implode($separator, $fields) . ')' . PHP_EOL;
    $sql .= '  VALUES' . PHP_EOL;
    $sql .= '  (' . implode($separator, array_fill(0, \count($values), '?')) . ')' . PHP_EOL;

    // execute the query
    $insert_id = $this->query($sql, $values, 'insert');

    // set messaging as appropriate
    if ($insert_id !== false) {
      $this->message = 'Successfully inserted values with ID ' . $insert_id;
    }
    else {
      $this->message = 'Error inserting values to table';
    }

    return $insert_id;
  } // end insert

  /**
   * Build and execute a SELECT query with the table name, fields, conditions,
   * and order
   *
   * @param string $table Table name
   * @param array $fields Fields to select
   * @param array $conditions Conditions to add to WHERE
   * @param array $order ORDER BY values and their directions
   * @param string $return_type Desired return type (single|multiple)
   *
   * @return array|bool|int|null|string
   */
  public function select($table = '', array $fields = [], array $conditions = [], array $order = [], $return_type = 'multiple') {
    // loosely validate table
    if (!\is_string($table) || empty($table)) {
      $this->message = 'Invalid table specified';

      return false;
    }

    // validate fields
    if (empty($fields)) {
      // default fields to all if there are no fields
      $fields = ['*'];
    }
    else {
      if (!$this->validateScalar($fields, true)) {
        $this->message = 'Fields are not set up properly';

        return false;
      }
    }

    // build SELECT query
    $sql = 'SELECT ' . implode(', ', $fields) . PHP_EOL;
    $sql .= '  FROM ' . $table . PHP_EOL;

    // add WHERE conditions
    [$where_sql, $params] = $this->conditions($conditions);
    $sql .= $where_sql;

    // add ORDER BY values
    $sql .= $this->order($order);

    // execute the query
    $results = $this->query($sql, $params, $return_type);

    // set messaging based on results
    if ($results === false) {
      $this->message = 'Error getting results';
    }
    else {
      $num_results = \count($results);
      if ($num_results > 0) {
        $this->message = 'Found ' . $num_results . ' results';
      }
      else {
        $this->message = 'Could not find results from the query';
      }
    }

    return $results;
  } // end select

  /**
   * Update table for specified record
   *
   * @param string $table Table name to update
   * @param array $set Field => Value parameters to update
   * @param array $conditions Conditions for WHERE clause
   * @param int $limit Default to 1 to try to only update 1 row
   *
   * @return array|bool|int|null|string
   */
  public function update($table = '', array $set = [], array $conditions = [], $limit = 1) {
    // loosely validate input
    if (!\is_string($table) || empty($table) || !\is_array($set) || empty($set)) {
      return false;
    }

    // get simple arrays for validation purposes
    $fields = array_keys($set);
    $values = array_values($set);

    if (!$this->validateScalar($fields, true)) {
      $this->message = 'The fields provided are not strings';

      return false;
    }
    if (!$this->validateScalar($values)) {
      $this->message = 'The values provided are not scalar';

      return false;
    }

    $params = [];

    // build the query
    $sql = 'UPDATE ' . $table . PHP_EOL;
    $sql .= '  SET' . PHP_EOL;
    foreach ($set as $field => $value) {
      $sql .= '    ' . $field . ' = ?' . PHP_EOL;
      $params[] = $value;
    }

    // add WHERE clause
    [$where_sql, $where_params] = $this->conditions($conditions);
    $params = array_merge($params, $where_params);
    $sql .= $where_sql;

    // add LIMIT clause
    if (\is_int($limit) && $limit > 0) {
      $sql .= '  LIMIT ' . $limit;
    }

    // execute the query
    $updated = $this->query($sql, $params, 'update');

    // set messaging as appropriate
    if ($updated !== false) {
      $this->message = 'Successfully updated values';
    }
    else {
      $this->message = 'Error updating values in table';
    }

    return $updated;
  } // end update

  /**
   * Delete a specific record
   *
   * @param string $table
   * @param array $conditions
   * @param int $limit
   *
   * @return array|bool|int|null|string
   */
  public function delete($table = '', array $conditions = [], $limit = 1) {
    // loosely validate input
    if (!\is_string($table) || empty($table) || !\is_array($conditions) || empty($conditions)) {
      return false;
    }

    // build DELETE query
    $sql = 'DELETE FROM ' . $table . PHP_EOL;

    // add WHERE clause
    [$where_sql, $params] = $this->conditions($conditions);
    $sql .= $where_sql;

    // add LIMIT clause
    if (\is_int($limit) && $limit > 0) {
      $sql .= '  LIMIT ' . $limit;
    }

    // execute query
    $deleted = $this->query($sql, $params, 'delete');

    // set messaging as appropriate
    if ($deleted !== false) {
      $this->message = 'Successfully deleted the poll';
    }
    else {
      $this->message = 'Error deleting poll from the table';
    }

    return $deleted;
  } // end delete

  /**
   * Execute a query with optional parameters and return the requested result
   *
   * @param string $sql SQL string with parameter placeholders
   * @param array $params Parameters to fill the placeholders
   * @param string $return_type Desired return type
   *
   * @return array|bool|int|null|string
   */
  public function query($sql = '', array $params = [], $return_type = 'single') {
    // prepare the query
    try {
      $this->stmt = $this->dbh->prepare($sql);
    }
    catch (\Exception $ex) {
      $this->message = 'Error preparing query';

      return false;
    }

    // bind parameters
    if (\is_array($params) && !empty($params)) {
      foreach ($params as $i => $param) {
        $this->stmt->bindValue($i + 1, $param);
      }
    }

    // execute the statement
    try {
      $executed = $this->stmt->execute();
    }
    catch (\Exception $ex) {
      $this->message = 'Error executing prepared statement';

      return false;
    }

    if (!$executed) {
      return false;
    }

    // get results in the requested format
    $results = null;
    switch ($return_type) {
      case 'single':
        $row = $this->stmt->fetch(\PDO::FETCH_NUM);
        $results = $row[0];
        break;
      case 'multiple':
        $results = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
        break;
      case 'insert':
        $results = $this->dbh->lastInsertId();
        break;
      case 'update':
      case 'delete':
        $results = $this->stmt->rowCount() > 0;
        break;
    }

    // return results
    return $results;
  } // end query

  /**
   * Handle AND conditions for WHERE clause
   *
   * @param array $conditions
   *
   * @return array
   */
  private function conditions(array $conditions = []): array {
    // prepare variables for return
    $sql = '';
    $params = [];

    if (empty($conditions)) {
      $this->message = 'Conditions are empty';

      return ['', []];
    }

    // set up default WHERE clause to add AND conditions (no need for OR at this point)
    $sql .= '  WHERE 1';
    foreach ($conditions as $condition) {
      if (!\is_array($condition)) {
        continue;
      }
      $sql .= '    AND ' . $condition[1] . ' ';
      if (array_key_exists(2, $condition)) {
        $sql .= $condition[2];
      }
      else {
        $sql .= '=';
      }
      $sql .= ' ?' . PHP_EOL;
      $params[] = $condition[1];
    }

    return [$sql, $params];
  } // end conditions

  /**
   * Add ORDER BY clause with fields and directions
   *
   * @param array $order
   *
   * @return string
   */
  private function order(array $order = []): string {
    if (empty($order)) {
      $this->message = 'There is no order';

      return '';
    }

    if (!\is_array($order)) {
      $this->message = 'Order is invalid';

      return '';
    }

    $sql = '  ORDER BY ';
    foreach ($order as $array) {
      // add the field name to the query
      $sql .= $array[0];
      // get the direction
      if (array_key_exists(1, $array) && \in_array(strtoupper($array[1]), [
          'ASC',
          'DESC',
        ])) {
        $sql .= ' ' . $array[1];
      }
      else {
        $sql .= ' ASC';
      }
      // add comma and space in preparation of the next value
      $sql .= ', ';
    } // end foreach
    // remove the trailing comma and space
    $sql = rtrim($sql, ', ') . PHP_EOL;

    return $sql;
  } // end order

  /**
   * Validate the array passed contains all scalar or string values
   *
   * @param $array
   * @param bool $force_string
   *
   * @return bool
   */
  private function validateScalar($array, $force_string = false): bool {
    if (!\is_array($array)) {
      return false;
    }

    // determine if all of the values in the array are scalar or string
    $mapped = array_map(function ($value) use ($force_string) {
      if ($force_string === true) {
        return \is_string($value);
      }

      return is_scalar($value);
    }, $array);

    // get unique values
    $unique = array_unique($mapped);

    // if there is only 1 value and it is true, all elements of the array are scalar
    return \count($unique) === 1 && $unique[0] === true;
  } // end validateScalar
} // end Db
