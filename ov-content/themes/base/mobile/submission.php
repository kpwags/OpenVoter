<?php
$submission_id = $_GET['id'];
$submission_details = $ovSubmission->GetSubmissionDetails($submission_id);

if (!$submission_details) {
	header("Location: /error");
	exit();
}

$ovoSubmission = new ovoSubmission($submission_details);
$comment_count = $ovComment->GetCommentCount($submission_id);

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

include (get_mobile_head());
?>
<title><?php echo $ovoSubmission->Title() . " | " . $ovSettings->Title() . $alert_count_text; ?></title>
</head>
<body>
	<?php
	include (get_mobile_header());
	?>
	
	<div class="submission">
		<div class="submission-details" onclick="navigateTo('<?php echo $ovoSubmission->URL() ?>')">
			<div class="submission-title"><?php echo $ovoSubmission->Title(); ?></div>
			<div class="submission-summary"><?php echo $ovoSubmission->Summary(); ?></div>
		</div>
	
		<!-- VOTING -->
		<?php $vote_direction = $ovSubmission->CheckForVote($ovoSubmission->ID()); ?>
		<div class="submission-voting">
			<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
				<?php if ($vote_direction == 1) { ?>
					<a class="submission-up-voted" onclick="submissionVote('<?php echo $ovoSubmission->ID(); ?>', '0')" id="submission-up-vote"></a>
				<?php } else { ?>
					<a class="submission-up-vote" onclick="submissionVote('<?php echo $ovoSubmission->ID(); ?>', '1')" id="submission-up-vote"></a>
				<?php } ?>
			<?php } else { ?>
				<a class="submission-up-vote" href="/m/login"></a>
			<?php } ?>
			<span id="submission-score" class="submission-score"><?php echo $ovoSubmission->Score(); ?></span>
			<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
				<?php if ($vote_direction == -1) { ?>
					<a class="submission-down-voted" onclick="submissionVote('<?php echo $ovoSubmission->ID(); ?>', '0')" id="submission-down-vote"></a>
				<?php } else { ?>
					<a class="submission-down-vote" onclick="submissionVote('<?php echo $ovoSubmission->ID(); ?>', '-1')" id="submission-down-vote"></a>
				<?php } ?>
			<?php } else { ?>
				<a class="submission-down-vote" href="/m/login"></a>
			<?php } ?>
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="submission-user" onclick="navigateTo('<?php echo "/m/users/" . strtolower($ovoSubmission->Username()); ?>')">
		Submitted by 
		<img src="<?php echo $ovoSubmission->Avatar(); ?>" alt="<?php echo $ovoSubmission->Username(); ?>" width="16" style="vertical-align:middle"/>
		<a href="/m/users/<?php echo strtolower($ovoSubmission->Username()); ?>" title="<?php echo $ovoSubmission->Username(); ?>"><?php echo $ovoSubmission->Username(); ?></a>
	</div>
	
	<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
	<div class="submission-actions">
		<ul class="submission-actions-list">
			<li><a class="normal-button modal-form-link" href="#share-form">Share</a></li>
			<li><a class="normal-button" id="favorite-button" onclick="toggleSubmissionFavorite('<?php echo $ovoSubmission->ID(); ?>')"><?php if ($ovSubmission->IsFavorite($ovoSubmission->ID())) { echo "Unfavorite"; } else { echo "Favorite"; } ?></a></li>
			<li><a class="cancel-button modal-form-link" href="#report-form">Report</a></li>
		</ul>
		<div class="clearfix"></div>
	</div>
	<?php } ?>
	
	<div class="submission-tags">
		<div style="padding:10px">
			<a onclick="toggleTagsList()" class="collapse-arrow" id="tags-collapse-arrow">
				<img src="/img/arrow-collapsed.png" alt="" width="10" />
			</a>
			<a onclick="toggleTagsList()" class="collapse-link" id="tags-collapse-link">Show Tags</a>
		</div>
		<div id="submission-tags-list" style="display:none">
			<ul>
				<?php $ovoSubmission->ListTagsMobilePage(); ?>
			</ul>
		</div>
	</div>
	
	<div id="#comments"></div>
	<?php 
		$comments = $ovComment->GetComments($ovoSubmission->ID()); 
		
		if ($comments && count($comments) > 0)
		{
			foreach ($comments as $comment)	{
				$ovoComment = new ovoComment($comment);
	?>
				<div class="comment" onclick="toggleCommentReplies('<?php echo $ovoComment->ID(); ?>')">
					<?php if ($ovoComment->Active()) { ?>
						<div class="comment-content">
						<div class="comment-body"><?php echo $ovoComment->Body(); ?></div>
						<div class="comment-user" onclick="navigateTo('<?php echo "/m/users/" . strtolower($ovoComment->Username()); ?>')">
							<img src="<?php echo $ovoComment->Avatar(); ?>" alt="<?php echo $ovoComment->Username(); ?>" width="12" style="vertical-align:middle"/>
							<a href="/m/users/<?php echo strtolower($ovoComment->Username()); ?>" title="<?php echo $ovoComment->Username(); ?>"><?php echo $ovoComment->Username(); ?></a>
							<span class="comment-date"><?php echo $ovoComment->Date(); ?></span>
						</div>
						</div>
						<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
							<div class="comment-reply-button" onclick="showCommentReplyForm('<?php echo $ovoComment->ID(); ?>')"></div>
						<?php } ?>
						<div class="clearfix"></div>
					<?php } else { ?>
						<span class="comment-deleted">
							This comment has been deleted <?php if ($ovoComment->DeletedByUser()) { echo "by its author"; } else { echo "by an admin"; }?>.	
						</span>
					<?php } ?>
				</div>
	<?php				
				$comment_replies = $ovComment->GetCommentReplies($ovoComment->ID());
				if ($comment_replies && count($comment_replies) > 0) {
	?>
				<div id="comment-replies-<?php echo $ovoComment->ID(); ?>">
	<?php
					foreach ($comment_replies as $reply) {
						$ovoCommentReply = new ovoComment($reply);
	?>
						<div class="comment-reply">
							<div class="comment-reply-content">
								<?php if ($ovoCommentReply->Active()) { ?>
									<div class="comment-body"><?php echo $ovoCommentReply->Body(); ?></div>
									<div class="comment-user" onclick="navigateTo('<?php echo "/m/users/" . strtolower($ovoCommentReply->Username()); ?>')">
										<img src="<?php echo $ovoComment->Avatar(); ?>" alt="<?php echo $ovoCommentReply->Username(); ?>" width="12" style="vertical-align:middle"/>
										<a href="/m/users/<?php echo strtolower($ovoCommentReply->Username()); ?>" title="<?php echo $ovoCommentReply->Username(); ?>"><?php echo $ovoCommentReply->Username(); ?></a>
										<span class="comment-date"><?php echo $ovoComment->Date(); ?></span>
									</div>
								<?php } else { ?>
									<span class="comment-deleted">
										This comment has been deleted <?php if ($ovoCommentReply->DeletedByUser()) { echo "by its author"; } else { echo "by an admin"; }?>.
									</span>
								<?php } ?>
							</div>
						</div>
	<?php
					}
	?>
			</div>
			<div class="hidden-comment-replies" id="hidden-comment-replies-<?php echo $ovoComment->ID(); ?>" style="display:none">&nbsp;</div>
	<?php
				}
			}
		}
	?>
	
	<div id="comment-form" class="comment-form">
		<h2 style="padding:0 10px">Post Comment</h2>
		<div class="modal-form-field"><textarea class="modal-textarea" name="comment-body" id="comment-body"></textarea></div>
		<input type="hidden" name="comment-replied-to-id" id="comment-replied-to-id" value="" />
		<div class="modal-form-field"><button class="normal-button modal-submit-button" onclick="postComment('<?php echo $ovoSubmission->ID(); ?>')">Post Comment</button></div>
		<div class="modal-form-field"><button class="cancel-button modal-submit-button" onclick="hideCommentForm()">Cancel</button></div>
	</div>
	
	<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
		<div class="post-comment-button"><button class="normal-button button-full-width" onclick="showCommentForm()">Post Comment</button></div>
	<?php } else { ?>
		<div class="post-comment-button">You must be logged in to post a comment. <a href="/m/login">Log In</a> now!</div>
	<?php } ?>

<!-- MODAL FORMS -->
<div style="display:none">
	<div id="share-form" class="modal-form">
		<h2>Share Submission</h2>
		<div class="modal-form-field"><textarea class="modal-textarea" name="share-message" id="share-message"></textarea></div>
		<div class="modal-form-field"><button class="normal-button modal-submit-button" onclick="shareSubmission('<?php echo $ovoSubmission->ID(); ?>')">Share</button></div>
	</div>
</div>

<div style="display:none">
	<div id="report-form" class="modal-form">
		<h2>Report Submission</h2>
		<div class="modal-form-field">
			<select id="report-reason" name="report-reason" class="modal-select">
				<option value="Spam">Spam</option>
				<option value="Offensive">Offensive</option>
				<option value="Violation">Violation of TOU</option>
				<option value="Other">Other</option>
			</select>
		</div>
		<div class="modal-form-field"><textarea class="modal-textarea" name="report-details" id="report-details"></textarea></div>
		<div class="modal-form-field"><button class="normal-button modal-submit-button" onclick="submitReport('submission', '<?php echo $ovoSubmission->ID(); ?>')">Submit</button></div>
	</div>
</div>
	
<?php include(get_mobile_footer()); ?>