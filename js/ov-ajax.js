/*
SUBMISSION PAGE JAVASCRIPT
*/

function AddLocation() {
	var submissionId = $('#submission-id').val(),
		submissionLocation = $('#submission-location').val();

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'add_location',
			id: submissionId,
			location: submissionLocation
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				$('#add-location-button').fadeOut('fast');
				$("#location_text").html(submissionLocation);
				var imageSrc = "http://maps.google.com/maps/api/staticmap?center=" + escape(submissionLocation) + "&zoom=13&size=228x228&sensor=false&markers=color:red|" + escape(submissionLocation);
				$("#location_image").attr("src", imageSrc);
				$.fancybox.close();
			} else {
				noty({
					text: response.errorMessage,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function ToggleSubscription(submissionId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'toggle_subscription',
			id: submissionId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				if (response.message == "subscribed") {
					$('#submission-subscribe-link').html("Unsubscribe");
				} else {
					$('#submission-subscribe-link').html("Subscribe");
				}
			} else {
				noty({
					text: response.errorMessage,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function ToggleSubmissionFavorite(submissionId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'toggle_submission_favorite',
			id: submissionId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				if (response.message == "is_favorite") {
					$('#submission-favorite-link').html("Unfavorite");
				} else {
					$('#submission-favorite-link').html("Favorite");
				}
			} else {
				noty({
					text: response.errorMessage,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function SubmitReport() {
	var objectType, objectId, reason, details, reportFormId;

	objectType = $('#report-object-type').val();
	objectId = $('#report-object-id').val();
	reason = $('#reportReason').val();
	details = $('#report-details').val();

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'submit_report',
			reason: reason,
			details: details,
			objecttype: objectType,
			objectid: objectId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			$('#report-object-type').val('');
			$('#report-object-id').val('');
			$('#reportReason').val('');
			$('#report-details').val('');
		
			$.fancybox.close();
			
			if (response.status == "OK") {
				// report went through
				noty({
					text: response.message,
					type: 'success',
					layout: 'top'
				});
			} else if (response.status == "REPEAT") {
				// repeat report
				noty({
					text: response.message,
					type: 'alert',
					layout: 'top'
				});
			} else {
				// report failure
				noty({
					text: response.message,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function ShareSubmission() {
	var submissionId = $('#share-submission-id').val(),
		message = $('#share-message').val(),
		shareWith = $('#share-with').val();

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'share_submission',
			id: submissionId,
			message: message,
			share_with: shareWith
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				$.fancybox.close();
				noty({
					text: response.message,
					type: 'success',
					layout: 'top'
				});
			} else {
				noty({
					text: response.message,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function EditSubmission() {
	var submissionId = $('#submission-id').val(),
		submissiontTitle = $('#edit-submission-title').val(),
		submissionSummary = $('#edit-submission-summary').val();

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'edit_submission',
			id: submissionId,
			title: submissiontTitle,
			summary: submissionSummary
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				$.fancybox.close();
				$('#submission-title').text(response.title);
				$('#submission-summary').text(response.summary);
				noty({
					text: "Changes Saved",
					type: 'success',
					layout: 'top'
				});
			} else {
				noty({
					text: response.message,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function DeleteSubmission() {
	var submissionId = $('#submission-id').val();

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'delete_submission',
			id: submissionId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				window.location = "/";
			} else {
				noty({
					text: response.message,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function AddThumbnail() {
	var submissionId = $('#submission-id').val(),
		thumbnailUrl = $('#submission-thumb-input').val();

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'add_thumbnail',
			id: submissionId,
			url: thumbnailUrl
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				$.fancybox.close();
				$('#add-submission-thumb').fadeOut('fast');
				$('#submission-thumb').attr('src', response.src);
				noty({
					text: "Thumbnail applied",
					type: 'success',
					layout: 'top'
				});
			} else {
				noty({
					text: response.message,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

/*
SIGN UP PAGE JAVASCRIPT
*/

function ValidateUsername()
{
	var username = jQuery.trim($("#username").val());

	var usernameRegex = new RegExp("^[a-zA-Z0-9_]{5,20}$");
	if (!usernameRegex.test(username)) 
	{
		$("#username_ok").css("display", "none");
		$("#username_not_ok").css("display", "inline");
		$("#username_error").html("Invalid Username, Should Be Alpha-Numeric and be 5-20 characters.");
		$("#username_error").css("display", "block");
		$("#username").attr("style", "border-color:#ff4444");
	} else {
		var ovAjax = GetXHTTPRequest();
		ovAjax.onreadystatechange = function()
		{
			if(ovAjax.readyState == 4)
			{
				var xmlObject = ParseXML(ovAjax.responseText);

				var $xml = $( xmlObject );
				var $responseCode = $xml.find("code");
				var $message = $xml.find("message");

				if ($responseCode.text() == "OK") {
					// username is OK
					$("#username_not_ok").css("display", "none");
					$("#username_ok").css("display", "inline");
					$("#username_error").css("display", "none");
					$("#username").attr("style", "border-color:#3a3ad2");
				} else {
					// username is not OK
					$("#username_ok").css("display", "none");
					$("#username_not_ok").css("display", "inline");
					$("#username_error").html("Username is Taken");
					$("#username_error").css("display", "block");
					$("#username").attr("style", "border-color:#ff4444");
				}
			}
		}

		var requestString = '/php/ov-ajax.php?action=validate_username_email&identifier=' + username;
		ovAjax.open("GET", requestString, true) ;
		ovAjax.send(null) ;
	}
}

function ValidateEmail()
{
	var email = jQuery.trim($("#email").val());

	var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
	if( !emailExp.test(email)) {
		$("#email_ok").css("display", "none");
		$("#email_not_ok").css("display", "inline");
		$("#email_error").html("Invalid Email Address.");
		$("#email_error").css("display", "block");
		$("#email").attr("style", "border-color:#ff4444");
	} else {
		var ovAjax = GetXHTTPRequest();
		ovAjax.onreadystatechange = function()
		{
			if(ovAjax.readyState == 4)
			{
				var xmlObject = ParseXML(ovAjax.responseText);

				var $xml = $( xmlObject );
				var $responseCode = $xml.find("code");
				var $message = $xml.find("message");

				if ($responseCode.text() == "OK") {
					// email is OK
					$("#email_not_ok").css("display", "none");
					$("#email_ok").css("display", "inline");
					$("#email_error").css("display", "none");
					$("#email").attr("style", "border-color:#3a3ad2");
				} else {
					// email is not OK
					$("#email_ok").css("display", "none");
					$("#email_not_ok").css("display", "inline");
					$("#email_error").html("Email is Taken. Did you forget your password?");
					$("#email_error").css("display", "block");
					$("#email").attr("style", "border-color:#ff4444");
				}
			}
		}

		var requestString = '/php/ov-ajax.php?action=validate_username_email&identifier=' + email;
		ovAjax.open("GET", requestString, true) ;
		ovAjax.send(null) ;
	}
}


/*
USER PAGE JAVASCRIPT
*/

function BlockUser(userId, username) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'block_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user blocked OK
				
				// ADJUST FOLLOW BUTTON
				// change class and text
				$('#user-follow-link').removeClass('user-is-following');
				$('#user-follow-link').addClass('user-is-not-following');
				$('#user-follow-link').text("Follow");
				
				// turn off the mouseover and mouseout
				$('#user-follow-link').off('mouseover');
				$('#user-follow-link').off('mouseout');

				// change click to now unfollow user
				$('#user-follow-link').click(function() {
					FollowUser(userId, username);
				});

				// ADJUST BLOCK BUTTON
				$('#user-block-link').text("Unblock " + username);
				$('#user-block-link').click(function() {
					UnblockUser(userId, username);
				});
			} else {
				// user not blocked

			}
		}
	});
}

function UnblockUser(userId, username) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'unblock_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user blocked OK
				
				$('#user-block-link').text("Block " + username);
				$('#user-block-link').click(function() {
					BlockUser(userId, username);
				});
			} else {
				// user not blocked

			}
		}
	});
}

function FollowUser(userId, username) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'follow_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user followed OK
				
				// adjust follow button

				// change class and text
				$('#user-follow-link').removeClass('user-is-not-following');
				$('#user-follow-link').addClass('user-is-following');
				$('#user-follow-link').text("Following");
				
				// add function to handle mouse over and out
				$('#user-follow-link').mouseover(function() {
					$('#user-follow-link').text("Unfollow");
				});
				$('#user-follow-link').mouseout(function() {
					$('#user-follow-link').text("Following");
				});

				// change click to now unfollow user
				$('#user-follow-link').click(function() {
					UnfollowUser(userId, username);
				});

				// adjust block button
				$('#user-block-link').text("Block " + username);
				$('#user-block-link').click(function() {
					BlockUser(userId, username);
				});
			} else {
				// user not followed
				ShowMessage('Error', response.message);
			}
		}
	});
}

function UnfollowUser(userId, username) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'unfollow_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user unfollowed OK
				
				// adjust follow button

				// change class and text
				$('#user-follow-link').removeClass('user-is-following');
				$('#user-follow-link').addClass('user-is-not-following');
				$('#user-follow-link').text("Follow");
				
				// turn off the mouseover and mouseout
				$('#user-follow-link').off('mouseover');
				$('#user-follow-link').off('mouseout');

				// change click to now unfollow user
				$('#user-follow-link').click(function() {
					FollowUser(userId, username);
				});
			} else {
				// user not unfollowed
				ShowMessage('Error', 'An error occurred');
			}
		}
	});
}

function UserFollowingUnfollowUser(userId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'unfollow_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user unfollowed OK
				$("#user-following-" + userId).slideUp();
			} else {
				// user not unfollowed
				ShowMessage('Error', 'An error occurred');
			}
		}
	});
}

function UserFollowersUnfollowUser(userId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'unfollow_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user unfollowed OK
				$("#user-follower-follow-link-" + userId).removeClass('red');
				$("#user-follower-follow-link-" + userId).addClass('confirm');
				
				var title = $("#user-follower-follow-link-" + userId).attr('title');
				$("#user-follower-follow-link-" + userId).attr('title', title.replace('Unfollow', 'Follow'));
				
				$("#user-follower-unfollow-image-" + userId).fadeOut();
				$("#user-follower-follow-image-" + userId).fadeIn();
			} else {
				// user not unfollowed
				ShowMessage('Error', 'An error occurred');
			}
		}
	});
}

function UserFollowingBlockUser(userId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'block_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user blocked OK
				$("#user-following-" + userId).slideUp();
			} else {
				// user not blocked

			}
		}
	});
}

function UserFollowersBlockUser(userId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'block_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user blocked OK
				$("#user-follower-" + userId).slideUp();
			} else {
				// user not blocked

			}
		}
	});
}

function UserFollowingFollowUser(userId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'follow_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user followed OK
				$("#user-following-" + userId).slideUp();
			} else {
				// user not followed
				ShowMessage('Error', response.message);
			}
		}
	});
}

function UserFollowersFollowUser(userId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'follow_user',
			id: userId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// user followed OK
				$("#user-follower-follow-link-" + userId).removeClass('confirm');
				$("#user-follower-follow-link-" + userId).addClass('red');
				
				var title = $("#user-follower-follow-link-" + userId).attr('title');
				$("#user-follower-follow-link-" + userId).attr('title', title.replace('Follow', 'Unfollow'));
				
				$("#user-follower-follow-image-" + userId).fadeOut();
				$("#user-follower-unfollow-image-" + userId).fadeIn();
			} else {
				// user not followed
				ShowMessage('Error', response.message);
			}
		}
	});
}

/*
ALERT PAGE JAVASCRIPT
*/

function MarkAlertRead(alertType, alertId) {
	var ovAjax = GetXHTTPRequest();
	ovAjax.onreadystatechange = function()
	{
		if(ovAjax.readyState == 4)
		{
			var xmlObject = ParseXML(ovAjax.responseText);
			
			var $xml = $( xmlObject );
			var $responseCode = $xml.find("code");
			var $message = $xml.find("message");
	
			if ($responseCode.text() == "OK") {
				// alert marked read
				$("#alert-" + alertId).slideUp();
				
				switch(alertType) {
					case "shares":
						var alertCount = $("#share-alert-count").text();
						alertCount = GetAlertCount(alertCount);
						alertCount -= 1;
						if (alertCount == 0) {
							$("#share-alert-count").text("");
							$("#no-comments-alert").html("No new alerts");
							$("#no-comments-alert").fadeIn('fast');
						} else {
							$("#share-alert-count").text(" (" + alertCount + ")");
						}
						break;
					case "comments":
						var alertCount = $("#comment-alert-count").text();
						alertCount = GetAlertCount(alertCount);
						alertCount -= 1;
						if (alertCount == 0) {
							$("#comment-alert-count").text("");
							$("#no-comments-alert").html("No new alerts");
							$("#no-comments-alert").fadeIn('fast');
						} else {
							$("#comment-alert-count").text(" (" + alertCount + ")");
						}
						break;
					case "followers":
						var alertCount = $("#follower-alert-count").text();
						alertCount = GetAlertCount(alertCount);
						alertCount -= 1;
						if (alertCount == 0) {
							$("#follower-alert-count").text("");
							$("#no-comments-alert").html("No new alerts");
							$("#no-comments-alert").fadeIn('fast');
						} else {
							$("#follower-alert-count").text(" (" + alertCount + ")");
						}
						break;
					case "favorites":
						var alertCount = $("#favorite-alert-count").text();
						alertCount = GetAlertCount(alertCount);
						alertCount -= 1;
						if (alertCount == 0) {
							$("#favorite-alert-count").text("");
							$("#no-comments-alert").html("No new alerts");
							$("#no-comments-alert").fadeIn('fast');
						} else {
							$("#favorite-alert-count").text(" (" + alertCount + ")");
						}
						break;
				}
				
				var shareAlerts = GetAlertCount($("#share-alert-count").text()),
					commentAlerts = GetAlertCount($("#comment-alert-count").text()),
					followerAlerts = GetAlertCount($("#follower-alert-count").text()),
					favoriteAlerts = GetAlertCount($("#favorite-alert-count").text());
				var totalAlerts = shareAlerts + commentAlerts + followerAlerts + favoriteAlerts;
				var siteName = GetSiteName();
				
				if (totalAlerts == 0) {
					$("#imgNewAlerts").fadeOut('fast');
					$("#imgAlerts").fadeIn('fast');
					$("#header-alert-count").text("");
					document.title = "Notifications | " + siteName;
				} else {
					$("#header-alert-count").text(" (" + totalAlerts + ")");
					document.title = "Notifications (" + totalAlerts + ") | " + siteName;
				}
			} else {
				// error with marking read

			}
		}
	}

	var requestString = '/php/ov-ajax.php?action=mark_alert_read&alert_type=' + alertType + '&id=' + alertId;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function MarkAllAlertsRead(alertType) {
	var ovAjax = GetXHTTPRequest();
	ovAjax.onreadystatechange = function()
	{
		if(ovAjax.readyState == 4)
		{
			var xmlObject = ParseXML(ovAjax.responseText);
			
			var $xml = $( xmlObject );
			var $responseCode = $xml.find("code");
			var $message = $xml.find("message");
	
			if ($responseCode.text() == "OK") {
				// alert marked read
				$(".alert").slideUp();
				
				switch (alertType) {
					case "shares":
						$("#share-alert-count").text("");
						break;
					case "comments":
						$("#comment-alert-count").text("");
						break;
					case "followers":
						$("#follower-alert-count").text("");
						break;
					case "favorites":
						$("#favorite-alert-count").text("");
						break;
					case "all":
						$("#share-alert-count").text("");
						$("#comment-alert-count").text("");
						$("#follower-alert-count").text("");
						$("#favorite-alert-count").text("");
						break;
				}
				
				var shareAlerts = GetAlertCount($("#share-alert-count").text()),
					commentAlerts = GetAlertCount($("#comment-alert-count").text()),
					followerAlerts = GetAlertCount($("#follower-alert-count").text()),
					favoriteAlerts = GetAlertCount($("#favorite-alert-count").text());
				var totalAlerts = shareAlerts + commentAlerts + followerAlerts + favoriteAlerts;
				var siteName = GetSiteName();
				
				if (totalAlerts == 0 || isNaN(totalAlerts)) {
					$("#imgNewAlerts").fadeOut('fast');
					$("#imgAlerts").fadeIn('fast');
					$("#header-alert-count").text("");
					document.title = "Notifications | " + siteName;
				} else {
					$("#header-alert-count").text(" (" + totalAlerts + ")");
					document.title = "Notifications (" + totalAlerts + ") | " + siteName;
				}
				
				$("#no-comments-alert").html("No new alerts");
				$("#no-comments-alert").fadeIn('fast');
			} else {
				// error with marking read

			}
		}
	}

	var requestString = '/php/ov-ajax.php?action=mark_all_alerts_read&alert_type=' + alertType;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

/*
ACCOUNT PAGE JAVASCRIPT
*/

function DeleteAvatar() {
	var ovAjax = GetXHTTPRequest();
	ovAjax.onreadystatechange = function()
	{
		if(ovAjax.readyState == 4)
		{
			var responseXml = ovAjax.responseText;
			var xmlObject = GetXMLDocument(responseXml);

			var responseCode = xmlObject.getElementsByTagName("code")[0].childNodes[0].nodeValue;
	
			if (responseCode == "OK") {
				$("#user_avatar").slideUp('slow');
			} else {
				
			}
		}
	}
	
	var requestString = '/php/ov-ajax.php?action=delete_avatar';
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

/*
COMMENTING JAVASCRIPT
*/

var defaultComment = "<div class=\"comment\"><div class=\"comment-user\"><img src=\"%USERAVATAR%\" alt=\"=\" /><a href=\"/users/%LCUSERNAME%\" title=\"\">%USERNAME%</a><span class=\"date\">posted just now</span></div><div class=\"body\">%BODY%</div><div class=\"actions refresh-actions\">Refresh to see Actions</div></div><div class=\"comment-separator\"></div>";

function PostComment() {
	var submissionId = $('#submission-id').val(),
		body = $('#post-comment-body').val();
		
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'post_comment',
			id: submissionId,
			body: body
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// comment posted properly
				var commentId = response.commentId;
				var userAvatar = response.userAvatar;
				var username = response.username;
				var commentBody = response.body;

				var commentHtml = defaultComment;
				commentHtml = commentHtml.replace('%USERAVATAR%', userAvatar);
				commentHtml = commentHtml.replace('%USERNAME%', username);
				commentHtml = commentHtml.replace('%LCUSERNAME%', username.toLowerCase());
				commentHtml = commentHtml.replace('%BODY%', commentBody);

				$('#submission-comments').prepend(commentHtml);

				// clear out inputs
				$('#post-comment-body').val('');

				$.fancybox.close();
			} else {
				// user not followed
				$('#post-comment-error').text(response.errorMessage);
				$('#post-comment-error').fadeIn('fast');
			}
		}
	});

	//var requestString = '/php/ov-ajax.php?action=post_comment&id=' + submissionId + '&body=' + escape(body);
}

function PostCommentReply() {
	var submissionId = $('#submission-id').val(),
		body = $('#reply-text').val(),
		replyToId = $('#reply-id').val();
		
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'post_comment_reply',
			submission_id: submissionId,
			body: body,
			reply_id: replyToId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// comment posted properly
				var commentId = response.commentId;
				var userAvatar = response.userAvatar;
				var username = response.username;
				var commentBody = response.body;

				var commentHtml = defaultComment;
				commentHtml = commentHtml.replace('%USERAVATAR%', userAvatar);
				commentHtml = commentHtml.replace('%USERNAME%', username);
				commentHtml = commentHtml.replace('%LCUSERNAME%', username.toLowerCase());
				commentHtml = commentHtml.replace('%BODY%', commentBody);

				$('#replies-div-' + replyToId).append(commentHtml);
				window.location.hash = '#comment-' + commentId;

				// clear out inputs
				$('#reply-text').val('');
				$('#reply-id').val('');

				$.fancybox.close();
			} else {
				// user not followed
				$('#comment-reply-error').text(response.errorMessage);
				$('#comment-reply-error').fadeIn('fast');
			}
		}
	});
}

function ToggleCommentFavorite(commentId)
{
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'toggle_comment_favorite',
			id: commentId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				if (response.message == "is_favorite") {
					$('#favorite-text-' + commentId).html("Unfavorite");
				} else {
					$('#favorite-text-' + commentId).html("Favorite");
				}
			} else {
				noty({
					text: response.errorMessage,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function EditComment() {
	var body = $('#edit-comment-body').val(),
		commentId = $('#comment-to-edit-id').val();
	
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'edit_comment',
			id: commentId,
			body: body
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				$('#comment-body-' + commentId).html(response.body);
				$('#comment-edited-' + commentId).text('[EDITED]');
				$('#comment-' + commentId + ' div.actions').show();
				$('#comment-to-edit-id').val('');
			} else {
				noty({
					text: response.message,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function DeleteComment(commentId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'delete_comment',
			id: commentId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				$("#comment-" + commentId).html("<div class=\"comment-hidden\">This comment has been deleted by its author.</div>")
			} else {
				noty({
					text: response.message,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

/*
VOTING JAVASCRIPT
*/

function CommentVote(commentId, direction) {
	$.ajax({
		url: '/php/ov-ajax.php?action=comment_vote',
		data: {
			id: commentId,
			direction: direction
		},
		type: 'GET',
		success: function(data) {
			var xmlObject = ParseXML(data);
			
			var $xml = $( xmlObject );
			var $responseCode = $xml.find("code");
			var $message = $xml.find("message");
	
			if ($responseCode.text() == "OK") {
				var commentScore = $message.text();
				if(direction > 0) {
					$('#comment_score_' + commentId).html(commentScore);
					$('#comment_vote_down_' + commentId).attr('class', 'comment-down-vote');
					$('#comment_vote_up_' + commentId).attr('class', 'comment-up-voted');
					
					$('#comment_vote_down_' + commentId).click( function() { CommentVote(commentId, -1); } );
					$('#comment_vote_up_' + commentId).click( function() { CommentVote(commentId, 0); } );
				} else if (direction == 0) {
					$('#comment_score_' + commentId).html(commentScore);
					$('#comment_vote_down_' + commentId).attr('class', 'comment-down-vote');
					$('#comment_vote_up_' + commentId).attr('class', 'comment-up-vote');
					
					$('#comment_vote_down_' + commentId).click( function() { CommentVote(commentId, -1); } );
					$('#comment_vote_up_' + commentId).click( function() { CommentVote(commentId, 1); } );
				} else {
					$('#comment_score_' + commentId).html(commentScore);
					$('#comment_vote_down_' + commentId).attr('class', 'comment-down-voted');
					$('#comment_vote_up_' + commentId).attr('class', 'comment-up-vote');
					
					$('#comment_vote_down_' + commentId).click( function() { CommentVote(commentId, 0); } );
					$('#comment_vote_up_' + commentId).click( function() { CommentVote(commentId, 1); } );
				}
			} else {
				ShowMessage('Error', xmlObject.getElementsByTagName("message")[0].firstChild.nodeValue);
			}
		}
	});
}

function SubmissionVote(submissionId, direction) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'submission_vote',
			id: submissionId,
			direction: direction
		},
		type: 'GET',
		success: function(data) {
			var xmlObject = ParseXML(data);
			
			var $xml = $( xmlObject );
			var $responseCode = $xml.find("code");
			var $message = $xml.find("message");
	
			if ($responseCode.text() == "OK") {
				var submissionScore = $message.text();
				if(direction > 0) {
					// voted up
					$('#score_' + submissionId).html(submissionScore);
					
					$('#submission_vote_down_button_' + submissionId).attr('class', 'story_down_vote');
					$('#submission_vote_up_button_' + submissionId).attr('class', 'story_up_voted');
					
					$('#submission_vote_up_button_' + submissionId).click( function() { SubmissionVote(submissionId, 0); });
					$('#submission_vote_down_button_' + submissionId).click( function() { SubmissionVote(submissionId, -1); });
					
					return false;
				} else if (direction == 0) {
					// canceled vote
					$('#score_' + submissionId).html(submissionScore);
					
					$('#submission_vote_down_button_' + submissionId).attr('class', 'story_down_vote');
					$('#submission_vote_up_button_' + submissionId).attr('class', 'story_up_vote');
					
					$('#submission_vote_down_button_' + submissionId).click( function() { SubmissionVote(submissionId, -1); });
					$('#submission_vote_up_button_' + submissionId).click( function() { SubmissionVote(submissionId, 1); });
					
					return false;
				} else {
					// voted down
					$('#score_' + submissionId).html(submissionScore);
					
					$('#submission_vote_down_button_' + submissionId).attr('class', 'story_down_voted');
					$('#submission_vote_up_button_' + submissionId).attr('class', 'story_up_vote');
					
					$('#submission_vote_down_button_' + submissionId).click( function() { SubmissionVote(submissionId, 0); });
					$('#submission_vote_up_button_' + submissionId).click( function() { SubmissionVote(submissionId, 1); });
					
					return false;
				}
			} else {
				return false;
			}
		}
	});
}

/*
PASSWORD RECOVERY JAVASCRIPT
*/

function GetSecurityQuestion() {
	var email = $("#email").val();
	
	var ovAjax = GetXHTTPRequest();
	ovAjax.onreadystatechange = function()
	{
		if(ovAjax.readyState == 4)
		{
			var xmlObject = ParseXML(ovAjax.responseText);
			
			var $xml = $( xmlObject );
			var $responseCode = $xml.find("code");
			var $message = $xml.find("message");
	
			if ($responseCode.text() == "OK") {
				$("#security-question").text($message.text());
				$("#user-email").val(email);
				$("#recover-step-1").slideUp('fast');
				$("#recover-step-2").slideDown('fast');				
			} else {
				ShowMessage('Error', $message.text());
			}
		}
	}
	
	var requestString = '/php/ov-ajax.php?action=get_security_question&email=' + email;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function CheckSecurityAnswer() {
	var answer = $("#answer").val();
	var email = $("#user-email").val();
	
	var ovAjax = GetXHTTPRequest();
	ovAjax.onreadystatechange = function()
	{
		if(ovAjax.readyState == 4)
		{
			var xmlObject = ParseXML(ovAjax.responseText);
			
			var $xml = $( xmlObject );
			var $responseCode = $xml.find("code");
			var $message = $xml.find("message");
	
			if ($responseCode.text() == "OK") {
				$("#recover-step-2").slideUp('fast');
				$("#recover-step-3").slideDown('fast');				
			} else {
				ShowMessage('Error', $message.text());
			}
		}
	}
	
	var requestString = '/php/ov-ajax.php?action=check_security_answer&answer=' + escape(answer) + '&email=' + email;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function ResetPassword() {
	var 
		password1 = $("#password-1").val(),
		password2 = $("#password-2").val(),
		email = $("#user-email").val();
	
	var ovAjax = GetXHTTPRequest();
	ovAjax.onreadystatechange = function()
	{
		if(ovAjax.readyState == 4)
		{
			var xmlObject = ParseXML(ovAjax.responseText);
			
			var $xml = $( xmlObject );
			var $responseCode = $xml.find("code");
			var $message = $xml.find("message");
	
			if ($responseCode.text() == "OK") {
				$("#recover-step-3").slideUp('fast');
				$("#recover-complete").slideDown('fast');				
			} else {
				ShowMessage('Error', $message.text());
			}
		}
	}
	
	var requestString = '/php/ov-ajax.php?action=reset_password&email=' + email + '&password=' + password1;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

/*
SUBMIT NEW LINK JAVASCRIPT
*/

function ValidateSubmissionURL() {
	$("#submit-loading-box").modal();

	var submissionUrl = $("#url").val(),
		submissionType = $("#type").val();

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'validate_url',
			url: submissionUrl,
			type: submissionType
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				$("#submission-url").val(submissionUrl);
				$("#submission-type").val(submissionType);

				var title = response.title,
					summary = response.summary;

				if (summary.length > 500) {
					summary = summary.substring(0,500);
				}

				var charsRemaining = 500 - summary.length;
				$("#submit_chars_left").html(charsRemaining + " characters remaining");
				
				$("#title").val(unescape(title));
				$("#summary").val(unescape(summary));

				if (submissionType == "photo") {
					BuildThumbnailChooser();
				} else {
					$("#thumbnail_chooser").css("display", "none");
				}
				
				$.modal.close();
				
				$("#submit-step-1").fadeOut('fast');
				$("#submit-step-2").fadeIn('fast');
			} else if (response.status == "EXISTS") {
				window.location = response.submissionUrl;
			} else {
				noty({
					text: response.message,
					type: 'error',
					layout: 'top'
				});
			}
		}
	});
}

function BuildThumbnailChooser() {
	var submissionUrl = $("#url").val();

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'get_thumbnails',
			url: submissionUrl
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				if (response.images.length == 1) {
					var img = response.images[0];
					var imageSrc = img.src;
					var imageId = img.id;
					$("#thumbnail_images").html('<span class="submission_image_chooser"><img src="' + imageSrc + '" id="' + imageId + '" alt="" width="200" class="submission-image-choice" style="background:#00f" onclick="ChooseImage(this)" /></span>');
					$("#thumbnail_url").val(imageSrc);
				} else {
					$.each(response.images, function(i, value) { 
						var imageSrc = value.src;
						var imageId = value.id;
						var currentHtml = $("#thumbnail_images").html();
						currentHtml += '<span class="submission_image_chooser"><img src="' + imageSrc + '" id="' + imageId + '" alt="" width="200" class="submission-image-choice" onclick="ChooseImage(this)" /></span>';
						$("#thumbnail_images").html(currentHtml);
					});
				}

				$("#clear-thumbnail").css("display", "block");
				$("#image-loader-wait").fadeOut();
			} else if (response.status =="NOIMAGES") {
				noty({
					text: response.message,
					type: 'alert',
					layout: 'top'
				});

				$("#clear-thumbnail").css("display", "block");
				$("#image-loader-wait").fadeOut();

				var arr = submissionUrl.split('.');
				var extension = arr[arr.length - 1];
				if (extension == 'jpg' || extension == 'gif' || extension == 'png' || extension == 'jpeg') {
					$("#thumbnail_chooser").hide(0);
				}
			} else {
				noty({
					text: response.message,
					type: 'error',
					layout: 'top'
				});

				$("#image-loader-wait").fadeOut();
				
				var arr = submissionUrl.split('.');
				var extension = arr[arr.length - 1];
				if (extension == 'jpg' || extension == 'gif' || extension == 'png' || extension == 'jpeg') {
					$("#thumbnail_chooser").hide(0);
				}
			}
		}
	});
}

function submitLinkToSite(ignoreDuplicates) {
	$("#category_error").css("display", "none");

	var url = $('#submission-url').val(),
		type = $('#submission-type').val(),
		title = $('#title').val(),
		summary = $('#summary').val(),
		tags = $('#tags').val(),
		thumbnail = $("#thumbnail_url").val(),
		category = document.getElementsByName('category[]');

	var nCategories = 0 ;
	var categories = "";
	for(var i = 0; i < category.length; i++)
	{
		if( category[i].checked == true )
		{
			categories += category[i].value + ",";
			nCategories++ ;
		}
	}

	categories = categories.substring(0, categories.length - 1);
	
	if (nCategories > 6 || nCategories == 0) {
		$("#tag_error").html("You must give your submission 1-6 categories");
		$("#tag_error").css("display", "block");
	} else {
		$.ajax({
			url: '/php/ov-ajax.php',
			data: {
				action: 'submit_link',
				url: url,
				type: type,
				title: title,
				summary: summary,
				tags: tags,
				thumbnail: thumbnail,
				categories: categories,
				ignore_duplicates: ignoreDuplicates
			},
			type: 'GET',
			success: function(data) {
				var response = $.parseJSON(data);

				if (response.status == "OK" || (response.status == "DUPLICATES" && ignoreDuplicates == true)) {
					window.location = response.url;
				} else if (response.status = "DUPLICATES") {
					var dupeHtml = "";
					$.each(response.submissions, function(i, value) { 
						dupeHtml += "<div class=\"submission\">";
							dupeHtml += "<div class=\"submission-content\">";

								if (value.Type.toUpperCase() == "PHOTO" || ( (value.Type.toUpperCase() == "STORY" || value.Type.toUpperCase() == "PODCAST") && value.Thumbnail != "/img/default_photo.jpg")) { 
									dupeHtml += "<div class=\"submission-thumbnail\">";
										dupeHtml += "<a href=\"" + value.PageURL + "\">";
											dupeHtml += "<img src=\"" + value.Thumbnail + "\" alt=\"\" width=\"100\"/>";
										dupeHtml += "</a>";
									dupeHtml += "</div>";
								} 

								dupeHtml += "<div class=\"submission-details\">";
									dupeHtml += "<div class=\"submission-title\">";
										dupeHtml += "<a href=\"" + value.PageURL + "\">" + value.Title + "</a>";
									dupeHtml += "</div>";
									dupeHtml += "<div class=\"submission-summary\">";
										dupeHtml += "<span class=\"submission-domain\">";
											dupeHtml += "<a href=\"" + value.URL + "\">" + value.Domain + "</a>";
										dupeHtml += "</span>";
										dupeHtml += value.Summary;
									dupeHtml += "</div>";
									dupeHtml += "<div class=\"submitted-by\">Submitted " + value.SubmissionDate + "</div>";
								dupeHtml += "</div>"
							dupeHtml += "</div>"
						dupeHtml += "</div>"
						dupeHtml += "<div class=\"submission-seperator\"></div>";
					});

					$('#submit-duplicates-area').html(dupeHtml);
					$("#submit-step-3-site").fadeOut('fast');
					$("#submit-step-3-group").fadeOut('fast');
					$("#submit-duplicates").fadeIn('fast');
				} else {
					noty({
						text: response.message,
						type: 'error',
						layout: 'top'
					});
				}
			}
		});
	}
}


function AddNewList() {
	var listName = $('#list-name').val();
	var isPrivate;
	if ($('#list-private').is(':checked')) {
		isPrivate = "private";
	} else {
		isPrivate = "public";
	}

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'add_list',
			name: listName,
			is_private: isPrivate
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// list added ok
				var newListHtml = "<div class=\"list-checkbox\"><input type=\"checkbox\" name=\"list[]\" value=\"" + response.listId + "\" /> " + response.listName + "</div>";
				$('#list-choices').append(newListHtml);
				$('#new-list-form').slideUp('fast');
			} else {
				// error adding to list
				$('#user-add-list-error').text(response.errorMessage);
				$('#user-add-list-error').fadeIn('fast');
			}
		}
	});
}

function AdjustListsForUser() {
	var userToAddId = $('#add-user-to-list-user-id').val();
	var listsToAdd = "";
	var listsToDelete = "";
	
	$("input[name='list[]']").each(function () {
    	if ($(this).is(':checked')) {
    		listsToAdd += $(this).val() + ",";
    	} else {
    		listsToDelete += $(this).val() + ",";
    	}
	});

	if (listsToAdd.length > 0) {
		listsToAdd = listsToAdd.substring(0, listsToAdd.length - 1);
	}

	if (listsToDelete.length > 0) {
		listsToDelete = listsToDelete.substring(0, listsToDelete.length - 1);
	}

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'adjust_lists_for_user',
			user_to_add: userToAddId,
			add_lists: listsToAdd,
			delete_lists: listsToDelete
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
			var htmlString;

			if (response.status == "OK") {
				// user added to list(s) ok
				htmlString = "<div class=\"success-box\" style=\"margin-top:20px\">Changes saved</div>";						
			} else if (response.status == "WARNING") {
				// warning adding user to list(s)
				htmlString = "<div class=\"warning-box\" style=\"margin-top:20px\">" + response.errorMessage + "</div>";
			} else {
				// error adding user to list(s)
				htmlString = "<div class=\"error-box\" style=\"margin-top:20px\">" + response.errorMessage + "</div>";
			}

			$('#add-to-list-form-content').html(htmlString);
		}
	});
}

function RemoveUserFromList(userId, listId) {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'remove_user_from_list',
			user_id: userId,
			list_id: listId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				$('#list' + listId + '-user' + userId).fadeOut('fast');
				$('#list' + listId + '-user' + userId).remove();

				var remainingItems = $('#listMembers' + listId + ' li').size();

				if (remainingItems == 0) {
					// no more
					$('#listMembersArea' + listId).html('<p class="no-members">No Members</p>');
				}
			}
		}
	});
}

function listManagementAddList() {
	var listName = $('#list-name').val();
	var isPrivate;
	if ($('#list-private').is(':checked')) {
		isPrivate = "private";
	} else {
		isPrivate = "public";
	}

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'add_list',
			name: listName,
			is_private: isPrivate
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// list added ok
				window.location = "/manage-lists?list=" + response.listId;
			} else {
				// error adding to list
				$('#add-list-error').text(response.errorMessage);
				$('#add-list-error').fadeIn('fast');
			}
		}
	});
}

function listManagementEditList() {
	var listName = $('#edit-list-name').val(),
		listId = $('#edit-list-id').val();
	var isPrivate;

	if ($('#edit-list-private').is(':checked')) {
		isPrivate = "private";
	} else {
		isPrivate = "public";
	}

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'edit_list',
			name: listName,
			is_private: isPrivate,
			id: listId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// list added ok
				window.location = "/manage-lists?list=" + response.listId;
			} else {
				// error adding to list
				$('#edit-list-error').text(response.errorMessage);
				$('#edit-list-error').fadeIn('fast');
			}
		}
	});
}

function deleteList() {
	var listId = $('#list-delete-id').val();

	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'delete_list',
			id: listId
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);
	
			if (response.status == "OK") {
				// list added ok
				window.location = "/manage-lists";
			} else {
				// error adding to list
				$('#list-error-' + listId).text(response.errorMessage);
				$('#list-error-' + listId).fadeIn('fast');
			}
		}
	});
}

function getUserFriendsAsArray() {
	$.ajax({
		url: '/php/ov-ajax.php',
		data: {
			action: 'get_user_friends'
		},
		type: 'GET',
		success: function(data) {
			var response = $.parseJSON(data);

			if (response.status == "OK") {
				var friends = Array();
				$.each(response.friends, function(index, friend) {
					friends.push(friend.username);
				});

				return friends;
			} else {
				return new Array();
			}
		}
	});
}






