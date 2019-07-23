<?php
session_start();
if (!$ovUserSecurity->IsUserLoggedIn()) {
	header("Location: /login");
	exit();
}

if (isset($_GET['url'])) {
	$url = $_GET['url'];
} else {
	$url = "";
}

if (isset($_GET['dupes'])) {
	$dupes = $_GET['dupes'];
} else {
	$dupes = "no";
}

if ($dupes == "yes") {
	$keyword_string = $_SESSION['keyword_string'];
	$submissions = $ovSubmission->Search($keyword_string, "all", "all", "date", 1, "3 DAY");
	$submissions = $submissions['submissions'];
	if (!$submissions) {
		header("Location: /php/submit_link_dupes.php");
		exit();
	}
}

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

include (get_head());
?>
<title>Share New Link | <?php echo $ovSettings->Title(); ?></title>
<script type="text/javascript" src="/js/jquery.simplemodal.1.4.1.min.js"></script>
</head>
<body>
<?php
include ('category-bar-hidden.php');
include (get_header());
?>
<div class="submit-form">

	<input type="hidden" id="submission-url" name="submission-url" />
	<input type="hidden" id="submission-type" name="submission-type" />
	<input type="hidden" id="thumbnail_url" name="submission-thumbnail-url" value="" />
	<input type="hidden" id="submit-to" name="submit-to" value="" />

	<h1>Submit a New Link to <?php echo $ovSettings->Title(); ?></h1>

	<div id="submit-step-1">
		
		<div id="url-error-line" class="error_text margin_tb_10"></div>
		
		<div>
			<input type="text" name="url" id="url" size="60" value="<?php echo $url; ?>" placeholder="URL of Submission" />
		</div>
		
		<div>
			<h6>What kind of submission is this?:</h6>
			<ul class="submission-type-chooser">
				<li style="margin-left:0"><img src="/img/submission_type/story_selected.png" alt="story" id="story_type" onclick="SwitchType('story')" /></li>
				<li><img src="/img/submission_type/photo.png" alt="photo" id="photo_type" onclick="SwitchType('photo')" /></li>
				<li><img src="/img/submission_type/video.png" alt="video" id="video_type" onclick="SwitchType('video')" /></li>
				<li><img src="/img/submission_type/podcast.png" alt="podcast" id="podcast_type" onclick="SwitchType('podcast')" /></li>
				<li><img src="/img/submission_type/self.png" alt="self" onclick="SwitchType('self')" /></li>
			</ul>
		</div>
		<div>
			<input type="hidden" name="type" id="type" value="story" />
			<button class="normal-button" onclick="ValidateSubmissionURL()">Continue</button>
		</div>
	</div>

	<div id="submit-step-2" style="display:none">
		<div id="thumbnail_chooser">
			<label>Choose an Image for the Thumbnail</label>
			<div id="thumbnail_images">
		
			</div>
			<div id="clear-thumbnail" style="display:none"><a href="javascript:ClearThumbnail()">Remove Thumbnail</a></div>
			<div class="align_center" id="image-loader-wait">
				<img src="/img/ajax-loader.gif" alt="" />
				<br/>Loading Images...
			</div>
		</div>

		<div>
			<input type="text" id="title" name="title" value="<?php echo $sub_info['title']; ?>" placeholder="Title" />
			<div class="error-field" id="title_error"></div>
		</div>

		<div>
			<textarea id="summary" name="summary" rows="25" cols="25" class="limit500" charsleft="submit_chars_left" placeholder="Summary"></textarea>
			<?php if ($type != "self") { ?>
				<p class="align_right" id="submit_chars_left">500 characters remaining</p>
			<?php } ?>
			<div class="error-field" id="summary_error"></div>
		</div>

		<div>
			<input type="text" id="tags" name="tags" value="" placeholder="Submission Tags"/>
			<div class="error-field" id="tag_error"></div>
		</div>

		<div>
			<button onclick="submitTo('site')" class="normal-button">Submit To Site</button>
			<button onclick="submitTo('group')" class="normal-button">Submit To Group</button>
		</div>
	</div>

	<div id="submit-step-3-site" style="display:none">
		<div>
			<h6>Choose the Categories for your Submission.</h6>
			<?php
				$submission_categories = $ovContent->GetCategories();
				foreach ($submission_categories as $category)
				{
					$subcategories = $ovContent->GetCategories($category['url_name']);
			?>
					<div class="top-category">
						<input type="checkbox" name="category[]" onclick="ChooseCategory(this)" value="<?php echo $category['id']; ?>" />&nbsp;&nbsp;<?php echo $category['name']; ?>
					</div>

					<?php if ($subcategories) { ?>
						<ul class="subcategory-chooser">
							<?php foreach($subcategories as $subcategory) { ?>
								<li><input type="checkbox" name="category[]" onclick="ChooseCategory(this)" value="<?php echo $subcategory['id']; ?>" />&nbsp;&nbsp;<?php echo $subcategory['name']; ?></li>
							<?php } ?>
						</ul>
					<?php } ?>

			<?php
				}
			?>
			<div class="error-field" id="category_error"></div>
		</div>
		<div>
			<button onclick="submitLinkToSite(false)" class="normal-button">Submit</button>
		</div>
	</div>
	
	<div id="submit-step-3-group" style="display:none">

	</div>

	<div id="submit-duplicates" style="display:none">
		<h1>Check for Duplicate Submissions</h1>
		<p>It looks like someone might have beat you to the punch with this submission.  Why not check to see if that's indeed the case? If it isn't 
			and this is a new submission, just click the submit link at the bottom of the page.</p>

		<div id="submit-duplicates-area" class="margin_tb_20"></div>

		<div class="margin_tb_20">
			<a href="/php/submit_link_dupes.php" title="Submit Link">Submit Link Anyway</a>
		</div>
	</div>

	<div class="terms-reminder">
		Please make sure your submission follows the <a href="/terms">Terms of Use</a>. <?php echo $ovSettings->Title(); ?> reserves the right to remove any submission that violates the rules.
	</div>
</div>
<div id="submit-loading-box" style="display:none">
	<div><img src="/img/ajax-loader.gif" alt="" /></div>
	<div>Loading...</div>
</div>
<?php
include (get_footer());
?>