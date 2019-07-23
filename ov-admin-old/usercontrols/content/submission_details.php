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
	$submission_details = $ovAdminContent->GetSubmissionDetails($content_id);

?>
<h1>Submission Details</h1>
<?php if ($submission_details) { ?>
	<div class="report-details">
		<div class="report-submission-score">
			<div class="report-submission-score-box"><?php echo $submission_details['score']; ?></div>
		</div>
		<div class="report-submission-details">
			<div class="report-submission-title">
				<a href="<?php echo $submission_details['page_url']; ?>" target="_blank"><?php echo $submission_details['title']; ?></a>
			</div>
			<div class="report-submission-summary">
				<?php echo $submission_details['summary']; ?>
			</div>
			<div class="report-submission-url">
				<strong>URL:</strong> <a href="<?php echo $submission_details['url']; ?>" target="_blank"><?php echo $submission_details['url']; ?></a>
			</div>
			<div><strong>Submitted By:</strong> <a href="/users/<?php echo strtolower($submission_details['username']); ?>" target="_blank"><?php echo $submission_details['username']; ?></a></div>
		</div>
		<div class="clearfix"></div>
	</div>

	<h3>Actions</h3>
	<div>
 		<a href="javascript:OpenEditSubmissionForm('<?php echo $submission_details['id']; ?>', '<?php echo $submission_details['title']; ?>', '<?php echo $submission_details['summary']; ?>', '<?php echo $submission_details['url']; ?>')" title="Edit" class="normal-button">Edit</a>
		<a onclick="return ConfirmAction('Are you sure you want to delete this submission')" href="/ov-admin/php/delete_submission?id=<?php echo $submission_details['id']; ?>" title="Remove" class="cancel-button">Remove</a>
		<?php if (!$ovAdminBans->IsDomainBanned($submission_details['url']) && strtolower($submission_details['type']) != "self") { ?>
			<a href="javascript:OpenBanDomainFormWithUrl('<?php echo $submission_details['url']; ?>')" title="Ban Domain" class="cancel-button">Ban Domain</a>
		<?php } ?>
	</div>
<?php } else { ?>
	<div class="error_text">No Submission Found.</div>
<?php } ?>
	
