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
?>
<h1>Banned Users</h1>
<?php if ($ban_list && count($ban_list) > 0) { ?>
	<table width="710" border="0" cellspacing="0" cellpadding="0" class="grid_table">
		<thead>
			<tr>
				<th width="150">Username</th>
				<th width="150">Email</th>
				<th width="150">Banned By</th>
				<th width="185">Reason</th>
				<th width="75">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($ban_list as $ban) { ?>
				<tr>
					<td><?php echo $ban['username']; ?></td>
					<td><?php echo $ban['email']; ?></td>
					<td><?php echo $ban['banned_by']; ?></td>
					<td><?php echo $ban['ban_reason']; ?></td>
					<td><img src="/ov-admin/img/icons/delete.png" alt="">&nbsp;<a href="/ov-admin/php/unban.php?type=user&amp;id=<?php echo $ban['id']; ?>" title="Unban">Unban</a></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<div class="margin_tb_15">No one seems to have been banned yet...good!</div>
<?php } ?>