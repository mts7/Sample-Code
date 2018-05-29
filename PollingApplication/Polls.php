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
   * @param int|bool $poll_id
   * @param string $name
   * @param string $question
   * @param array $answers
   *
   * @return bool|int
   */
  public function edit($poll_id = 0, $name = '', $question = '', array $answers = []): bool {
    // loosely validate input
    if (!\is_string($name) || empty($name) || !\is_string($question) || empty($question) || !\is_array($answers) || empty($answers)) {
      $this->message = 'Invalid input';

      return false;
    }

    $insert = $poll_id === 0;

    if ($insert === false) {
      // prepare poll fields for updating
      $fields = [
        'name' => $name,
        'question' => $question,
        'updated_date' => date('Y-m-d H:i:s'),
        'updated_ip' => $this->getIp(),
      ];
      // update a poll with the name and question
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
    }
    else {
      // create a new poll and get the poll ID
      // prepare poll fields for updating
      $fields = [
        'name' => $name,
        'question' => $question,
        'created_date' => date('Y-m-d H:i:s'),
        'created_ip' => $this->getIp(),
      ];
      $poll_id = $this->db->insert($this->tables['poll'], $fields);

      if ($poll_id === false) {
        $this->message = 'Could not create a new poll';

        return false;
      }

      // there is a poll ID, so insert the answers, too

      $good = 0;
      // loop through answers to update
      foreach ($answers as $id => $answer) {
        // prepare answer fields for updating
        $fields = [
          'poll_id' => $poll_id,
          'answer' => $answer,
          'created_data' => date('Y-m-d H:i:s'),
          'created_ip' => $this->getIp(),
        ];
        $answer_id = $this->db->insert($this->tables['answer'], $fields);
        if ($answer_id !== false) {
          $good++;
        }
      } // end loop answers
    }

    // set message based on success
    $success = \count($answers) === $good;
    $verb = $insert ? 'created' : 'updated';
    if ($success) {
      $this->message = 'Successfully ' . $verb . ' poll with answers';
    }
    else {
      $this->message = 'Could not handle answers';
    }

    return $insert ? $poll_id : $success;
  } // end edit

  /**
   * Delete the poll with the provided ID
   *
   * @param int $poll_id
   *
   * @return array|bool|int|null|string
   */
  public function delete($poll_id = 0) {
    // loosely validate poll_id
    if (!\is_int($poll_id) || $poll_id === 0) {
      $this->message = 'Invalid poll ID provided to delete';

      return false;
    }

    // delete the answers
    $deleted = $this->db->delete($this->tables['answer'], [
      [
        'poll_id',
        $poll_id,
      ],
    ], 0);

    if (!$deleted) {
      $this->message = 'Could not delete the answers for the poll';

      return false;
    }

    // delete the poll
    $deleted = $this->db->delete($this->tables['poll'], [['id', $poll_id]], 1);

    // set messages
    if (!$deleted) {
      $this->message = 'Error deleting a poll';
    }
    else {
      $this->message = 'Successfully deleted the poll and answers';
    }

    return $deleted;
  } // end delete

  /**
   * Save the vote for the poll with the answer
   *
   * @param int $poll_id
   * @param int $answer_id
   *
   * @return bool|int
   */
  public function vote($poll_id = 0, $answer_id = 0) {
    // loosely validate the poll and answer IDs
    if (!\is_int($poll_id) || $poll_id === 0 || !\is_int($answer_id) || $answer_id === 0) {
      $this->message = 'Could not save the vote';

      return false;
    }

    // get the user ID
    $user_id = $this->getUserId();

    // determine if this is the first vote for the user
    $insert = $this->userCanVote($user_id, $poll_id);

    if ($insert === true) {
      // prepare fields and values for insert
      $fields = [
        'poll_id' => $poll_id,
        'answer_id' => $answer_id,
        'taker_hash' => $user_id,
        'created_date' => date('Y-m-d H:i:s'),
        'created_ip' => $this->getIp(),
      ];

      // add this vote to the table
      $result = $this->db->insert($this->tables['result'], $fields);

      // get message from result
      if ($result === false) {
        $this->message = 'Error inserting into database';
      }
      else {
        if (!\is_int($result) || $result === 0) {
          $this->message = 'Could not insert into table';
        }
        else {
          $this->message = 'Successfully inserted vote for poll';
          $expire = 60 * 60 * 24 * 365;
          setcookie('poll_' . $poll_id, $result, $expire, '/');
        }
      }
    }
    else {
      // prepare fields and values for update
      $set = [
        'answer_id' => $answer_id,
        'updated_date' => date('Y-m-d H:i:s'),
        'updated_ip' => $this->getIp(),
      ];
      $result = $this->db->update($this->tables['result'], $set, [['poll_id' => $poll_id]], 1);

      // set message from result
      if ($result === false) {
        $this->message = 'Could not update the answer';
      }
      else {
        $this->message = 'Successfully updated the vote for poll';
      }
    }

    return $result;
  } // end vote

  /**
   * Return HTML of the results for this poll
   *
   * @param int $poll_id
   *
   * @return string
   */
  public function results($poll_id = 0): string {
    // get the results of the poll from the poll id
    $results = $this->db->select($this->tables['result'], ['answer_id'], [
      [
        'poll_id',
        $poll_id,
      ],
    ], [['created_date', 'ASC']]);

    // instead of adding a group by clause to the Db class, loop through the results
    $grouped_ids = [];
    foreach ($results as $id) {
      if (!array_key_exists($id, $grouped_ids)) {
        $grouped_ids[$id] = 0;
      }

      $grouped_ids[$id]++;
    }

    // send the results through HTML template
    $html = $this->template('results', [
      'answer_ids' => $grouped_ids,
      'total_answers' => \count($results),
    ]);

    if ('' === $html) {
      $this->message = 'Could not get HTML from template';
    }
    else {
      $this->message = 'Successfully created the HTML with the results';
    }

    // display poll results
    return $html;
  } // end results

  /**
   * Remove an answer from a poll
   *
   * @param int $answer_id
   *
   * @return array|bool|int|null|string
   */
  public function removeAnswer($answer_id = 0) {
    // loosely validate input
    if (!\is_int($answer_id) || $answer_id === 0) {
      $this->message = 'Invalid answer ID';

      return false;
    }

    // execute query
    $deleted = $this->db->delete($this->tables['answer'], [
      [
        'id',
        $answer_id,
      ],
    ], 1);

    // set messages
    if ($deleted === true) {
      $this->message = 'Successfully deleted answer with ID ' . $answer_id;
    }
    else {
      $this->message = 'Error deleting answer';
    }

    return $deleted;
  } // end removeAnswer

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
   * Accessor method to get the last message
   *
   * @return string
   */
  public function getMessage(): string {
    return $this->message;
  } // end getMessage

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
   * Generate an user string to attempt to identify the user
   *
   * @return string
   */
  private function getUserId(): string {
    $ip = $this->getIp();
    $ua = $_SERVER['HTTP_USER_AGENT'];

    return session_id() . '|' . $ip . '|' . $ua;
  } // end getUserId

  /**
   * Determine if this user has already voted for this poll
   *
   * @param string $user_id
   * @param int $poll_id
   *
   * @return bool
   */
  private function userCanVote($user_id = '', $poll_id = 0): bool {
    $result = $this->db->select($this->tables['result'], ['id'], [
      [
        'poll_id',
        $poll_id,
      ],
      ['taker_hash', $user_id],
    ], [], 'single');

    return $result === false || empty($result);
  } // end userCanVote

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

  /**
   * Get the header template
   *
   * @return bool|string
   */
  public function getHeader() {
    return $this->template('header');
  } // end getHeader

  /**
   * Get the footer template
   *
   * @return bool|string
   */
  public function getFooter() {
    return $this->template('footer');
  } // end getFooter
} // end Polls
