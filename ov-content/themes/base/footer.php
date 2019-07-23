</div>
<div class="clearfix"></div>

<!-- SHARE FORM -->
<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
<div style="display:none">
	<div id="share-form">
		<a class="fancybox-close-button" onclick="closePopup()"></a>
		<h1>Share Submission</h1>
		<div>
			<label>Share With</label>
			<select id="share-with">
				<option value="all">All Followers</option>
				<?php
					$user_lists = $ovList->GetUserLists($ovUserSecurity->LoggedInUserID());
					if ($user_lists) {
						foreach ($user_lists as $list) {
							echo "<option value=\"" . $list['id'] . "\">" . $list['name'] . "</option>";
						}
					}
				?>
			</select>
		</div>
		<div>
			<textarea id="share-message" name="share-message" class="limit255" charsleft="share-message-chars-left" placeholder="Send a personal message"></textarea>
			<div class="align_right" id="share-message-chars-left">255 characters remaining</div>
		</div>
		<div class="align_right">
			<input type="hidden" id="share-submission-id" value="" />
			<button onclick="ShareSubmission()" class="normal-button">Share</button>
		</div>
	</div>
</div>
<?php } ?>

<!-- REPORT FORM -->
<div style="display:none">
	<div id="report-form">
		<a class="fancybox-close-button" onclick="closePopup()"></a>
		<h1>Report <span id="report-object-type-header"></span></h1>
		<form id="reportForm" method="get">
		<div>
			<select id="reportReason" name="reportReason">
				<option value="">Select a Reason</option>
				<option value="Spam">Spam</option>
				<option value="Offensive">Offensive</option>
				<option value="Violation">Violates TOU</option>
				<option value="Other">Other</option>
			</select>
		</div>
		<div>
			<textarea id="report-details" name="details" class="limit255" charsleft="report-chars-left" placeholder="More details"></textarea>
			<div class="align_right" id="report-chars-left">255 characters remaining</div>
		</div>

		<input type="hidden" id="report-object-id" value="" />
		<input type="hidden" id="report-object-type" value="" />

		<div class="align_right">
			<button onclick="SubmitReport();return false;" class="normal-button">Submit Report</button>
		</div>
		</form>
	</div>
</div>
<!-- END REPORT FORM -->

<div id="footer">
	<ul>
		<li><a href="/about" title="">About</a></li>
		<li><a href="/help" title="">Help</a></li>
		<?php if ($ovSettings->Blog() != "") { ?><li><a href="<?php echo $ovSettings->Blog(); ?>" title="">Blog</a></li><?php } ?>
		<li><a href="/rss-feeds" title="RSS Feeds">RSS</a></li>
		<?php if ($ovSettings->EnableAPI()) { ?><li><a href="/developer" title="Developer Tools">Developer</a></li><?php } ?>
		<li><a href="/fun" title="Fun Stuff">Fun</a></li>
		<li><a href="/feedback" title="Leave Feedback">Feedback</a></li>
		<li><a href="/terms" title="">Terms of Use</a></li>
		<li><a href="/privacy" title="">Privacy Policy</a></li>
		<li><a href="/powered-by-openvoter" title="">Powered By OpenVoter</a></li>
	</ul>
	<div class="copyright">&copy; 2011 <a href="<?php echo $ovSettings->RootURL(); ?>" title="<?php echo $ovSettings->Title(); ?>"><?php echo $ovSettings->Title(); ?></a></div>
</div>
<?php echo $ovSettings->GoogleAnalytics(); ?>
</body>
</html>