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

	$num_users = $ovAdminContent->GetSuspendedUserCount();
	
	if ($num_users > 0)
	{
		$limits = $ovUtilities->CalculateLimits($current_page, $num_users, 30);
		$last_page = $limits[2];
		$users = $ovAdminContent->GetSuspendedUsers($limits[0], $limits[1]);
	}
	else
	{
		// no submissions
		$users = false;
		$last_page = 1;
	}
?>
<h1>Suspended Users</h1>
<?php if ($users && count($users) > 0) { ?>
	<div class="margin_tb_10" style="line-height:24px">
		<form action="" method="GET" style="display:inline">
			<input type="hidden" name="type" value="user" />
			<input type="text" size="5" name="id" class="textbox_18" placeholder="ID" style="vertical-align:middle" />
			&nbsp;&nbsp;
			<button type="submit" style="vertical-align:middle" class="normal-button">Submit</button>
		</form>
	</div>
	<table width="710" border="0" cellspacing="0" cellpadding="0" class="grid_table">
		<thead>
			<tr>
				<th width="40">ID</th>
				<th width="450">Username</th>
				<th width="120">&nbsp;</th>
				<th width="100">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user) { ?>
				<tr>
					<td><?php echo $user['id']; ?></td>
					<td>
						<?php echo $user['username']; ?>
						<?php if ($user['suspended']) { ?>
							&nbsp;&nbsp;(Suspended Since <?php echo $user['date_suspended']; ?>)
						<?php } ?>
					</td>
					<td><img src="/ov-admin/img/icons/forward.png" alt="">&nbsp;<a href="/ov-admin/content?type=suspended_user&amp;id=<?php echo $user['id']; ?>" title="View Details">View Details</a></td>
					<td><img src="/ov-admin/img/icons/delete.png" alt="">&nbsp;<a onclick="return ConfirmAction('Are you sure you want to delete <?php echo $user['username']; ?>?')" href="/ov-admin/php/delete_user?user_id=<?php echo $user['id']; ?>" title="Delete">Delete</a></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<div class="margin_tb_15">No Suspended Users.</div>
<?php } ?>

<?php 
	if ($last_page > 1) { 
		$ovUtilities->PrintPaginationRow("/ov-admin/content?type=suspended_user&p=", $current_page, $last_page);
	}
?>