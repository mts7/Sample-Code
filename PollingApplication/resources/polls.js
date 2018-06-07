'use strict';

(function jQueryClosure($) {
  $(function jQueryReady() {
    /**
     * Event handler to display the create page
     * @see list.php
     */
    $(document).on('click', '.poll-create', function clickPollCreate() {
      // display the create page
      getPage('create');
    }); // end clickPollCreate

    /**
     * Event handler for viewing a specific poll
     * @see list.php
     */
    $(document).on('click', '.poll-go', function clickPollView(e) {
      // display the poll page
      getPage('poll', {id: $(e.currentTarget).data('id')});
    }); // end clickPollView

    /**
     * Event handler for displaying the edit page
     * @see list.php
     * @see poll.php
     */
    $(document).on('click', '.poll-edit', function clickPollEdit(e) {
      // display the edit page
      getPage('edit', {id: $(e.currentTarget).data('id')});
    }); // end clickPollEdit

    /**
     * Event handler for deleting a specific poll
     * @see list.php
     * @see poll.php
     */
    $(document).on('click', '.poll-delete', function clickPollDelete(e) {
      var id = $(e.currentTarget).data('id');

      // delete the poll
      var object = {
        dataType: 'json',
        data: {
          action: 'delete',
          id: id
        }
      };

      doAjax(object, function ajaxDoneDelete(data) {
        if (data.hasOwnProperty('deleted')) {
          if (data.deleted === true) {
            getPage('list');
          }
        }
      }); // end ajaxDoneDelete
    }); // end clickPollDelete

    /**
     * Event handler for saving a vote on a poll
     * @see poll.php
     */
    $(document).on('click', '.poll-answer-submit', function clickPollAnswerSubmit() {
      // get values from page
      var object = {
        dataType: 'json',
        data: {
          action: 'vote',
          pollId: $('.poll-container').data('id'),
          answerId: $('.poll-answer-input:checked').val()
        }
      };

      // send values to API
      doAjax(object, function ajaxDoneSaveAnswer(data) {
        if (data.hasOwnProperty('results')) {
          if (data.results.length > 0) {
            $('.poll-result-area').html(data.results);
          }
        }
      }); // end ajaxDoneSaveAnswer
    }); // end clickPollAnswerSubmit

    /**
     * Event handler for adding a new answer row
     * @see edit.php
     */
    $(document).on('click', '.poll-add-answer-button', function clickPollAnswer() {
      var $newAnswer = $('.poll-answer-new');
      if ($newAnswer.val().length > 0) {
        // there is a value in the new answer box, so remove the new answer class
        $newAnswer.removeClass('poll-answer-new');
        // add a new answer row for input
        $('.poll-answer-button-area').before(templateAnswer());
      }
    }); // end clickPollAnswer

    /**
     * Event handler for removing an answer and answer row
     * @see edit.php
     */
    $(document).on('click', '.poll-remove-answer-input', function clickPollRemoveAnswer(e) {
      var id = $(e.currentTarget).data('id');
      var object = {
        dataType: 'json',
        data: {
          action: 'removeAnswer',
          answerId: id
        }
      };

      // remove answer from table
      doAjax(object, function ajaxDoneRemoveAnswer(data) {
        if (data.hasOwnProperty('deleted')) {
          if (data.deleted === true) {
            // remove answer row from page
            $('.poll-answer-' + id).remove();
          }
        }
      }); // end ajaxDoneRemoveAnswer
    }); // end clickPollRemoveAnswer

    /**
     * Event handler for saving the edited form
     * @see edit.php
     */
    $(document).on('click', '.poll-edit-save-button', function clickPollEditSave() {
      // get values from page
      var params = {
        id: $('.poll-edit-container').data('id'),
        name: $('.poll-name-input').val(),
        question: $('.poll-question-input').val(),
        answers: []
      };

      $('.poll-answer-input').each(function eachAnswerInput(ai, answer) {
        var $answer = $(answer);
        var id = $answer.data('id');
        params['answers'].push({id: id, answer: $answer.val()});
      });

      var object = {
        dataType: 'json',
        data: {
          action: 'edit',
          data: params
        }
      };

      doAjax(object, function ajaxDoneEdit(data) {
        /** @param {boolean} data.edited */
        if (data.hasOwnProperty('edited')) {
          if (data.edited === true) {
            getPage('poll', {id: params.id});
          }
          else {
            if (data.edited !== false) {
              // data.edited must be an integer ID of the newly created poll
              getPage('poll', {id: data.edited});
            }
          }
        }
      }); // end ajaxDoneEdit
    }); // end clickPollEditSave

    /**
     * Event handler for canceling the edit/create or vote
     * @see edit.php
     * @see poll.php
     */
    $(document).on('click', '.poll-cancel-button', function clickPollEditCancel() {
      getPage('list');
    }); // end clickPollEditCancel

    /**
     * AJAX wrapper
     * @param {{}} object
     * @param {function} callback
     */
    function doAjax(object, callback) {
      $.ajax(Object.assign({
        url: '/api.php',
        type: 'POST'
      }, object))
        .done(function ajaxDone(data) {
          if (data.hasOwnProperty('message')) {
            $('.poll-message-area').html(data.message);
          }
          callback(data);
        });
    } // end doAjax

    /**
     * Redraw the page
     * @param page
     * @param params
     */
    function getPage(page, params) {
      var object = {
        dataType: 'html',
        data: {
          action: 'page',
          page: page,
          params: params
        }
      };
      doAjax(object, function ajaxDoneCreate(data) {
        $('#page-content').html(data);
      });
    } // end getPage

    /**
     * Get the HTML for an answer row
     * @returns {string}
     */
    function templateAnswer() {
      var html = '';

      html += '<div class="poll-answer-area">\n';
      html += '  <input type="text" maxlength="255" name="poll-answer" class="poll-answer-input poll-input poll-input-text poll-answer-new" placeholder="Poll Answer" value="" />\n';
      html += '</div> <!-- end .poll-answer-area -->\n';

      return html;
    } // end templateAnswer
  }); // end jQueryReady
})(jQuery); // end jQueryClosure
