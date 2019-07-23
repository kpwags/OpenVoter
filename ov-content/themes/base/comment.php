<a name="comment-<?php echo $ovoComment->ID(); ?>"></a>
<div class="comment" id="comment-<?php echo $ovoComment->ID(); ?>">
	<div id="view_comment_<?php echo $ovoComment->ID(); ?>" 
		<?php if (($ovUserSettings->CommentThreshold() != 999 && $ovoComment->Score() <= $ovUserSettings->CommentThreshold() && $ovoComment->UserID() != $ovUserSecurity->LoggedInUserID()) 
			|| $ovoComment->UserBlocked() 
			|| !$ovoComment->Active()) { echo "style=\"display:none;\""; } ?>>
		
		<div class="comment-user">
			<img src="<?php echo $ovoComment->Avatar(); ?>" alt="<?php echo $ovoComment->Username(); ?>" />
			<a href="/users/<?php echo strtolower($ovoComment->Username()); ?>" title="<?php echo $ovoComment->Username(); ?>" id="comment-user-<?php echo $ovoComment->ID(); ?>"><?php echo $ovoComment->Username(); ?><?php if ($ovoComment->UserID() == $ovoSubmission->UserID()) { echo " (Submitter)"; } ?></a>
			<span class="date">posted <?php echo $ovoComment->Date(); ?></span>
			<span class="edited" id="comment-edited-<?php echo $ovoComment->ID(); ?>"><?php if ($ovoComment->Edited()) { echo "[EDITED]"; } ?></span>
		</div>
		<div class="body" id="comment-body-<?php echo $ovoComment->ID(); ?>"><?php echo $ovoComment->Body(); ?></div>
		
		<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
		<div class="actions">
			<ul>
				<li>
					<a class="fancybox-form-link" href="#comment-reply-form" onclick="launchReplyForm('<?php echo $ovoComment->ID(); ?>', '<?php echo $reply_to_id; ?>')">
						<img src="/<?php echo get_theme_directory(); ?>img/icons/reply.png" alt="" />Reply
					</a>
				</li>
				<li>
					<a href="#report-form" onclick="ShowReportForm('comment', '<?php echo $ovoComment->ID(); ?>')" class="fancybox-form-link">
						<img src="/<?php echo get_theme_directory(); ?>img/icons/flag_red.png" alt="" />Report
					</a>
				</li>
				
				<li>
					<a onclick="ToggleCommentFavorite('<?php echo $ovoComment->ID(); ?>')">
						<img src="/<?php echo get_theme_directory(); ?>img/icons/star.png" alt="" /><span id="favorite-text-<?php echo $ovoComment->ID(); ?>"><?php if ($ovComment->IsFavorite($ovoComment->ID())) { echo "Unfavorite"; } else { echo "Favorite"; } ?></span>
					</a>
				</li>

				<?php if ($ovoComment->UserID() == $ovUserSecurity->LoggedInUserID() && $ovoComment->Modifiable()) { ?>
					<li>
						<a onclick="DisplayEditComment('<?php echo $ovoComment->ID(); ?>')">
							<img src="/<?php echo get_theme_directory(); ?>img/icons/edit.png" alt="" />Edit
						</a>
					</li>
					<li>
						<a onclick="ConfirmDeleteComment('<?php echo $ovoComment->ID(); ?>')">
							<img src="/<?php echo get_theme_directory(); ?>img/icons/not-ok.png" alt="" />Delete
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<?php } ?>

	<?php if (!$ovoComment->Active()) { ?>
		<div class="comment-hidden" id="comment_hidden_<?php echo $ovoComment->ID(); ?>">
			This comment has been deleted 
			<?php if ($ovoComment->DeletedByUser()) { echo "by its author"; } else { echo "by an admin"; }?>
			.
		</div>
	<?php } elseif ($ovoComment->UserBlocked()) { ?>
		<div class="comment-hidden" id="comment_hidden_<?php echo $ovoComment->ID(); ?>">
			This comment is from a user you have blocked. <a href="javascript:ShowHiddenComment('<?php echo $ovoComment->ID(); ?>')" title="Show Comment">Show Comment</a>
		</div>
	<?php } elseif ($ovUserSettings->CommentThreshold() != 999 && $ovoComment->Score() <= $ovUserSettings->CommentThreshold()) { ?>
		<div class="comment-hidden" id="comment_hidden_<?php echo $ovoComment->ID(); ?>">
			This comment is below the score threshold. <a href="javascript:ShowHiddenComment('<?php echo $ovoComment->ID(); ?>')" title="Show Comment">Show Comment</a>
		</div>
	<?php } ?>
</div>
<div class="comment-separator"></div>