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
	if (isset($_GET['p'])) {
		$current_page = $_GET['p'];
	} else {
		$current_page = 1;
	}

	$num_subs = $ovAdminContent->GetSubmissionCount();
	
	if ($num_subs > 0)
	{
		$limits = $ovUtilities->CalculateLimits($current_page, $num_subs, 20);
		$last_page = $limits[2];
		$submissions = $ovAdminContent->GetSubmissions($limits[0], $limits[1]);
	}
	else
	{
		// no submissions
		$submissions = false;
		$last_page = 1;
	}
?>
<h1>Submissions</h1>
<?php if ($submissions && count($submissions) > 0) { ?>
	<div class="margin_tb_10" style="line-height:24px">
		<form action="" method="GET" style="display:inline">
			<input type="hidden" name="type" value="submission" />
			<input type="text" size="5" name="id" class="textbox_18" placeholder="ID" style="vertical-align:middle" />
			&nbsp;&nbsp;
			<button type="submit" style="vertical-align:middle" class="normal-button">Submit</button>
		</form>
	</div>
	<table width="710" border="0" cellspacing="0" cellpadding="0" class="grid_table">
		<thead>
			<tr>
				<th width="40">ID</th>
				<th width="170">Title</th>
				<th width="140">Date</th>
				<th width="120">Submitted By</th>
				<th width="120">&nbsp;</th>
				<th width="100">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($submissions as $submission) { ?>
				<tr>
					<td><?php echo $submission['id']; ?></td>
					<td><?php echo $submission['title']; ?></td>
					<td><?php echo $submission['date']; ?></td>
					<td><?php echo $submission['username']; ?></td>
					<td><img src="/ov-admin/img/icons/forward.png" alt="">&nbsp;<a href="/ov-admin/content?type=submission&amp;id=<?php echo $submission['id']; ?>" title="View Details">View Details</a></td>
					<td><img src="/ov-admin/img/icons/delete.png" alt="">&nbsp;<a onclick="return ConfirmAction('Are you sure you want to delete this submission')" href="/ov-admin/php/delete_submission?id=<?php echo $submission_details['id']; ?>" title="Delete">Delete</a></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<div class="margin_tb_15">No Submissions.</div>
<?php } ?>

<?php 
	if ($last_page > 1) { 
		$ovUtilities->PrintPaginationRow("/ov-admin/content?type=submission&p=", $current_page, $last_page);
	}
?>