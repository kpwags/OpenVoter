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

$feedback = $ovAdminContent->GetFeedback();
?>
<h1>Feedback</h1>
<?php if ($feedback && count($feedback) > 0) { ?>
	<table width="710" border="0" cellspacing="0" cellpadding="0" class="grid_table">
		<thead>
			<tr>
				<th width="200">From</th>
				<th width="170">Date</th>
				<th width="140">About</th>
				<th width="100">&nbsp;</th>
				<th width="100">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($feedback as $fb) { ?>
				<tr <?php if ($fb['unread']) { echo "class=\"unread\""; } ?>>
					<td><?php echo $fb['name']; ?></td>
					<td><?php echo $fb['date']; ?></td>
					<td><?php echo $fb['reason']; ?></td>
					<td><img src="/ov-admin/img/icons/forward.png" alt="">&nbsp;<a href="/ov-admin/feedback?id=<?php echo $fb['id']; ?>" title="View">View</a></td>
					<td><img src="/ov-admin/img/icons/delete.png" alt="">&nbsp;<a onclick="return ConfirmAction('Are you sure you want to delete this message?')" href="/ov-admin/php/delete_feedback.php?id=<?php echo $fb['id']; ?>" title="Delete">Delete</a></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<div class="margin_tb_15">No Feedback.</div>
<?php } ?>