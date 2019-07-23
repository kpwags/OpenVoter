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
	$report_details = $ovAdminReporting->GetCommentReportDetails($report_id);

?>
<h1>Report Details</h1>

<?php if ($report_details) { ?>
	<div class="report-details">
		<div class="report-comment-submission"><a href="<?php echo $report_details['page_url']; ?>#comment-<?php echo $report_details['comment_id']; ?>" target="_blank"><?php echo $report_details['submission_title']; ?></a></div>
		<div class="report-comment-body"><?php echo $report_details['comment_body']; ?></div>
		<div class="report-comment-user"><a href="/users/<?php echo strtolower($report_details['comment_user']); ?>" target="_blank"><?php echo $report_details['comment_user']; ?></a></div>
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
			<a href="/ov-admin/php/ignore_report.php?id=<?php echo $report_id; ?>&amp;type=comment" title="Ignore" class="ok-button">Ignore</a>
			<a href="/ov-admin/php/delete_comment_from_report.php?id=<?php echo $report_id; ?>&amp;comment_id=<?php echo $report_details['comment_id']; ?>" title="Remove" class="cancel-button">Remove</a>
			<?php if (!$ovAdminContent->IsUserSuspendedByUsername($report_details['comment_user'])) { ?>
				<a href="/ov-admin/php/suspend_user.php?username=<?php echo $report_details['comment_user']; ?>" title="Suspend Commenter" class="cancel-button">Suspend Commenter</a>
			<?php } ?>
			<a onclick="return ConfirmAction('Are you sure you want to ban <?php echo $report_details['comment_user']; ?>?')" href="javascript:OpenBanUserForm('<?php echo $report_details['comment_user_id']; ?>', '<?php echo $report_details['comment_user']; ?>')" title="Ban Commenter" class="cancel-button">Ban Commenter</a>
		</div>
	</div>
<?php } else { ?>
	<div class="error_text">No Report Found.</div>
<?php } ?>