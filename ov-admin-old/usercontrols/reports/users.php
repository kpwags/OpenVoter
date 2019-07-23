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
<h1>User Reports</h1>
<?php if ($reports && count($reports) > 0) { ?>
	<table width="710" border="0" cellspacing="0" cellpadding="0" class="grid_table">
		<thead>
			<tr>
				<th width="610">Submission</th>
				<th width="100">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($reports as $report) { ?>
				<tr>
					<td><?php echo $report['username']; ?></td>
					<td><img src="/ov-admin/img/icons/forward.png" alt="">&nbsp;<a href="/ov-admin/reports?type=user&amp;id=<?php echo $report['id']; ?>" title="View Details">View</a></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<div class="margin_tb_15">No Reports.</div>
<?php } ?>