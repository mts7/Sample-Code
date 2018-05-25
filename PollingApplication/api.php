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
    }
    echo $html;
    break;
  case 'delete':
    break;
  default:
    break;
}

exit;
