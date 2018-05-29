<div class="poll-create-area">
  <input type="button" value="Create New Poll" class="poll-input poll-input-button poll-create"/>
</div> <!-- end .poll-create-area -->
<div class="poll-list-container">
  <h1 class="poll-list-header">Available Polls</h1>
  <div class="poll-list">
    <?php foreach ($polls as $poll) : ?>
      <div class="poll-container">
        <h2 class="poll-name"><?php echo $poll['name']; ?></h2>
        <h3 class="poll-question"><?php echo $poll['question']; ?></h3>
        <input type="button" value="View Poll" id="poll_view_<?php echo $poll['id']; ?>" class="poll-input poll-input-button poll-go" data-id="<?php echo $poll['id']; ?>"/>
        <input type="button" value="Edit Poll" id="poll_edit_<?php echo $poll['id']; ?>" class="poll-input poll-input-button poll-edit" data-id="<?php echo $poll['id']; ?>"/>
        <input type="button" value="Delete Poll" id="poll_delete_<?php echo $poll['id']; ?>" class="poll-input poll-input-button poll-delete" data-id="<?php echo $poll['id']; ?>"/>
      </div> <!-- end poll-container -->
    <?php endforeach; ?>
  </div> <!-- end .poll-list -->
</div> <!-- end .poll-list-container -->
