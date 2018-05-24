<?php
/**
 * @file
 *
 * Handle CRUD for polls, and show all available polls
 *
 * @author Mike Rodarte
 */

namespace Polls;

@session_start();

/**
 * Class Polls
 */
class Polls {

  private $db = NULL;

  /**
   * Table names used in the application
   *
   * @var array
   */
  private $tables = [
    'poll' => 'polls',
    'answer' => 'poll_answers',
    'result' => 'poll_results',
  ];

  public function __construct() {
    $this->db = new Db();
  }

  /**
   * Take the provided parameters and send them to the database to create a
   * poll with answers
   *
   * @param string $name
   * @param string $question
   * @param array $answers
   *
   * @return boolean
   */
  public function create($name = '', $question = '', $answers = []) {
    // create a poll with the name and question and get the poll id
    $poll_id = $this->db->insert($this->tables['poll'], [
      'name' => $name,
      'question' => $question,
    ]);
    // insert each answer in the array to the table using the poll id
    $valid = 0;
    $expected = count($answers);
    foreach ($answers as $answer) {
      $aid = $this->db->insert($this->tables['answer'], [
        'poll_id' => $poll_id,
        'answer' => $answer,
      ]);
      if ($aid > 0) {
        $valid++;
      }
    }

    // return if all answers were inserted correctly
    return $poll_id !== FALSE && $poll_id > 0 && $valid === $expected;
  }

  /**
   * Return HTML for all polls in a list
   */
  public function viewAll() {
    // get all polls from database
    // run polls through HTML template
    // display polls
  }

  /**
   * Return HTML for the specified poll with its question and answers
   *
   * @param int $poll_id
   */
  public function view($poll_id = 0) {
    // get the poll from the poll id
    // send poll through HTML template
    // display poll
  }

  /**
   * Return HTML of the results for this poll
   *
   * @param int $poll_id
   */
  public function results($poll_id = 0) {
    // get the results of the poll from the poll id
    // send the results through HTML template
    // display poll results
  }

  /**
   * Create a string based on features of this user's browsing experience
   *
   * @return string User string
   */
  private function getUserString() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];

    return session_id() . '|' . $ip . '|' . $ua;
  }
} // end Polls
