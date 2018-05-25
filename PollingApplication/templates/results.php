<div class="poll-results-area">
  <?php foreach ($answer_ids as $id => $count) : ?>
  <div class="poll-result-answer" data-id="<?php echo $id; ?>">
    <div class="poll-result-answer-count" data-percent="<?php echo $count / $total_answers; ?>">
      <?php echo $count; ?>
    </div> <!-- end .poll-result-answer-count -->
  </div> <!-- end .poll-result-answer -->
  <?php endforeach; ?>
</div> <!-- end .poll-results-area -->
