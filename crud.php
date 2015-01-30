<?php
function is_valid_user() {
    return strlen($_SERVER['HTTP_HOST']) > 0;
}


if (!is_valid_user()) {
    die('You are unauthorized to use this script.');
}


define('NL', "\n");
$_SESSION['last_insert_id'] = 0;


$action = isset($_POST['action']) ? $_POST['action'] : '';

// create the new Customer object
$customer = new Customer();

// determine what the API should handle
switch ($action) {
    case 'create':
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $number = isset($_POST['number']) ? $_POST['number'] : 0;
        $row = !empty($name) && $number > 0 ? $customer->create($name, $number) : 'Invalid parameters provided.';
        echo $row;
        exit(1);
        break;
    case 'delete':
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        if ($id > 0) {
            $result = $customer->delete($id);
            echo $result;
        }
        exit(1);
        break;
    case 'edit':
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $number = isset($_POST['number']) ? $_POST['number'] : 0;
        $row = !empty($name) && $number > 0 ? $customer->update($id, $name, $number) : 'Invalid parameters provided.';
        echo $row;
        exit(1);
        break;
    case 'load':
        echo $customer->read();
        exit(1);
        break;
}

class Customer {
    /**
     * @var PDOWrapper $db
     */
    private $db = false;
    /**
     * @var string $error
     */
    private $error = '';


    public function __construct() {
        if (file_exists('PDOWrapper.php')) {
            // see https://github.com/mts7/PDOWrapper/blob/master/PDOWrapper.php
            require_once 'PDOWrapper.php';
            $this->db = new PDOWrapper();

            $this->setLastInsertId();
        }
    }


    public function buildRow($name, $number, $id, $added, $modified) {
        if (!is_string($name) || !is_numeric($number) || !is_numeric($id) || !is_string($added) || !is_string($modified)) {
            $this->error = 'Invalid parameter format.';
            return $this->error;
        }

        // this would usually be done elsewhere, but this is for a single file application
        $html = '<tr id="row_'.$id.'">'.NL;
        $html .= '    <td class="edit" data-id="'.$id.'">Edit</td>'.NL;
        $html .= '    <td class="id">'.$id.'</td>'.NL;
        $html .= '    <td class="name">'.$name.'</td>'.NL;
        $html .= '    <td class="number">'.$number.'</td>'.NL;
        $html .= '    <td class="added">'.$added.'</td>'.NL;
        $html .= '    <td class="modified">'.$modified.'</td>'.NL;
        $html .= '    <td class="delete" data-id="'.$id.'">Delete</td>'.NL;
        $html .= '</tr>'.NL;

        return $html;
    }


    /**
     * Create a customer record in the database table and return the HTML row for the table
     * @param string $name Customer name
     * @param int $number Customer number
     * @return string HTML table row
     * @uses PDOWrapper::insert()
     * @uses Customer::buildRow()
     */
    public function create($name, $number) {
        if (!is_string($name) || !is_numeric($number)) {
            $this->error = 'Invalid parameter format.';
            return $this->error;
        }
        $added = date('Y-m-d H:i:s');
        if (is_object($this->db)) {
            $insert_id = $this->db->insert('customers', array('name' => $name, 'number' => $number, 'added' => $added, 'modified' => $added));
            if ($insert_id > 0) {
                $_SESSION['last_insert_id']++;
                $row = $this->buildRow($name, $number, $insert_id, $added, $added);
            }
        } else {
            // assume there is no database class, so we return the formatted string
            $_SESSION['last_insert_id']++;
            $row = $this->buildRow($name, $number, $_SESSION['last_insert_id'], $added, $added);
        }

        return $row;
    }

    /*
     * Delete the table row with the specified ID
     * @param int $id Customer ID to delete
     * @return string Status
     * @uses PDOWrapper::delete()
     */
    public function delete($id) {
        if (!is_numeric($id)) {
            $this->error = 'Invalid parameter.';
            return $this->error;
        }

        if (is_object($this->db)) {
            $deleted = $this->db->delete('customers', array('id' => $id));
            if ($deleted) {
                return 'deleted';
            } else {
                return $this->db->lastError();
            }
        } else {
            // there is no database object, so keep going as if there was
            return 'deleted';
        }
    }


    /**
     * Returns the last error message (for debugging purposes)
     * @return string Error message
     */
    public function lastError() {
        return $this->error;
    }


    /**
     * Read all customers from the database table and return them in HTML format to the API.
     * @uses PDOWrapper::select()
     * @uses Customer::buildRow()
     */
    public function read() {
        if (is_object($this->db)) {
            $q = 'SELECT * FROM customers';
            $rows = $this->db->select($q);
            if (is_array_ne($rows)) {
                $html = '';
                foreach($rows as $row) {
                    $html .= $this->buildRow($row['name'], $row['number'], $row['id'], $row['added'], $row['modified']);
                }
                return $html;
            } else {
                $this->error = 'No customers found in table.';
                return '';
            }
        } else {
            return '';
        }
    }


    /**
     * Set the last insert ID from the database if there is a database object available.
     * @uses PDOWrapper::select()
     */
    public function setLastInsertId() {
        if (!is_object($this->db)) {
            // use the one that was set before the class was instantiated
            return true;
        }

        // get the highest insert id from the database table
        $rows = $this->db->select('SELECT MAX(id) AS "last" FROM customers');
        if (is_array_ne($rows)) {
            $row = $rows[0];
            $_SESSION['last_insert_id'] = $row['last'];
            return true;
        }

        return false;
    }


    /**
     * Update a table record with the specified values.
     * @param int $id Customer ID
     * @param string $name Customer name
     * @param int $number Customer number
     * @return string Modified date or error message
     * @uses PDOWrapper::update()
     */
    public function update($id, $name, $number) {
        if (!is_numeric($id) || $id == 0 || !is_string($name) || !is_numeric($number) || empty($name) || empty($number)) {
            $this->error = 'Invalid parameters.';
            return $this->error;
        }

        $modified = date('Y-m-d H:i:s');
        if (is_object($this->db)) {
            $updated = $this->db->update('customers', array('name' => $name, 'number' => $number, 'modified' => $modified), $id);
            if ($updated) {
                return $modified;
            } else {
                return $this->db->lastError();
            }
        } else {
            // there is no database object, so return the modified date
            return $modified;
        }
    }
} // end class Customer
?>
<html>
    <head>
        <title>OOP CRUD Demonstration by Mike Rodarte</title>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <style type="text/css">
body {
    width: 100%;
}

#customer {
    max-width: 1280px;
    width: 100%;
}

#add_link, .edit, .delete {
    cursor: pointer;
}

#add_form {
    display: none;
}

label {
    display: inline-block;
    width: 20%;
}
        </style>
        <script type="text/javascript">
function loadTable() {
    $.ajax({
        url: 'crud.php',
        type: 'POST',
        dataType: 'html',
        data: {
            action: 'load'
        }
    })
    .done(function(data) {
        $('#customer').find('tbody').html(data);
    });
}


$(function() {
    loadTable();

    $('#add_link').on('click', function() {
        $('#id').val('new');
        $('#add_form').show();
    });

    $('#button_add').on('click', function() {
        // get values from form
        var id = $('#id').val();
        var name = $('#name').val();
        var number = $('#number').val();

        // check for length of values
        if (name.length === 0 || number.length === 0) {
            // display error message if either of the values is empty
            $('#error_message').html('Please enter values for both Name and Number and try again.');
            return false;
        }

        var action = 'edit';
        if (id === 'new') {
            action = 'create';
        }

        $.ajax({
            url: 'crud.php',
            type: 'POST',
            dataType: 'html',
            data: {
                action: action,
                id: id,
                name: name,
                number: number
            }
        })
        .done(function(data) {
            if (data.indexOf('tr') >= 0) {
                $('#customer').find('tbody').append(data);
                $('#add_form').hide();
            } else if (data.length === 19) {
                // this is the modified date in YYYY-MM-DD HH:MM:SS format
                var $row = $('#row_' + id);
                $row.find('.name').text(name);
                $row.find('.number').text(number);
                $row.find('.modified').text(data);
                $('#id').val('new');
                $('#name').val('');
                $('#number').val('');
                $('#add_form').hide();
            } else {
                $('#error_message').html('There was an error processing your request: ' + data);
            }
        });
    });
    // end button_add click

    $(document).on('click', '.edit', function(e) {
        var $target = $(e.currentTarget);
        var id = $target.data('id');
        var $row = $('#row_' + id);
        $('#id').val(id);
        $('#name').val($row.find('.name').text());
        $('#number').val($row.find('.number').text());
        $('#add_form').show();
    });
    // end edit click

    $(document).on('click', '.delete', function(e) {
        var $target = $(e.currentTarget);
        var id = $target.data('id');

        $.ajax({
            url: 'crud.php',
            type: 'POST',
            data: {
                action: 'delete',
                id: id
            }
        })
        .done(function(data) {
            if (data === 'deleted') {
                $('#row_' + id).remove();
            } else {
                $('#error_message').html('There was an error processing your request: ' + data);
            }
        });
    });
    // end delete click
});
        </script>
    </head>
    <body>
<table id="customer">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th>ID</th>
            <th>Name</th>
            <th>Number</th>
            <th>Added</th>
            <th>Modified</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<div id="add_area">
    <div id="add_link">Add Customer</div>
    <div id="add_form">
        <input type="hidden" name="id" id="id" value="new" />
        <div class="add-row">
            <label for="name">Name</label> <input type="text" name="name" id="name" maxlength="64" placeholder="Customer Name" size="40" />
        </div>
        <div class="add-row">
            <label for="number">Number</label> <input type="text" name="number" id="number" maxlength="8" placeholder="Customer Number" size="40" />
        </div>
        <div class="add-row">
            <input type="button" id="button_add" value="Save" />
        </div>
        <div class="add-row">
            <div id="error_message"></div>
        </div>
    </div>
</div>
    </body>
</html>