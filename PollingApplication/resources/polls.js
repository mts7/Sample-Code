'use strict';

(function jQueryClosure($) {
  $(function jQueryReady() {
    $(document).on('click', '.poll-create', function clickPollCreate() {
      // display the create page
      getPage('create');
    }); // end clickPollCreate

    $(document).on('click', '.poll-go', function clickPollView(e) {
      // display the poll page
      let pollId = $(e.currentTarget).data('id');
      getPage('poll', {id: pollId});
    });

    $(document).on('click', '.poll-edit', function clickPollEdit(e) {
      // display the edit page
      let pollId = $(e.currentTarget).data('id');
      getPage('edit', {id: pollId});
    });

    $(document).on('click', '.poll-delete', function clickPollDelete(e) {
      // delete the poll
      let pollId = $(e.currentTarget).data('id');
      deletePoll(pollId);
    });
    
    $(document).on('click', '.poll-add-answer-button', function clickPollAnswer() {
      let $newAnswer = $('.poll-answer-new');
      if ($newAnswer.val().length > 0) {
        // there is a value in the new answer box, so remove the new answer class
        $newAnswer.removeClass('poll-answer-new');
        // add a new answer row for input
        $('.poll-answer-button-area').before(templateAnswer());
      }
    });

    $(document).on('click', '.poll-edit-save-button', function clickPollEditSave(e) {
      let data = {};
      // get values from page
      data['id'] = $('.poll-edit-container').data('id');
      data['name'] = $('.poll-name-input').val();
      data['question'] = $('.poll-question-input').val();
      data['answers'] = [];

      $('.poll-answer-input').each(function eachAnswerInput(ai, answer) {
        let $answer = $(answer);
        let id = $answer.data('id');
        data['answers'].push({id: id, answer: $answer.val()});
      });

      editPoll(data);
    });

    /**
     * Redraw the page
     * @param page
     * @param params
     */
    function getPage(page, params) {
      $.ajax({
        url: '/api.php',
        type: 'POST',
        data: {
          action: 'page',
          page: page,
          params: params
        }
      })
        .done(function ajaxDoneCreate(data) {
          $('#page-content').html(data);
        });
    } // end getPage

    /**
     * Delete a poll with AJAX and redraw the page
     * @param id
     */
    function deletePoll(id) {
      $.ajax({
        url: '/api.php',
        type: 'POST',
        data: {
          action: 'delete',
          id: id
        }
      })
        .done(function ajaxDoneDelete(data) {
          // if successful, display brief success message and then display all polls
          // if not successful, display error message
        });
    } // end deletePoll

    function editPoll(params) {
      $.ajax({
        url: '/api.php',
        type: 'POST',
        data: {
          action: 'edit',
          data: params
        }
      })
        .done(function ajaxDoneEdit(data) {
          // if successful, display brief success message and then display this poll again
          // if not successful, display error message
        });
    } // end editPoll

    /**
     * Get the HTML for an answer row
     * @returns {string}
     */
    function templateAnswer () {
      let html = '';

      html += '<div class="poll-answer-area">\n';
      html += '  <input type="text" maxlength="255" name="poll-answer" class="poll-answer-input poll-input poll-input-text poll-answer-new" placeholder="Poll Answer" value="" />\n';
      html += '</div> <!-- end .poll-answer-area -->\n';
      
      return html;
    }
  }); // end jQueryReady
})(jQuery); // end jQueryClosure
