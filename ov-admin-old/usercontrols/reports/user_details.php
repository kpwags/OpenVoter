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
	$report_details = $ovAdminReporting->GetUserReportDetails($report_id);

?>

<h1>Report Details</h1>

<?php if ($report_details) { ?>
	<div class="report-details">
		<div class="report-user-avatar">
			<img src="<?php echo $report_details['avatar']; ?>" alt="" width="70"/>
		</div>
		<div class="report-user-details">
			<div class="report-user-username"><a href="/users/<?php echo strtolower($report_details['username']); ?>" target="_blank"><?php echo $report_details['username']; ?></a></div>
			<div><strong>Email:</strong> <?php echo $report_details['email']; ?></div>
			<div><strong>Website:</strong> <a href="<?php echo strtolower($report_details['website']); ?>" target="_blank"><?php echo $report_details['website']; ?></a></div>
			<p><?php echo $report_details['details']; ?></p>
		</div>
		<div class="clearfix"></div>		
	</div>
	<div>
		<h3>Reports from Users</h3>
		<?php 
			if ($report_details['reports'] && count($report_details['reports']) > 0) { 
				foreach ($report_details['reports'] as $report) {
		?>
					<div class="report">
						<div class="report-user"><a href="/users/<?php echo strtolower($report['username']); ?>" target="_blank"><?php echo $report['username']; ?></a></div>
						<div><strong><?php echo $report['reason']; ?></strong></div>
						<?php if ($report['details'] != "") { ?>
							<p><?php echo $report['details']; ?></p>
						<?php } ?>
					</div>
		<?php 
				} 
			}
		?>
	</div>
	<div>
		<h3>Actions</h3>
		<div>
			<a href="/ov-admin/php/ignore_report.php?id=<?php echo $report_id; ?>&amp;type=user" title="Ignore" class="ok-button">Ignore</a>
			<?php if (!$ovAdminContent->IsUserSuspendedByID($report_details['user_id'])) { ?>
				<a href="/ov-admin/php/suspend_user.php?user_id=<?php echo $report_details['user_id']; ?>" title="Suspend User" class="cancel-button">Suspend User</a>
			<?php } ?>
			<a onclick="return ConfirmAction('Are you sure you want to ban <?php echo $report_details['username']; ?>?')" href="javascript:OpenBanUserForm('<?php echo $report_details['user_id']; ?>', '<?php echo $report_details['username']; ?>')" title="Ban User" class="cancel-button">Ban User</a>
		</div>
	</div>
<?php } else { ?>
	<div class="error_text">No Report Found.</div>
<?php } ?>
	
