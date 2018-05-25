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
 *
 * Handle the middle-man transactions between the front-end AJAX and the
 * database
 *
 * @author Mike Rodarte
 */
class Polls {

  /**
   * The database object
   *
   * @var \Polls\Db
   */
  private $db;

  /**
   * The last message set by the application
   *
   * @var string
   */
  private $message = '';

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

  /**
   * Template directory containing PHP files with HTML in them
   *
   * @var string
   */
  private $template_directory = 'templates/';

  /**
   * Polls constructor.
   * Set the db variable to the Db object
   */
  public function __construct() {
    $this->db = new Db();
  } // end __construct

  /**
   * Return HTML for all polls in a list
   *
   * @return bool|string
   */
  public function viewAll() {
    // get all polls from database
    $polls = $this->db->select($this->tables['poll'], [
      'id',
      'name',
      'question',
    ], [], [['create_date', 'DESC']]);

    // send polls to template to get HTML
    return $this->template('list', [
      'polls' => $polls,
    ]);
  } // end viewAll

  /**
   * Return HTML for the specified poll with its question and answers
   *
   * @param int $poll_id
   *
   * @return bool|string
   */
  public function view($poll_id = 0) {
    $poll = $this->getPoll($poll_id);

    if ($poll === false || empty($poll)) {
      return false;
    }

    // send poll to template to get HTML
    $html = $this->template('poll', [
      'poll_id' => $poll['id'],
      'name' => $poll['name'],
      'question' => $poll['question'],
      'answers' => $poll['answers'],
    ]);

    // display polls
    return $html;
  } // end view

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
  public function create($name = '', $question = '', array $answers = []): bool {
    // loosely validate input
    if (!\is_string($name) || empty($name) || !\is_string($question) || empty($question) || !\is_array($answers) || empty($answers)) {
      $this->message = 'Invalid input';

      return false;
    }

    // prepare fields for inserting
    $fields = [
      'name' => $name,
      'question' => $question,
      'created_date' => date('Y-m-d H:i:s'),
      'created_ip' => $this->getIp(),
    ];
    // create a poll with the name and question and get the poll id
    $poll_id = $this->db->insert($this->tables['poll'], $fields);

    // validate $poll_id
    if ($poll_id === false || !\is_int($poll_id) || $poll_id <= 0) {
      $this->message = 'Error creating the poll';

      return false;
    } // end create

    // insert each answer in the array to the table using the poll id
    $valid = 0;
    foreach ($answers as $answer) {
      $aid = $this->db->insert($this->tables['answer'], [
        'poll_id' => $poll_id,
        'answer' => $answer,
      ]);
      if ($aid !== false && $aid > 0) {
        // count the number of inserted answers
        $valid++;
      }
    }

    $num_answers = \count($answers);
    // validate the inserted answers matches the provided answers
    $result = $valid === $num_answers;

    if ($result) {
      $this->message = 'Successfully created poll ' . $name . ' with ' . $num_answers . ' answers';
    }
    else {
      $this->message = 'Could not create poll';
    }

    return $result;
  } // end create

  /**
   * Save the edited poll and answers
   *
   * @param int $poll_id
   * @param string $name
   * @param string $question
   * @param array $answers
   *
   * @return bool
   */
  public function edit($poll_id = 0, $name = '', $question = '', array $answers = []): bool {
    // loosely validate input
    if (!\is_string($name) || empty($name) || !\is_string($question) || empty($question) || !\is_array($answers) || empty($answers)) {
      $this->message = 'Invalid input';

      return false;
    }

    // prepare poll fields for updating
    $fields = [
      'name' => $name,
      'question' => $question,
      'updated_date' => date('Y-m-d H:i:s'),
      'updated_ip' => $this->getIp(),
    ];
    // create a poll with the name and question and get the poll id
    $updated = $this->db->update($this->tables['poll'], $fields, [
      [
        'id',
        $poll_id,
      ],
    ], 1);

    if (!$updated) {
      $this->message = 'Could not update poll';

      return false;
    }

    $good = 0;
    // loop through answers to update
    foreach ($answers as $id => $answer) {
      // prepare answer fields for updating
      $fields = [
        'answer' => $answer,
        'updated_data' => date('Y-m-d H:i:s'),
        'updated_ip' => $this->getIp(),
      ];
      $updated = $this->db->update($this->tables['answer'], $fields, [
        [
          'id',
          $id,
        ],
      ], 1);
      if ($updated) {
        $good++;
      }
    } // end loop answers

    $success = \count($answers) === $good;
    if ($success) {
      $this->message = 'Successfully updated poll with answers';
    }
    else {
      $this->message = 'Could not update answers';
    }

    return $success;
  } // end edit

  /**
   * Return HTML of the results for this poll
   *
   * @param int $poll_id
   */
  public function results($poll_id = 0) {
    // get the results of the poll from the poll id
    // send the results through HTML template
    // display poll results
  } // end results

  /**
   * Get edit/create page HTML
   *
   * @param int $poll_id
   *
   * @return bool|string
   */
  public function getEdit($poll_id = 0) {
    $poll = $this->getPoll($poll_id);

    if ($poll === false || empty($poll)) {
      // this is probably a create request that should use default/empty values
      $poll = [
        'id' => 0,
        'name' => '',
        'question' => '',
        'answers' => [],
      ];
    }

    // send poll to template to get HTML
    $html = $this->template('edit', [
      'id' => $poll['id'],
      'name' => $poll['name'],
      'question' => $poll['question'],
      'answers' => $poll['answers'],
    ]);

    return $html;
  } // end getEdit

  /**
   * Get the poll with answers for the given ID
   *
   * @param int $poll_id
   *
   * @return array|bool|int|null|string
   */
  private function getPoll($poll_id = 0) {
    // get specific poll from database
    $poll = $this->db->select($this->tables['poll'], [
      'id',
      'name',
      'question',
    ], [['poll_id', $poll_id]], [['create_date', 'ASC']]);

    // verify the poll ID matches between what was passed and the database
    if ($poll === false || empty($poll) || $poll['id'] !== $poll_id) {
      $this->message = 'Error pulling the requested poll';

      return false;
    }

    // get answers
    $answers = $this->db->select($this->tables['answer'], [
      'id',
      'answer',
    ], [['poll_id', $poll_id]]);

    if (empty($answers) || $answers === false) {
      $this->message = 'Error pulling the answers for the requested poll';

      return false;
    }

    // fix answers
    $poll['answers'] = $this->fixAnswers($answers);

    return $poll;
  } // end getPoll

  /**
   * Get the IP address of the user
   *
   * @return mixed
   */
  private function getIp() {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
  } // end getIp

  /**
   * Process a template file with PHP variables
   *
   * @param string $template
   * @param array $values
   *
   * @return bool|string
   */
  private function template($template = '', array $values = []) {
    // verify template name does not contain any periods
    if (false !== strpos($template, '.')) {
      $this->message = 'Template name is invalid';

      return false;
    }

    // set file name
    $file_name = $this->template_directory . $template . '.php';

    // validate file name is a file
    if (!is_file($file_name)) {
      $this->message = 'Template ' . $template . ' is not an actual template file';

      return false;
    }

    // extract the values to pass to the template file
    extract($values, EXTR_OVERWRITE);

    // start output buffer
    ob_start();

    // include the file
    include $file_name;

    // get the output buffer contents
    $html = ob_get_clean();

    if (\strlen($html) > 0) {
      $this->message = 'Successfully processed template ' . $template;
    }
    else {
      $this->message = 'Could not process template ' . $template;
    }

    // return the processed HTML
    return $html;
  } // end template

  /**
   * Convert database results into array indexed by ID
   *
   * @param array $answers
   *
   * @return array|bool
   */
  private function fixAnswers(array $answers = []) {
    // verify answers is an array
    if (!\is_array($answers)) {
      $this->message = 'Answers is not in a valid format';

      return false;
    }
    // verify answers has values
    if (empty($answers)) {
      $this->message = 'Answers is empty';

      return [];
    }

    // put the answers into an array indexed by ID
    $fixed = [];
    foreach ($answers as $answer) {
      $fixed[$answer['id']] = $answer['answer'];
    }

    // set message based on result
    if (\count($fixed) > 0) {
      $this->message = 'Fixed answers';
    }
    else {
      $this->message = 'Could not fix answers';
    }

    return $fixed;
  } // end fixAnswers
} // end Polls
