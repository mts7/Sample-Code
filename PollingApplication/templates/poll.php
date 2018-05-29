<div class="poll-container" id="poll_<?php echo $poll_id; ?>" data-id="<?php echo $poll_id; ?>">
  <h1 class="poll-name"><?php echo $name; ?></h1>
  <input type="button" value="Edit Poll" id="poll_edit_<?php echo $poll_id; ?>" class="poll-input poll-input-button poll-edit" data-id="<?php echo $poll_id; ?>"/>
  <input type="button" value="Delete Poll" id="poll_delete_<?php echo $poll_id; ?>" class="poll-input poll-input-button poll-delete" data-id="<?php echo $poll_id; ?>"/>
  <h2 class="poll-question"><?php echo $question; ?></h2>
  <ul class="poll-answers">
    <?php foreach ($answers as $answer_id => $answer): ?>
      <li class="poll-answer-item" id="poll-answer-<?php echo $answer_id; ?>">
        <label><?php echo $answer; ?>
          <input type="radio" name="poll-answer" class="poll-answer-input poll-input poll-input-radio" value="<?php echo $answer_id; ?>"/>
        </label>
      </li> <!-- end .poll-answer-item -->
    <?php endforeach; ?>
  </ul> <!-- end .poll-answers -->
  <div class="poll-submit-area">
    <input type="button" name="poll-answer-submit" class="poll-submit poll-input poll-input-button poll-answer-submit" value="Vote"/>
    <input type="button" name="poll-vote-cancel" class="poll-cancel poll-input poll-input-button poll-vote-cancel-button poll-cancel-button" value="Cancel"/>
  </div> <!-- end .poll-submit-area -->
  <div class="poll-message-area">

  </div> <!-- end .poll-message-area -->
</div> <!-- end .poll-container -->
