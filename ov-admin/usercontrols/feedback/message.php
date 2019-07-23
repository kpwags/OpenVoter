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

$feedback_message = $ovAdminContent->GetFeedbackMessage($feedback_id);
$ovAdminContent->MarkFeedbackRead($feedback_id);
?>
<h1>Feedback Message</h1>

<div><strong>From:</strong> <?php echo $feedback_message['name']; ?></div>
<div><strong>Date:</strong> <?php echo $feedback_message['date']; ?></div>
<div><strong>Email:</strong> <a href="mailto:<?php echo strtolower($feedback_message['email']); ?>"><?php echo $feedback_message['email']; ?></a></div>
<div><strong>Reason:</strong> <?php echo $feedback_message['reason']; ?></div>
<div class="bold">The Message</div><div><?php echo $feedback_message['message']; ?></div>

<div class="add_row">
	<img src="/ov-admin/img/icons/letter.png" alt=""><a href="/ov-admin/php/mark_feedback_unread.php?id=<?php echo $feedback_message['id']; ?>" title="Mark as Unread">Mark as Unread</a>
</div>
<div class="add_row">
	<img src="/ov-admin/img/icons/delete.png" alt=""><a href="/ov-admin/php/delete_feedback.php?id=<?php echo $feedback_message['id']; ?>" title="Delete">Delete</a>
</div>
<div class="add_row">
	<img src="/ov-admin/img/icons/back.png" alt=""><a href="/ov-admin/feedback" title="Back to Messages">Back to Messages</a>
</div>