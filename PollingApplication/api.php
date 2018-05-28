<?php
/**
 * @file
 *
 * Handle AJAX requests
 *
 * @author Mike Rodarte
 */

$polls = new \Polls\Polls();

// do AJAX handling
$action = '';
if (array_key_exists('action', $_POST)) {
  $action = $_POST['action'];
}

switch ($action) {
  case 'page':
    $page = array_key_exists('page', $_POST) ? $_POST['page'] : '';
    $html = '';
    switch ($page) {
      case 'create':
        $html = $polls->getEdit(0);
        break;
      case 'poll':
        $params = array_key_exists('params', $_POST) ? $_POST['params'] : [];
        // TODO: make sure params is an array
        $id = $params['id'] ?? 0;
        if ($id > 0) {
          $html = $polls->view($id);
        }
        break;
      case 'edit':
        $params = array_key_exists('params', $_POST) ? $_POST['params'] : [];
        // TODO: make sure params is an array
        $id = $params['id'] ?? 0;
        if ($id > 0) {
          $html = $polls->getEdit($id);
        }
        break;
      case 'list':
        $html = $polls->viewAll();
        break;
    }
    echo $html;
    break;
  case 'vote':
    $poll_id = array_key_exists('pollId', $_POST) ? (int) $_POST['pollId'] : 0;
    $answer_id = array_key_exists('answerId', $_POST) ? (int) $_POST['answerId'] : 0;
    $result = $polls->vote($poll_id, $answer_id);
    $poll_results = '';
    if ($result !== false) {
      // get results for poll
      $poll_results = $polls->results($poll_id);
    }
    echo json_encode(['result' => $result, 'results' => $poll_results, 'message' => $polls->getMessage()]);
    break;
  case 'delete':
    $id = array_key_exists('id', $_POST) ? $_POST['id'] : 0;
    $deleted = $polls->delete($id);
    echo json_encode(['deleted' => $deleted, 'message' => $polls->getMessage()]);
    break;
  case 'edit':
    // get parameters from POST
    $params = array_key_exists('params', $_POST) ? $_POST['params'] : 0;

    // set up the whitelist of keys
    $expected_keys = ['id', 'name', 'question', 'answers'];

    // set default values
    $id = 0;
    $name = '';
    $question = '';
    $answers = [];

    // set valid to true
    $valid = true;
    // loop through whitelist of keys and do loose validation
    foreach ($expected_keys as $key) {
      if (!array_key_exists($key, $_POST)) {
        $valid = false;
        break;
      }
      switch ($key) {
        case 'id':
          $id = (int) $_POST['id'];
          break;
        case 'name':
          $name = is_string($_POST['name']) ? $_POST['name'] : '';
          break;
        case 'question':
          $question = is_string($_POST['question']) ? $_POST['question'] : '';
          break;
        case 'answers':
          $answers = is_array($_POST['answers']) ? $_POST['answers'] : '';
          break;
      }
    } // end loop
    $success = $polls->edit($id, $name, $question, $answers);
    echo json_encode(['edited' => $success, 'message' => $polls->getMessage()]);
    break;
  case 'removeAnswer':
    $answer_id = array_key_exists('answerId', $_POST) ? (int) $_POST['answerId'] : 0;
    $removed = $polls->removeAnswer($answer_id);
    echo json_encode(['deleted' => $removed, 'message' => $polls->getMessage()]);
    break;
  default:
    break;
}

exit;
