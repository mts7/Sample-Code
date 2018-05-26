<div class="poll-edit-container" data-id="<?php echo $id; ?>">
  <div class="poll-name-area">
    <input type="text" maxlength="255" name="poll-name" class="poll-name-input poll-input poll-input-text" placeholder="Poll Name" value="<?php echo $name; ?>" />
  </div> <!-- end .poll-name-area -->
  <div class="poll-question-area">
    <input type="text" maxlength="255" name="poll-question" class="poll-question-input poll-input poll-input-text" placeholder="Poll Question" value="<?php echo $question; ?>" />
  </div> <!-- end .poll-question-area -->
  <div class="poll-answers-area">
    <?php foreach ($answers as $answer_id => $answer) : ?>
    <div class="poll-answer-area">
      <input type="text" maxlength="255" name="poll-answer" class="poll-answer-input poll-input poll-input-text" placeholder="Poll Answer" value="<?php echo $answer; ?>" data-id="<?php echo $answer_id; ?>" />
    </div> <!-- end .poll-answer-area -->
    <?php endforeach; ?>
    <div class="poll-answer-area">
      <input type="text" maxlength="255" name="poll-answer" class="poll-answer-input poll-input poll-input-text poll-answer-new" placeholder="Poll Answer" value="" />
    </div> <!-- end .poll-answer-area -->
    <div class="poll-answer-button-area">
      <input type="button" value="Add Answer" class="poll-input poll-input-button poll-add-answer-button" />
    </div> <!-- end .poll-answer-button-area -->
  </div> <!-- end .poll-answers-area -->
  <div class="poll-submit-area">
    <input type="button" name="poll-edit-submit" class="poll-submit poll-input poll-input-button poll-edit-save-button" value="Save" />
  </div> <!-- end .poll-submit-area -->
  <div class="poll-message-area">

  </div> <!-- end .poll-message-area -->
</div> <!-- end .poll-edit-container -->
