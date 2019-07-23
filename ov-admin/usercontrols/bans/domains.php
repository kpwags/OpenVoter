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
<h1>Banned Domains</h1>
<?php if ($ban_list && count($ban_list) > 0) { ?>
	<table width="710" border="0" cellspacing="0" cellpadding="0" class="grid_table">
		<thead>
			<tr>
				<th width="200">Domain Name</th>
				<th width="435">Reason</th>
				<th width="75">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($ban_list as $ban) { ?>
				<tr>
					<td><?php echo $ban['domain']; ?></td>
					<td><?php echo $ban['reason']; ?></td>
					<td><img src="/ov-admin/img/icons/delete.png" alt="">&nbsp;<a href="/ov-admin/php/unban.php?type=domain&amp;id=<?php echo $ban['id']; ?>" title="Unban">Unban</a></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<div class="margin_tb_15">No bans yet...good!</div>
<?php } ?>
<div class="add_row">
	<img><img src="/ov-admin/img/icons/add.png" alt=""><a href="javascript:OpenAddBannedDomainForm()" title="Add Banned Domain">Add Banned Domain</a>
</div>