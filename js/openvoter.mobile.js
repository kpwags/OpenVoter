$(document).ready(function() {
	$("#category-link").fancybox({		
		'width'	: 290,
		'padding' : 0,
		'margin' : 15
	});
	
	$(".modal-form-link").fancybox({
		'width'	: 300,
		'padding' : 0,
		'margin' : 10
	})
	
	$("#user-avatar-link").fancybox({
		'titlePosition' : 'over'
	});
});

function navigateTo(url) {
	window.location = url;
}

/*------------------------
-----SUBMISSION PAGE------
------------------------*/
function toggleCommentReplies(commentId) {
	$("#comment-replies-" + commentId).toggle('fast');
	$("#hidden-comment-replies-" + commentId).toggle('fast');
}

function toggleSubmissionFavorite(submissionId)
{
	var ovAjax = GetXHTTPRequest();
	
	ovAjax.onreadystatechange = function()
	{
		if(ovAjax.readyState == 4)
		{
			if (ovAjax.responseText != "") {
				var xmlObject = ParseXML(ovAjax.responseText);

				var $xml = $( xmlObject );
				var $responseCode = $xml.find("code");
				var $message = $xml.find("message");

				if ($responseCode.text() == "OK") {
					var favoriteStatus = $message.text();
					if (favoriteStatus == "is_favorite") {
						$("#favorite-button").html("Unfavorite");
					} else {
						$("#favorite-button").html("Favorite");
					}
				}
			}
		}
	}

	var requestString = '/php/ov-ajax.php?action=toggle_submission_favorite&id=' + submissionId;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function shareSubmission(submissionId)
{
	var message = $("#share-message").val();
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
				$("#share-form").html("You have shared this with your followers");
				$("#share-form").addClass('success-line');
			} else {
				$("#share-form").html($message.text());
				$("#share-form").addClass('error-line');
			}
		}
	}
	
	var requestString = "/php/ov-ajax.php?action=share_submission&message=" + escape(message) + "&id=" + escape(submissionId);
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function submissionVote(submissionId, direction) {
	var ovAjax = GetXHTTPRequest();
	var hasVoted = false;
	
	ovAjax.onreadystatechange = function()
	{
		if(ovAjax.readyState == 4)
		{
			var xmlObject = ParseXML(ovAjax.responseText);
			
			var $xml = $( xmlObject );
			var $responseCode = $xml.find("code");
			var $message = $xml.find("message");
	
			if ($responseCode.text() == "OK") {
				var submissionScore = $message.text();
				if(direction > 0) {
					// voted up
					$('#submission-score').html(submissionScore);
					
					$('#submission-down-vote').attr('class', 'submission-down-vote');
					$('#submission-up-vote').attr('class', 'submission-up-voted');
					
					$('#submission-up-vote').click( function() { submissionVote(submissionId, 0); });
					$('#submission-down-vote').click( function() { submissionVote(submissionId, -1); });
					
					return false;
				} else if (direction == 0) {
					// canceled vote
					$('#submission-score').html(submissionScore);
					
					$('#submission-down-vote').attr('class', 'submission-down-vote');
					$('#submission-up-vote').attr('class', 'submission-up-vote');
					
					$('#submission-down-vote').click( function() { submissionVote(submissionId, -1); });
					$('#submission-up-vote').click( function() { submissionVote(submissionId, 1); });
					
					return false;
				} else {
					// voted down
					$('#submission-score').html(submissionScore);
					
					$('#submission-down-vote').attr('class', 'submission-down-voted');
					$('#submission-up-vote').attr('class', 'submission-up-vote');
					
					$('#submission-down-vote').click( function() { submissionVote(submissionId, 0); });
					$('#submission-up-vote').click( function() { submissionVote(submissionId, 1); });
					
					return false;
				}
			} else {
				return false;
			}
		}
	}
	
	if (!hasVoted) {
		var requestString = '/php/ov-ajax.php?action=submission_vote&id=' + submissionId + '&direction=' + direction;
		ovAjax.open("GET", requestString, true) ;
		ovAjax.send(null) ;
		hasVoted = true;
	}
}

function showCommentForm() {
	$("#comment-replied-to-id").val('NO');
	$("#comment-form").slideDown();
	$("#comment-body").focus();
}

function showCommentReplyForm(commentId) {
	$("#comment-replied-to-id").val(commentId);
	$("#comment-form").slideDown();
	$("#comment-body").focus();
}

function hideCommentForm() {
	$("#comment-form").slideUp();
}

function postComment(submissionId) {
	var body = $("#comment-body").val(),
		parentCommentId = $("#comment-replied-to-id").val();
		
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
				location.reload();
			} else {
				alert($message.text());
			}
		}
	}

	var requestString;
	if (parentCommentId != "NO") {
		requestString = '/php/ov-ajax.php?action=post_comment_reply&id=' + submissionId + '&body=' + escape(body) + '&parent_comment_id=' + parentCommentId;
	} else {
		requestString = '/php/ov-ajax.php?action=post_comment&id=' + submissionId + '&body=' + escape(body);
	}
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function toggleTagsList() {
	$("#submission-tags-list").toggle('fast');
	
	if ($("#tags-collapse-arrow img").attr("src") != "/img/arrow-collapsed.png") {
		$("#tags-collapse-arrow img").attr("src", "/img/arrow-collapsed.png");
		$("#tags-collapse-link").html("Show Tags");
	} else {
		$("#tags-collapse-arrow img").attr("src", "/img/arrow-expanded.png");
		$("#tags-collapse-link").html("Hide Tags");
	}
}

/*------------------------
--------USER PAGE---------
------------------------*/
function toggleUserDetails() {
	$("#user-details-div").toggle('fast');
	
	if ($("#details-collapse-arrow img").attr("src") != "/img/arrow-collapsed.png") {
		$("#details-collapse-arrow img").attr("src", "/img/arrow-collapsed.png");
		$("#details-collapse-link").html("Show User Bio");
	} else {
		$("#details-collapse-arrow img").attr("src", "/img/arrow-expanded.png");
		$("#details-collapse-link").html("Hide User Bio");
	}
}

function followUser(userId) {
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
				// user followed OK
				$("#follow-button").removeClass('cancel-button ok-button normal-button');
				$("#follow-button").addClass('cancel-button');
				$("#follow-button").html('Unfollow');
				$("#follow-button").click( function() { unfollowUser(userId) });
				$("#block-button").removeClass('cancel-button ok-button normal-button');
				$("#block-button").addClass('cancel-button');
				$("#block-button").html('Block');
				$("#block-button").click( function() { blockUser(userId) });
			} else {
				// user not followed
				alert("Error: " + $message.text());
			}
		}
	}

	var requestString = '/php/ov-ajax.php?action=follow_user&id=' + userId;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function unfollowUser(userId) {
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
				// user followed OK
				$("#follow-button").removeClass('cancel-button ok-button normal-button');
				$("#follow-button").addClass('ok-button');
				$("#follow-button").html('Follow');
				$("#follow-button").click( function() { followUser(userId) });
			} else {
				// user not followed
			}
		}
	}

	var requestString = '/php/ov-ajax.php?action=unfollow_user&id=' + userId;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function blockUser(userId) {
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
				// user blocked OK
				$("#block-button").removeClass('cancel-button ok-button normal-button');
				$("#block-button").addClass('ok-button');
				$("#block-button").html('Unblock');
				$("#block-button").click( function() { unblockUser(userId) });
				$("#follow-button").removeClass('cancel-button ok-button normal-button');
				$("#follow-button").addClass('ok-button');
				$("#follow-button").html('Follow');
				$("#follow-button").click( function() { followUser(userId) });
			} else {
				// user not blocked

			}
		}
	}

	var requestString = '/php/ov-ajax.php?action=block_user&id=' + userId;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function unblockUser(userId) {
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
				// user blocked OK
				$("#block-button").removeClass('cancel-button ok-button normal-button');
				$("#block-button").addClass('cancel-button');
				$("#block-button").html('Block');
				$("#block-button").click( function() { blockUser(userId) });
			} else {
				// user not blocked

			}
		}
	}

	var requestString = '/php/ov-ajax.php?action=unblock_user&id=' + userId;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

function toggleUserFriends(listName) {
	if (listName == "followers") {
		$(".user-following-li").fadeOut('fast');
		$(".user-follower-li").fadeIn('fast');
		$("#friends-title-li").html("Followers");
		$("#following-toggle-link").removeClass("active");
		$("#followers-toggle-link").addClass("active");
	} else {
		$(".user-follower-li").fadeOut('fast');
		$(".user-following-li").fadeIn('fast');
		$("#friends-title-li").html("Following");
		$("#followers-toggle-link").removeClass("active");
		$("#following-toggle-link").addClass("active");
	}
}

/*------------------------
------NOTIFICATIONS-------
------------------------*/

function markAllAlertsRead(alertType) {
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
				
				var shareAlerts = parseInt($("#share-alert-count").val());
				var	commentAlerts = parseInt($("#comment-alert-count").val());
				var	followerAlerts = parseInt($("#follower-alert-count").val());
				var	favoriteAlerts = parseInt($("#favorite-alert-count").val());
				var totalAlerts = shareAlerts + commentAlerts + followerAlerts + favoriteAlerts;
				var siteName = GetSiteName();
				
				switch (alertType) {
					case "shares":
						shareAlerts = 0;
						break;
					case "comments":
						commentAlerts = 0;
						break;
					case "followers":
						followerAlerts = 0;
						break;
					case "favorites":
						favoriteAlerts = 0;
						break;
					case "all":
						shareAlerts = 0;
						commentAlerts = 0;
						followerAlerts = 0;
						favoriteAlerts = 0;
						break;
				}
				
				totalAlerts = shareAlerts + commentAlerts + followerAlerts + favoriteAlerts;
				
				if (totalAlerts == 0 || isNaN(totalAlerts)) {
					$("#alert-count").html("");
					document.title = "Notifications | " + $("#site-title-text").val();
				} else {
					$("#alert-count").html("(" + totalAlerts + ")");
					document.title = "Notifications | " + $("#site-title-text").val() + " (" + totalAlerts + ")";
				}
				
				switch (alertType) {
					case "shares":
						$(".notifications-list").html("<li class=\"title-item\">Shares</li><li><div class=\"notification-normal-text\">No new share alerts</div></li>");
						break;
					case "comments":
						$(".notifications-list").html("<li class=\"title-item\">New Comments</li><li><div class=\"notification-normal-text\">No new comment alerts</div></li>");
						break;
					case "followers":
						$(".notifications-list").html("<li class=\"title-item\">New Followers</li><li><div class=\"notification-normal-text\">No new follower alerts</div></li>");
						break;
					case "favorites":
						$(".notifications-list").html("<li class=\"title-item\">New Favorites</li><li><div class=\"notification-normal-text\">No new favorite alerts</div></li>");
						break;
					case "all":
						$("#all-shares-alerts").html("");
						$("#all-comments-alerts").html("");
						$("#all-followers-alerts").html("");
						$("#all-favorites-alerts").html("");
						break;
				}
				
				$(".mark-all-read").slideUp('fast');
			} else {
				// error with marking read

			}
		}
	}

	var requestString = '/php/ov-ajax.php?action=mark_all_alerts_read&alert_type=' + alertType;
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}

/*------------------------
--------REPORTING---------
------------------------*/
function submitReport(objectType, objectId)
{
	var reason = $("#report-reason").val();
	var details = $("#report-details").val();
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
				var reportResponse = $message.text();
				if (reportResponse == "OK") {
					// report went through
					$("#report-form").html("Thank you for alerting us to this.");
					$("#report-form").addClass('success-line');
				} else if (reportResponse == "REPEAT") {
					// repeat report
					$("#report-form").html("You appear to have already reported this " + objectType);
					$("#report-form").addClass('error-line');
				} else {
					// report failure
					$("#report-form").html("An Error has Occurred', 'An error has occurred, sorry but your report did not go through.");
					$("#report-form").addClass('error-line');
				}
			} else {
				$("#report-form").html($message.text());
				$("#report-form").addClass('error-line');
			}
		}
	}
	
	var requestString = "/php/ov-ajax.php?action=submit_report&reason=" + escape(reason) + "&details=" + escape(details) + "&objecttype=" + escape(objectType) + "&objectid=" + escape(objectId);
	ovAjax.open("GET", requestString, true) ;
	ovAjax.send(null) ;
}