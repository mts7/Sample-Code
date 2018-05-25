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

    function deletePoll(id) {
      $.ajax({
        url: '/api.php',
        type: 'POST',
        data: {
          action: 'delete',
          id: id
        }
      });
    } // end deletePoll
  }); // end jQueryReady
}(jQuery); // end jQueryClosure
