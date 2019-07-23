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
<title>Submit New Link | <?php echo $ovSettings->Title() . $alert_count_text; ?></title>
<script type="text/javascript" src="/js/jquery.simplemodal.1.4.1.min.js"></script>
</head>
<body>
	<?php
	include (get_header());
	?>
	<div class="submit-form">
		<?php if ($dupes == "no") { ?>
			<h1>Submit a New Link to <?php echo $ovSettings->Title(); ?></h1>
			<div id="submit-step-1">
				<div id="url-error-line" class="error_text margin_tb_10"></div>
				<div class="submission-url-textbox">
					<input type="text" name="url" id="url" size="60" value="<?php echo $url; ?>" />
				</div>
				<div class="submission-type-chooser">
					<div class="font_16 margin_b_10">What kind of submission is this?:</div>
					<ul>
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
				<form action="/php/submit_link.php" method="post" onsubmit="return ValidateSubmission()">
					<div class="form-field" id="thumbnail_chooser">
						<label>Choose an Image for the Thumbnail</label>
						<div class="margin_tb_15" id="thumbnail_images">
					
						</div>
						<div id="clear-thumbnail" style="display:none"><a href="javascript:ClearThumbnail()">Remove Thumbnail</a></div>
						<div class="align_center" id="image-loader-wait">
							<img src="/img/ajax-loader.gif" alt="" />
							<br/>Loading Images...
						</div>
					</div>
			
					<div class="form-field">
						<label for="title">Title</label><br/>
						<input type="text" id="title" name="title" value="<?php echo $sub_info['title']; ?>" size="35" class="textInput" />
						<div class="error-field" id="title_error"></div>
					</div>

					<div class="form-field">
						<label for="summary">Summary</label><br/>
						<textarea id="summary" name="summary" rows="25" cols="25" class="limit500" charsleft="submit_chars_left"></textarea>
						<?php if ($type != "self") { ?>
							<p class="align_right" id="submit_chars_left" style="margin-right:160px">500 characters remaining</p>
						<?php } ?>
						<div class="error-field" id="summary_error"></div>
					</div>
			
					<div class="form-field category-area">
						<label>Categories</label><br/>
						<?php
							$submission_categories = $ovContent->GetCategories();
							$top_count = 0;
							foreach ($submission_categories as $category)
							{
								$subcategories = $ovContent->GetCategories($category['url_name']);
						?>
								<?php if ($category['id'] != 1) { ?>
									<div class="submission-top-category" <?php if ($top_count == 1) { echo "style=\"border-top:1px solid #2d2d2d\""; } ?>>
										<input type="checkbox" name="category[]" onclick="ChooseCategory(this)" value="<?php echo $category['id']; ?>" />&nbsp;&nbsp;<?php echo $category['name']; ?>
										<?php if ($subcategories) { ?>
											<span class="submission_view_more">
												<a class="js_link" id="show_more_link_<?php echo $category['id']; ?>" onclick="ToggleSubcategories('subcategories_for_<?php echo $category['id']; ?>', 'show_more_link_<?php echo $category['id']; ?>')">(View More)</a>
											</span>
										<?php } ?>
									</div>
								<?php } ?>
								<?php if ($subcategories) { ?>
									<div class="submission_subcategory_area" <?php if ($top_count == (count($submission_categories) - 1)) { echo "style=\"border-bottom:1px solid #2d2d2d;display:none\""; } else { echo "style=\"display:none\""; } ?> id="subcategories_for_<?php echo $category['id']; ?>">
										<ul class="subcategory-chooser-list">
												<?php
													$cat_count = count($subcategories);
													$count = 0;
													foreach($subcategories as $subcategory)
													{
												?>
														<li>
															<div class="checkbox">
																<input type="checkbox" name="category[]" onclick="ChooseCategory(this)" value="<?php echo $subcategory['id']; ?>" />
															</div>
															<div class="subcategory-name">
																<?php echo $subcategory['name']; ?>
															</div>
														</li>
												<?php
													}
												?>
										</ul>
										<div class="clearfix"></div>
									</div>
								<?php } ?>
						<?php
								$top_count++;
							}
						?>
						<div class="error-field" id="category_error"></div>
					</div>
			
					<div class="form-field">
						<label for="tags">Tags</label><br/>
						<input type="text" id="tags" name="tags" value="" size="35" />
						<div class="error-field" id="tag_error"></div>
					</div>

					<input type="hidden" id="submission-url" name="submission-url" />
					<input type="hidden" id="submission-type" name="submission-type" />
					<input type="hidden" id="thumbnail_url" name="submission-thumbnail-url" value="" />
					<div class="form-field"><button type="submit" class="normal-button">Submit</button></div>
				</form>
			</div>
		<?php } else { 
			// potential duplicates of the submission were found
		?>
			<h1>Check for Duplicate Submissions</h1>
			<p>It looks like someone might have beat you to the punch with this submission.  Why not check to see if that's indeed the case? If it isn't 
				and this is a new submission, just click the submit link at the bottom of the page.</p>
			
			<ul class="submit-duplicates">
			<?php	
				if ($submissions && count($submissions) > 0) 
				{
					foreach ($submissions as $submission) {
						$ovoSubmission = new ovoSubmission($submission);
			?>
						<li>
							<a href="<?php echo $ovoSubmission->PageURL(); ?>" title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>" class="font_16">
								<?php echo htmlspecialchars($ovoSubmission->Title()); ?>
							</a>
							<div style="margin:5px 0"><?php echo $ovoSubmission->Summary(); ?></div>
							<div>Submitted by <a href="/users/<?php echo strtolower($ovoSubmission->Username()); ?>"><?php echo $ovoSubmission->Username(); ?></a></div>
						</li>
			<?php
					}
				}
			?>
			</ul>
			
			<div class="margin_tb_20">
				<a href="/php/submit_link_dupes.php" title="Submit Link">Submit Link Anyway</a>
			</div>
		<?php } ?>
	</div>
	<div id="submit-loading-box" style="display:none">
		<div><img src="/img/ajax-loader.gif" alt="" /></div>
		<div>Loading...</div>
	</div>
<?php
include (get_footer());
?>