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
	$report_details = $ovAdminReporting->GetSubmissionReportDetails($report_id);

?>
<h1>Report Details</h1>

<?php if ($report_details) { ?>
	<div class="report-details">
		<div class="report-submission-score">
			<div class="report-submission-score-box"><?php echo $report_details['submission_score']; ?></div>
		</div>
		<div class="report-submission-details">
			<div class="report-submission-title">
				<a href="<?php echo $report_details['page_url']; ?>" target="_blank"><?php echo $report_details['submission_title']; ?></a>
			</div>
			<div class="report-submission-summary">
				<?php echo $report_details['submission_summary']; ?>
			</div>
			<div class="report-submission-url">
				<strong>URL:</strong> <a href="<?php echo $report_details['submission_url']; ?>" target="_blank"><?php echo $report_details['submission_url']; ?></a>
			</div>
			<div><strong>Submitted By:</strong> <a href="/users/<?php echo strtolower($report_details['submission_user']); ?>" target="_blank"><?php echo $report_details['submission_user']; ?></a></div>
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
			<a href="/ov-admin/php/ignore_report.php?id=<?php echo $report_id; ?>&amp;type=submission" title="Ignore" class="ok-button">Ignore</a>
			<a href="/ov-admin/php/delete_submission_from_report.php?id=$report_id&amp;submission_id=<?php echo $report_details['submission_id']; ?>" title="Remove" class="cancel-button">Remove</a>
			<?php if (!$ovAdminBans->IsDomainBanned($report_details['submission_url'])) { ?>
				<a href="javascript:OpenBanDomainFormWithUrl('<?php echo $report_details['submission_url']; ?>')" title="Ban Domain" class="cancel-button">Ban Domain</a>
			<?php } ?>
			<?php if (!$ovAdminContent->IsUserSuspendedByUsername($report_details['submission_user'])) { ?>
				<a href="/ov-admin/php/suspend_user.php?username=<?php echo $report_details['submission_user']; ?>" title="Suspend User" class="cancel-button">Suspend Submitter</a>
			<?php } ?>
			<a onclick="return ConfirmAction('Are you sure you want to ban <?php echo $report_details['submission_user']; ?>?')" href="javascript:OpenBanUserForm('<?php echo $report_details['submission_user_id']; ?>', '<?php echo $report_details['submission_user']; ?>')" title="Ban Submitter" class="cancel-button">Ban Submitter</a>
		</div>
	</div>
<?php } else { ?>
	<div class="error_text">No Report Found.</div>
<?php } ?>