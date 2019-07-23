<div class="comment-form">
	<h2>Post New Comment</h2>
	<div class="error-box" id="post-comment-error" style="display:none"></div>
	<div><textarea id="post-comment-body" placeholder="Your Comment"></textarea></div>
	<div><button type="submit" onclick="PostComment()" class="normal-button">Post Comment</button></div>
</div>

<div class="comments-area" id="submission-comments">
<?php 
	$comments = $ovComment->GetComments($ovoSubmission->ID()); 
	
	if ($comments && count($comments) > 0)
	{
		foreach ($comments as $comment)	{
			$ovoComment = new ovoComment($comment);
			$comment_replies = $ovComment->GetCommentReplies($ovoComment->ID());
			$top_comment = $ovoComment;
			
			$reply_to_id = $ovoComment->ID();
			include (get_comment());
?>
			<div class="comment-replies-first-level" id="replies-div-<?php echo $ovoComment->ID(); ?>">
<?php
			if ($comment_replies) {
				foreach ($comment_replies as $reply) {
					
					$ovoComment = new ovoComment($reply);
					$second_replies = $ovComment->GetCommentReplies($ovoComment->ID());
					$reply_to_id = $ovoComment->ID();
					$first_reply = $ovoComment;

					include (get_comment());
?>
				<div class="comment-replies-second-level" id="replies-div-<?php echo $ovoComment->ID(); ?>">
<?php
					if ($second_replies) {
						foreach ($second_replies as $second_reply) {
							$reply_to_id = $first_reply->ID();
							$ovoComment = new ovoComment($second_reply);
							include (get_comment());
						}
					}
?>
				</div>
<?php
				}
			}
?>
			</div>
<?php
		}
	} else {
?>
		<div class="comment" id="no-replies">There doesn't seem to be anything here, why not add your thoughts.</div>
<?php
	}
?>
</div>

<div style="display:none">
	<div id="comment-reply-form">
		<a class="fancybox-close-button" onclick="closePopup()"></a>
		<h1>Post a Reply</h1>
		<div class="error-box" id="comment-reply-error" style="display:none"></div>
		<div class="reply-user"><span id="reply-user"></span> Posted:</div>
		<div class="reply-body" id="reply-body"></div>
		<textarea id="reply-text"></textarea>
		<input type="hidden" id="reply-id" />
		<button onclick="PostCommentReply()" class="normal-button">Post Reply</button>
	</div>
</div>

<input type="hidden" id="comment-to-edit-id" value="" />
<textarea id="edit-comment-temp-body" style="display:none"></textarea>