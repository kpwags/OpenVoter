<?php
if ($type == "api") {
	$page_title = $ovSettings->Title() . " API | " . $ovSettings->Title();
} elseif ($type == "fun") {
	$page_title = "Fun Stuff | " . $ovSettings->Title();
} elseif ($type == "feedback") {
	$page_title = "Leave Feedback | " . $ovSettings->Title();
} elseif ($type == "rss") {
	$page_title = "RSS Feeds | " . $ovSettings->Title();
} else {
	header("Location: /error");
	exit();
}

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

include (get_head());
?>
<title><?php echo $page_title . $alert_count_text; ?></title>
<script type="text/javascript">
	<?php echo "var RecaptchaOptions = { theme : '" . $ovSettings->RecaptchaTheme() . "' };"; ?>

	$(document).ready(function() {
		$('#feedbackForm').validate({
			rules: {
				username: {
					required: true					
				}, 
				email: {
					required: true,
					email: true
				},
				reason: {
					required: true
				},
				message: {
					required: true
				}
			},
			messages: {
				username: {
					required: "You must enter your name"
				},
				email: {
					required: "You must enter a valid email address",
					email: "You must enter a valid email address"
				},
				reason: {
					required: "You must give a reason"
				},
				message: {
					required: "You must enter a message"
				}
			}
		});
	});
</script>
</head>
<body>
	<?php
	include ('category-bar-hidden.php');
	include (get_header());
	?>
	
	<?php
		if ($type == "api") {
	?>
			<h1><?php echo $ovSettings->Title(); ?> API</h1>
		<?php if ($ovSettings->EnableAPI()) { ?>
			<p>Yes, <?php echo $ovSettings->Title(); ?> does have an Application Programmer's Interface (API).  What that means is that 
				developers can use their mad programming skills to interface with the site to hopefully do some cool stuff.</p>

			<p>So without further adieu, here's the documentation stuff.</p>

			<a name="return_data"></a>
			<h3>The Data Returned</h3>
			<p>The data returned by the API calls all return the data in XML. Here is what the general return will look like. The comments node will only be available for an individual submission.</p>
			<pre>
&lt;OpenVoter&gt; 
	&lt;submissions&gt; 
		&lt;submission&gt; 
			&lt;ID&gt;20&lt;/ID&gt; 
			&lt;type&gt;photo&lt;/type&gt; 
			&lt;title&gt;SUBMISSION TITLE&lt;/title&gt; 
			&lt;summary&gt;This is the summary of the submission&lt;/summary&gt; 
			&lt;url&gt;http://submission.com&lt;/url&gt; 
			&lt;score&gt;15&lt;/score&gt; 
			&lt;thumbnail&gt;<?php echo $ovSettings->RootURL(); ?>/ov-upload/thumbnails/submission-20.jpg&lt;/thumbnail&gt; 
			&lt;date&gt;2011-01-28 12:00:45&lt;/date&gt; 
			&lt;popular&gt;false&lt;/popular&gt; 
			&lt;popularDate&gt;&lt;/popularDate&gt; 
			&lt;location&gt;&lt;/location&gt; 
			&lt;pageUrl&gt;<?php echo $ovSettings->RootURL(); ?>/20/photo/submission-title&lt;/pageUrl&gt; 
			&lt;user&gt; 
				&lt;username&gt;SubmissionUser&lt;/username&gt; 
				&lt;avatar&gt;<?php echo $ovSettings->RootURL(); ?>/ov-upload/avatars/submissionuser.jpg&lt;/avatar&gt; 
			&lt;/user&gt;
			&lt;comments&gt;
				&lt;comment&gt;
					&lt;id&gt;55&lt;/id&gt;
					&lt;user&gt;
						&lt;username&gt;SubmissionUser&lt;/username&gt;
						&lt;avatar&gt;<?php echo $ovSettings->RootURL(); ?>/ov-upload/avatars/submissionuser.jpg&lt;/avatar&gt;
					&lt;/user&gt;
					&lt;date&gt;2011-01-28 12:00:45&lt;/date&gt;
					&lt;score&gt;5&lt;/score&gt;
					&lt;body&gt;The comment text&lt;/body&gt;
				&lt;comment&gt;
			&lt;/comments&gt;
		&lt;/submission&gt; 
	&lt;/submissions&gt;
&lt;/OpenVoter&gt;</pre>
			<p>For as many submissions you bring back, they'll be under the submissions node. To be sure you understand what each node contains, 
				we'll define them here.</p>
			<p>
				<strong>id</strong>: The ID of the submission<br/>
				<strong>type</strong>: The type of submission (Can be STORY, PHOTO, VIDEO, PODCAST, or SELF)<br/>
				<strong>title</strong>: The title of the submission<br/>
				<strong>summary</strong>: The summary of the submission<br/>
				<strong>url</strong>: The URL of the submission<br/>
				<strong>score</strong>: The Score of the submission<br/>
				<strong>thumbnail</strong>: If it's a photo submission, this will be the URL to the image thumbnail<br/>
				<strong>date</strong>: The date the submission was submitted<br/>
				<strong>popular</strong>: Is the submission popular? (Can be TRUE or FALSE)<br/>
				<strong>popularDate</strong>: The date the submission became popular<br/>
				<strong>location</strong>: The location the submission is about (like New York City or Philadelphia)<br/>
				<strong>commentCount</strong>: The number of comments on the submission
				<strong>pageUrl</strong>: The URL of the submission on <?php echo $ovSettings->Title(); ?><br/>
				<strong>user/username</strong>: The User's username who posted the submission<br/>
				<strong>user/avatar</strong>: The User's avatar who posted the submission<br/>
			</p>

			<hr />

			<a name="all_subs"></a>
			<h3>Getting All Submissions</h3>
			<p>The first API call we'll talk about is getting all submissions from the site.</p>
			<p>The call is pretty simple</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/all.php</pre>
			<p>That's it! Not hard, is it.  You can also pass in the following arguments.</p>
			<p>
				<strong>popular</strong>: Do you want to bring back only popular submissions? Upcoming submissions? <strong>Possible Values</strong>: all, yes, no (defaults to all if nothing specified)<br/>
				<strong>type</strong>: What type of submission do you want to bring back? <strong>Possible Values</strong>: story, photo, video, podcast, self (brings back all types if not specified)<br/>
				<strong>offset</strong>: Offset from beginning to bring back. Should be an integer. (Defaults to 0 (zero) if nothing specified)<br/>
				<strong>limit</strong>: How many records should be returned. (Defaults to 10 if nothing specified. Can not be greater than 20)
			</p>
			<p>A full call example</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/all.php?popular=yes&amp;type=photo&amp;offset=10&amp;limit=15</pre>

			<hr />

			<a name="by_category"></a>
			<h3>Getting Submissions In A Category</h3>
			<p>The second API call we'll talk about is getting all submissions in a specific category from the site.</p>
			<p>The call is pretty simple</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/category.php?name=category_name</pre>
			<p>That's it! Not hard, is it.  You can also pass in the following arguments.</p>
			<p>
				<strong>popular</strong>: Do you want to bring back only popular submissions? Upcoming submissions? <strong>Possible Values</strong>: all, yes, no (defaults to all if nothing specified)<br/>
				<strong>type</strong>: What type of submission do you want to bring back? <strong>Possible Values</strong>: story, photo, video, podcast, self (brings back all types if not specified)<br/>
				<strong>offset</strong>: Offset from beginning to bring back. Should be an integer. (Defaults to 0 (zero) if nothing specified)<br/>
				<strong>limit</strong>: How many records should be returned. (Defaults to 10 if nothing specified. Can not be greater than 20)
			</p>
			<p>A full call example</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/category.php?name=category_name&amp;popular=yes&amp;type=photo&amp;offset=10&amp;limit=15</pre>

			<hr />

			<a name="by_tag"></a>
			<h3>Getting Submissions By Tag</h3>
			<p>The next API call we'll talk about is getting all submissions tagged with a specific tag from the site.</p>
			<p>The call is pretty simple</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/tag.php?name=tag_name</pre>
			<p>That's it! Not hard, is it.  You can also pass in the following arguments.</p>
			<p>
				<strong>popular</strong>: Do you want to bring back only popular submissions? Upcoming submissions? <strong>Possible Values</strong>: all, yes, no (defaults to all if nothing specified)<br/>
				<strong>type</strong>: What type of submission do you want to bring back? <strong>Possible Values</strong>: story, photo, video, podcast, self (brings back all types if not specified)<br/>
				<strong>offset</strong>: Offset from beginning to bring back. Should be an integer. (Defaults to 0 (zero) if nothing specified)<br/>
				<strong>limit</strong>: How many records should be returned. (Defaults to 10 if nothing specified. Can not be greater than 20)
			</p>
			<p>A full call example</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/tag.php?name=tag_name&amp;popular=yes&amp;type=photo&amp;offset=10&amp;limit=15</pre>

			<hr />

			<a name="by_domain"></a>
			<h3>Getting Submissions By Domain</h3>
			<p>The next API call we'll talk about is getting all submissions from a specific domain from the site.</p>
			<p>The call is pretty simple</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/domain.php?domain=domain.com</pre>
			<p>That's it! Not hard, is it.  You can also pass in the following arguments.</p>
			<p>
				<strong>popular</strong>: Do you want to bring back only popular submissions? Upcoming submissions? <strong>Possible Values</strong>: all, yes, no (defaults to all if nothing specified)<br/>
				<strong>type</strong>: What type of submission do you want to bring back? <strong>Possible Values</strong>: story, photo, video, podcast, self (brings back all types if not specified)<br/>
				<strong>offset</strong>: Offset from beginning to bring back. Should be an integer. (Defaults to 0 (zero) if nothing specified)<br/>
				<strong>limit</strong>: How many records should be returned. (Defaults to 10 if nothing specified. Can not be greater than 20)
			</p>
			<p>A full call example</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/domain.php?domain=domain.com&amp;popular=yes&amp;type=photo&amp;offset=10&amp;limit=15</pre>

			<hr />

			<a name="by_user"></a>
			<h3>Getting Submissions From A User</h3>
			<p>The next API call we'll talk about is getting all submissions from a specific user from the site.</p>
			<p>The call is pretty simple</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/user.php?username=username</pre>
			<p>That's it! Not hard, is it.  You can also pass in the following arguments.</p>
			<p>
				<strong>popular</strong>: Do you want to bring back only popular submissions? Upcoming submissions? <strong>Possible Values</strong>: all, yes, no (defaults to all if nothing specified)<br/>
				<strong>type</strong>: What type of submission do you want to bring back? <strong>Possible Values</strong>: story, photo, video, podcast, self (brings back all types if not specified)<br/>
				<strong>offset</strong>: Offset from beginning to bring back. Should be an integer. (Defaults to 0 (zero) if nothing specified)<br/>
				<strong>limit</strong>: How many records should be returned. (Defaults to 10 if nothing specified. Can not be greater than 20)
			</p>
			<p>A full call example</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/user.php?username=username&amp;popular=yes&amp;type=photo&amp;offset=10&amp;limit=15</pre>

			<hr />

			<a name="by_url"></a>
			<h3>Getting Submission Data from a URL that is Submitted to the Site</h3>
			<p>The next API call we'll talk about is slightly different as it only brings back one record.  If a website is submitted to <?php echo $ovSettings->Title(); ?>, 
				Then you can make an API call with the URL to bring back its data from <?php echo $ovSettings->Title(); ?>. The URL we are talking about is the original 
				content's URL, not the <?php echo $ovSettings->Title(); ?> URL. The comments node is available for this call.</p>
			<p>The call is pretty simple, and you can pass in one additional argument</p>
			<p>
				<strong>comments</strong>: Do you want to bring back the comments on the submission? If so pass in &quot;yes&quot;. <strong>Possible Values</strong> yes, no (defaults to no)
			</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/submission.php?url=URL&amp;comments=yes</pre>
			<p>One thing to note with this, is that the URL must be escaped or it might bring back nothing.</p>

			<hr />
			
			<a name="by_submission_id"></a>
			<h3>Getting Submission Data from the Submission ID</h3>
			<p>The next API call we'll talk about is slightly different as it only brings back one record.  If you know the Primary Key or the ID of the submission, you can pass in the ID and bring back the submission's 
				data.  The comments node is available for this call.</p>
			<p>The call is pretty simple, and you can pass in one additional argument</p>
			<p>
				<strong>comments</strong>: Do you want to bring back the comments on the submission? If so pass in &quot;yes&quot;. <strong>Possible Values</strong> yes, no (defaults to no)
			</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/submission.php?id=ID&amp;comments=yes</pre>
			
			<hr />
			
			<a name="get_user_details"></a>
			<h3>Getting User Details</h3>
			<p>The API now allows you to bring back details about a given user.  Just pass in the username and you'll get the following result:</p>
			
			<pre>
&lt;OpenVoter&gt; 
	&lt;user&gt; 
		&lt;id&gt;44&lt;/id&gt;
		&lt;username&gt;USERNAME&lt;/username&gt;
		&lt;details&gt;USER DETAILS&lt;/details&gt;
		&lt;location&gt;Planet Earth&lt;/location&gt;
		&lt;website&gt;http://google.com&lt;/website&gt;
		&lt;avatar&gt;<?php echo $ovSettings->RootURL(); ?>/ov-upload/avatars/user.jpg&lt;/avatar&gt;
		&lt;points&gt;155&lt;/points&gt;
		&lt;numSubmissions&gt;20&lt;/numSubmissions&gt;
		&lt;numComments&gt;18&lt;/numComments&gt;
		&lt;numVotes&gt;19&lt;/numVotes&gt;
		&lt;numFavorites&gt;3&lt;/numFavorites&gt;
		&lt;numFollowers&gt;12&lt;/numFollowers&gt;
		&lt;numFollowing&gt;13&lt;/numFollowing&gt;
		&lt;joinDate&gt;2011-01-28 12:00:45&lt;/joinDate&gt;
	&lt;/user&gt;
&lt;/OpenVoter&gt;</pre>
			
			<p>And the call is:</p>
			<pre><?php echo $ovSettings->RootURL(); ?>/api/userdetails.php?username=USERNAME</pre>
			
			<hr />
			
			<p>If you have any further questions, just let us know through the feedback.</p>
		<?php } else { ?>
			<p>Sorry but the API is disabled.</p>
		<?php } ?>
		<?php } elseif ($type == "fun") { ?>
			<h1>Fun Stuff</h1>
			<div class="margin_tb_10"><h3>Bookmarklet</h3></div>
			<div class="margin_tb_10">Click and Drag this onto your bookmarks bar to allow for easier submissions to <?php echo $ovSettings->Title(); ?></div>
			<div class="margin_tb_10"><a style="border:1px solid #ccc;padding:5px" href="javascript:(window.open('<?php echo $ovSettings->RootURL(); ?>/submit?url=' + escape(window.location)))">Add to <?php echo $ovSettings->Title(); ?></a></div>
		<?php } elseif ($type == "feedback") { ?>
			<div class="feedback-form">
				<h1>Leave Feedback</h1>
				<p>Have something to tell us? We're always open to hearing your ideas and if it happens to be a problem, we'll 
					look into it and get it fixed.</p>
				<p>All fields are required.</p>

				<form action="/php/leave_feedback.php" method="post" id="feedbackForm">
				<?php if (isset($_GET['error'])) { ?>
					<div class="margin_tb_20 error_text">
						<?php 
						switch ($_GET['error']) { 
							case 1:
								echo "You must enter your name.";
								break;
							case 2:
								echo "You must enter your Email Address.";
								break;
							case 3:
								echo "You must enter a message.";
								break;
							case 4:
								echo "Invalid CAPTCHA or Human Text";
								break;
							default:
								echo "Error sending feedback.";
								break;
						}
						?>
					</div>
				<?php } elseif (isset($_GET['success'])) { ?>
					<div class="margin_tb_20 success_text">Message sent</div>
				<?php } ?>

					<div class="form-field">
						<input type="text" name="username" id="username" placeholder="Your Name" />
					</div>
					
					<div class="form-field">
						<input type="email" name="email" id="email" placeholder="Your Email Address" />
					</div>

					<div class="form-field">
						<select id="reason" name="reason">
							<option value="">Reason for Feedback</option>
							<option value="message">Message</option>
							<option value="bug">Bug Report</option>
							<option value="other">Other</option>
						</select>
					</div>

					<div class="form-field">
						<textarea id="message" name="message" rows="25" cols="25" placeholder="Your Message"></textarea>
					</div>
					
					<?php if ($ovSettings->EnableRecaptcha()) { ?>
						<div class="form-field">
							<label>Are You Human?</label><br/>
							<?php
								require_once('recaptcha/recaptchalib.php');
								$publickey = $ovSettings->RecaptchaPublicKey();
								echo recaptcha_get_html($publickey);
							?>
						</div>
					<?php } ?>

					<div class="form-field">
						<button type="submit" class="normal-button">Send</button>
					</div>
				</form>
				<div class="form-footer">
					All information entered is kept confidential and your email address will not be publicly displayed
				</div>
			</div>
		<?php } elseif ($type == "rss") { ?>
			<h1>RSS Feeds</h1>
			<div class="rss-feeds">
				<div class="feed-column-left">
					<div class="feed-group">
						<h3>All</h3>
						<div class="feed">
							<a href="/feeds?type=all" title="All Submissions" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all" title="All Submissions">All Submissions</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=story" title="All Stories" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=story" title="All Stories">All Stories</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=photo" title="All Photos" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=photo" title="All Photos">All Photos</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=video" title="All Videos" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=video" title="All Videos">All Videos</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=podcast" title="All Podcasts" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=podcast" title="All Podcasts">All Podcasts</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=self" title="All Self Posts" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=self" title="All Self Posts">All Self Posts</a>
						</div>
					</div>

					<div class="feed-group">
						<h3>All Popular</h3>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=all&amp;popular=yes" title="Popular Submissions" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=all&amp;popular=yes" title="Popular Submissions">Popular Submissions</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=story&amp;popular=yes" title="Popular Stories" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=story&amp;popular=yes" title="Popular Stories">Popular Stories</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=photo&amp;popular=yes" title="Popular Photos" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=photo&amp;popular=yes" title="Popular Photos">Popular Photos</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=video&amp;popular=yes" title="Popular Videos" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=video&amp;popular=yes" title="Popular Videos">Popular Videos</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=podcast&amp;popular=yes" title="Popular Podcasts" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=podcast&amp;popular=yes" title="Popular Podcasts">Popular Podcasts</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=self&amp;popular=yes" title="Popular Self Posts" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=self&amp;popular=yes" title="Popular Self Posts">Popular Self Posts</a>
						</div>
					</div>

					<div class="feed-group">
						<h3>All Upcoming</h3>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=all&amp;popular=no" title="Upcoming Submissions" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=all&amp;popular=no" title="Upcoming Submissions">Upcoming Submissions</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=story&amp;popular=no" title="Upcoming Stories" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=story&amp;popular=no" title="Upcoming Stories">Upcoming Stories</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=photo&amp;popular=no" title="Upcoming Photos" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=photo&amp;popular=no" title="Upcoming Photos">Upcoming Photos</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=video&amp;popular=no" title="Upcoming Videos" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=video&amp;popular=no" title="Upcoming Videos">Upcoming Videos</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=podcast&amp;popular=no" title="Upcoming Podcasts" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=podcast&amp;popular=no" title="Upcoming Podcasts">Upcoming Podcasts</a>
						</div>
						<div class="feed">
							<a href="/feeds?type=all&amp;subtype=self&amp;popular=no" title="Upcoming Self Posts" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
							<a href="/feeds?type=all&amp;subtype=self&amp;popular=no" title="Upcoming Self Posts">Upcoming Self Posts</a>
						</div>
					</div>
				</div>
				<div class="feed-column-right">
					<div class="feed-group">
						<h3>Category Feeds</h3>
						<?php
							$categories = $ovContent->GetCategories();

							if ($categories) {
								foreach ($categories as $category) {
						?>
									<div class="feed">
										<a href="/feeds?type=category&amp;subtype=all&amp;id=<?php echo $category['url_name']; ?>&amp;popular=all" title="<?php echo $category['name']; ?>" class="rss-image"><img src="/img/feeds.png" alt="RSS" height="32" width="32" /></a>
										<a href="/feeds?type=category&amp;subtype=all&amp;id=<?php echo $category['url_name']; ?>&amp;popular=all" title="<?php echo $category['name']; ?>"><?php echo $category['name']; ?></a>
									</div>
						<?php
								}
							} else {
						?>
									<div class="feed">No Category Feeds</div>
						<?php
							}
						?>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
		<?php } ?>

<?php
include (get_footer());
?>