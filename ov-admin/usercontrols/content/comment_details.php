<?php
/*
	Copyright 2008-2010 OpenVoter
	
	This file is part of OpenVoter.

	OpenVoter is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, version 3.

	OpenVoter is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with OpenVoter.  If not, see <http://www.gnu.org/licenses/>.
*/
	$comment_details = $ovAdminContent->GetCommentDetails($content_id);

?>
<h1>Comment Details</h1>
<?php if ($comment_details) { ?>	
	<div class="report-details">
		<div class="report-comment-submission"><a href="<?php echo $comment_details['page_url']; ?>#comment-<?php echo $comment_details['comment_id']; ?>" target="_blank"><?php echo $comment_details['title']; ?></a></div>
		<div class="submission-comment-date"><?php echo $comment_details['date']; ?></div>
		<div class="report-comment-body"><?php echo $comment_details['body']; ?></div>
		<div class="report-comment-user"><a href="/users/<?php echo strtolower($comment_details['username']); ?>" target="_blank"><?php echo $comment_details['username']; ?></a></div>
	</div>

	<h3>Actions</h3>
	<div>
		<a onclick="return ConfirmAction('Are you sure you want to delete this comment?')" href="/ov-admin/php/delete_comment.php?comment_id=<?php echo $comment_details['id']; ?>" title="Remove" class="cancel-button">Remove</a>
	</div>
<?php } else { ?>
	<div class="error_text">No Comment Found.</div>
<?php } ?>