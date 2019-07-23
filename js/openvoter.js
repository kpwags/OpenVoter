var isCategoryBarVisible = false;

/* ---------
 * DOCUMENT READY
 * --------- */

$(document).ready(function() {
	//$("input:submit, a.button, button").button();
	
	$(".modal_form").dialog({
		autoOpen : false,
		modal : true,
		resizable : false,
		width : 426,
		draggable : false
	});
	
	$(".votes_view").dialog({
		autoOpen : false,
		modal : true,
		resizable : false,
		width : 600,
		draggable : false
	});
	
	$("#modalMessageBox").dialog({
		autoOpen	:	false,
		draggable	:	false,
		modal		:	true,
		resizable	:	false
	});
	
	$('.limit255').keyup(function() {
	    var len = this.value.length;
		var charsLeft = this.getAttribute('charsleft');
        if (len >= 255) {
            this.value = this.value.substring(0, 255);
        }

		var charsCountLeft = 255 - len;
		if (charsCountLeft < 0) {
			charsCountLeft = 0;
		}

        $('#' + charsLeft).text(charsCountLeft + " characters remaining");
    });

	$('.limit500').keyup(function() {
	    var len = this.value.length;
		var charsLeft = this.getAttribute('charsleft');
        if (len >= 500) {
            this.value = this.value.substring(0, 500);
        }

		var charsCountLeft = 500 - len;
		if (charsCountLeft < 0) {
			charsCountLeft = 0;
		}

        $('#' + charsLeft).text(charsCountLeft + " characters remaining");
    });

	$("a#user_avatar_img").fancybox({ titlePosition: 'over' });
	
	$(".qtooltip").qtip({
		content: {
			attr: 'title'
		},
		style: {
			classes: 'ui-tooltip-rounded ui-tooltip-dark'
		},
		position: {
			my: 'bottom center',
			at: 'top center'
		}
	})
	
	$(".qtooltip-bottom").qtip({
		content: {
			attr: 'title'
		},
		style: {
			classes: 'ui-tooltip-rounded ui-tooltip-dark'
		},
		position: {
			my: 'top center',
			at: 'bottom center'
		}
	})
	
	$(".qtooltip-left").qtip({
		content: {
			attr: 'title'
		},
		style: {
			classes: 'ui-tooltip-rounded ui-tooltip-dark'
		},
		position: {
			my: 'right center',
			at: 'left center'
		}
	});

	$(".qtooltip-right").qtip({
		content: {
			attr: 'title'
		},
		style: {
			classes: 'ui-tooltip-rounded ui-tooltip-dark'
		},
		position: {
			my: 'left center',
			at: 'right center'
		}
	});

	$('.user-dropdown, #user-bar ul.main-list li.user-item').mouseleave(function() {
		$('.user-dropdown').slideUp('fast');
	});

	$('#user-bar ul.main-list li.user-item').mouseenter(function() {
		$('.user-dropdown').slideDown('fast');
	});

	$('.lists-dropdown, #user-bar ul.main-list li.lists-item').mouseleave(function() {
		$('.lists-dropdown').slideUp('fast');
	});

	$('#user-bar ul.main-list li.lists-item').mouseenter(function() {
		$('.lists-dropdown').slideDown('fast');
	});

	$('.user-menu-dropdown').mouseleave(function() {
		$('.user-menu-dropdown').slideUp('fast');
	});

	$('.user-menu-button').mouseenter(function() {
		$('.user-menu-dropdown').slideDown('fast');
	});

	$('a.user-is-following').mouseover(function() {
		$('a.user-is-following').text("Unfollow");
	});

	$('a.user-is-following').mouseout(function() {
		$('a.user-is-following').text("Following");
	});

	// user box
	$('.user-box-link').mouseenter(function() {
		var boxId = $(this).attr('box-id');
		$('#' + boxId).fadeIn('fast');
	});
	
	$('.user-box-link').mouseleave(function() {
		$('.user-box').fadeOut('fast');
	});

	$('.fancybox-form-link').fancybox({
		showCloseButton: false
	});

	$("#hidden-sidebar-button").click( function() {
        if (isCategoryBarVisible) {
        	$("#slider .slider-content").hide();
            $("#slider, #hidden-sidebar").animate({ 
                width: "0px"
                }, 500 );
			
			$("#hidden-sidebar-button").animate({ left: 0 });
			
			var bgimage = $('#hidden-sidebar-button').css('background-image');
			bgimage = bgimage.replace('hide', 'view');
			$('#hidden-sidebar-button').css('background-image', bgimage);

            isCategoryBarVisible = false;
        } else {
        	$("#slider .slider-content").show();
            $("#slider, #hidden-sidebar").animate({ 
                width: "150px"
                }, 500 );
			$("#hidden-sidebar-button").animate({ left: 150 });

            var bgimage = $('#hidden-sidebar-button').css('background-image');
            bgimage = bgimage.replace('view', 'hide');
            $('#hidden-sidebar-button').css('background-image', bgimage);

            isCategoryBarVisible = true;
        }
    });  

    $('input[type="text"], input[type="email"], input[type="password"], textarea').placeholder();

    $('#reportForm').validate({
		rules: {
			reportReason: {
				required: true
			}
		},
		messages: {
			reportReason: {
				required: "You must enter a reason for the report",
			}
		}
	});
});

/* ---------
 * USER BAR
 * --------- */

 function showUserDropdown() {
 	$('.user-dropdown').slideDown('fast');
 }

 function showListsDropdown() {
 	$('.lists-dropdown').slideDown('fast');
 }

/* ---------
 * FORGOT PASSWORD PAGE
 * --------- */

function ValidateResetPassword()
{
	$("#password-1-error").css("display", "none");
	$("#password-2-error").css("display", "none");
	$("#password-1").attr("style", "border-color:#3a3ad2");
	$("#password-2").attr("style", "border-color:#3a3ad2");
	
	var password1 = $('#password-1').val();
	var password2 = $('#password-2').val();
	
	var bValidates = true;
	
	if (jQuery.trim(password1) == "") {
		bValidates = false;
		$("#password-1-error").html("You must enter a password.");
		$("#password-1-error").css("display", "block");
		$("#password-1").attr("style", "border-color:#ff4444");
	}
	
	if (jQuery.trim(password2) ==  "") {
		bValidates = false;
		$("#password-2-error").html("You must enter a password.");
		$("#password-2-error").css("display", "block");
		$("#password-2").attr("style", "border-color:#ff4444");
	}
	
	if (password1 != password2) {
		bValidates = false;
		$("#password-1-error").html("Passwords do not match.");
		$("#password-1-error").css("display", "block");
		$("#password-1").attr("style", "border-color:#ff4444");
		$("#password-2-error").html("Passwords do not match.");
		$("#password-2-error").css("display", "block");
		$("#password-2").attr("style", "border-color:#ff4444");
	}
	
	if (password1.length < 6 || password1.length > 20) {
		bValidates = false;
		$("#password-1-error").html("Passwords must be between 6 and 20 characters.");
		$("#password-1-error").css("display", "block");
		$("#password-1").attr("style", "border-color:#ff4444");
		$("#password-2-error").html("Passwords must be between 6 and 20 characters.");
		$("#password-2-error").css("display", "block");
		$("#password-2").attr("style", "border-color:#ff4444");
	}

	return bValidates;
}

/* ---------
 * ACCOUNT PAGE
 * --------- */

function toggleUserBioTextArea(action) {
	if (action == "collapse") {
		// collapse
		$('#details').animate({ height: 40 }, 1500);
	} else {
		// expand
		$('#details').animate({ height: 170 }, 1500);
	}
}

/* ---------
 * REGISTRATION PAGE
 * --------- */

function ValidateRegistration() {
	$("#answer_error").css("display", "none");
	$("#password1_error").css("display", "none");
	$("#password2_error").css("display", "none");
	$("#username_error").css("display", "none");
	$("#email_error").css("display", "none");
	$("#tou_error").css("display", "none");
	
	var 
	email = jQuery.trim($('#email').val()),
	username = jQuery.trim($('#username').val()),
	password1 = $('#password1').val(),
	password2 = $('#password2').val(),
	answer = $('#securityanswer').val(),
	bValidates = true,
	usernameRegex = new RegExp("^[a-zA-Z0-9_]{5,20}$"),
	emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
	
	
	if (jQuery.trim(answer) == "") {
		bValidates = false;
		$("#answer_error").html("You must choose a security question and provide an answer");
		$("#answer_error").css("display", "block");
		$("#securityquestion").attr("style", "border-color:#ff4444");
		$("#securityanswer").attr("style", "border-color:#ff4444");
	}
	
	if (password1.length < 6 || password1.length > 20) {
		bValidates = false;
		$("#password1_error").html("Passwords must be between 6 and 20 characters");
		$("#password1_error").css("display", "block");
		$("#password1").attr("style", "border-color:#ff4444");
		$("#password2_error").html("Passwords must be between 6 and 20 characters");
		$("#password2_error").css("display", "block");
		$("#password2").attr("style", "border-color:#ff4444");
	}
	
	if (jQuery.trim(password1) == "") {
		bValidates = false;
		$("#password1_error").html("You need to enter a password");
		$("#password1_error").css("display", "block");
		$("#password1").attr("style", "border-color:#ff4444");
	}
	
	if (jQuery.trim(password2) ==  "") {
		bValidates = false;
		$("#password2_error").html("You need to enter a password");
		$("#password2_error").css("display", "block");
		$("#password2").attr("style", "border-color:#ff4444");
	}
	
	if (password1 != password2) {
		bValidates = false;
		$("#password1_error").html("Passwords do not match");
		$("#password1_error").css("display", "block");
		$("#password1").attr("style", "border-color:#ff4444");
		$("#password2_error").html("Passwords do not match");
		$("#password2_error").css("display", "block");
		$("#password2").attr("style", "border-color:#ff4444");
	}

	if (!usernameRegex.test(username)) {
		bValidates = false ;
		$("#username_ok").css("display", "none");
		$("#username_not_ok").css("display", "inline");
		$("#username_error").html("Invalid Username, Should Be Alpha-Numeric and be 5-20 characters.");
		$("#username_error").css("display", "block");
		$("#username").attr("style", "border-color:#ff4444");
	}
	
	if(!emailExp.test(email))	{
		bValidates = false ;
		$("#email_ok").css("display", "none");
		$("#email_not_ok").css("display", "inline");
		$("#email_error").html("Invalid Email Address.");
		$("#email_error").css("display", "block");
		$("#email").attr("style", "border-color:#ff4444");
	}
	
	if (!$("#agreetou").is(':checked')) {
		bValidates = false ;
		$("#tou_error").html("You must agree to our Terms of Use to register");
		$("#tou_error").css("display", "block");
		$("#tou").attr("style", "border-color:#ff4444");
	}
	
	return bValidates;
}

function CheckPassword()
{
	var 
	password1 = $('#password1').val(),
	password2 = $('#password2').val(),
	bValidates = true;
	
	if (password1.length < 6 || password1.length > 20) {
		$("#password1_error").html("Passwords must be between 6 and 20 characters");
		$("#password1_error").css("display", "block");
		$("#password1").attr("style", "border-color:#ff4444");
		$("#password2_error").html("Passwords must be between 6 and 20 characters");
		$("#password2_error").css("display", "block");
		$("#password2").attr("style", "border-color:#ff4444");
		bValidates = false;
	}
	
	if (jQuery.trim(password1) == "") {
		$("#password1_error").html("You need to enter a password");
		$("#password1_error").css("display", "block");
		$("#password1").attr("style", "border-color:#ff4444");
		bValidates = false;
	}
	
	if (jQuery.trim(password2) ==  "") {
		$("#password2_error").html("You need to enter a password");
		$("#password2_error").css("display", "block");
		$("#password2").attr("style", "border-color:#ff4444");
		bValidates = false;
	}
	
	if (password1 != password2) {
		$("#password1_error").html("Passwords do not match");
		$("#password1_error").css("display", "block");
		$("#password1").attr("style", "border-color:#ff4444");
		$("#password2_error").html("Passwords do not match");
		$("#password2_error").css("display", "block");
		$("#password2").attr("style", "border-color:#ff4444");
		bValidates = false;
	}
	
	if (bValidates) {
		$("#password1_error").css("display", "none");
		$("#password2_error").css("display", "none");
		$("#password1").attr("style", "border-color:#3a3ad2");
		$("#password2").attr("style", "border-color:#3a3ad2");
	}
}

function CheckSecurityQuestion()
{
	var 
	secQuestion = $("#securityquestion").val(),
	secAnswer = $("#securityanswer").val();
	
	if (jQuery.trim(secQuestion) == "" || jQuery.trim(secAnswer) == "") {
		$("#answer_error").html("You must choose a security question and provide an answer");
		$("#answer_error").css("display", "block");
		$("#securityquestion").attr("style", "border-color:#ff4444");
		$("#securityanswer").attr("style", "border-color:#ff4444");
	} else {
		$("#answer_error").css("display", "none");
		$("#securityquestion").attr("style", "border-color:#3a3ad2");
		$("#securityanswer").attr("style", "border-color:#3a3ad2");
	}
}


/* ---------
 * MODAL FORM SHOW
 * --------- */
function ShowEmailForm(emailFormId)
{
	$("#" + emailFormId).dialog("open");
}

function ShowReportForm(objectType, objectId) {
	$('#report-object-type').val(objectType);
	$('#report-object-id').val(objectId);
	$('#report-object-type-header').html(objectType.capitalize());
}

function ShowShareForm(submissionId)
{
	$('#share-submission-id').val(submissionId);
}

function ShowEditForm()
{
	$("#edit_submission_form").dialog("open");
}

function ShowAddLocationForm()
{
	$("#add_location_form").dialog("open");
}

function ShowAddThumbnailForm()
{
	$("#add_submission_thumbnail_form").dialog("open");
}

function ShowVotes()
{
	$("#votes_div").dialog("open");
}

function ShowDeleteAccountPasswdConfirm()
{
	$("#confirm_password_form").dialog("open");
}

function ShowAddToListForm(username) {
	$('#add-to-list-username').html(username);
}

function closePopup() {
	$.fancybox.close();
}

function ToggleAddNewListForm() {
	if ($('#new-list-form').css('display') == "none") {
		$('#new-list-form').slideDown('fast');
	} else {
		$('#new-list-form').slideUp('fast');
	}
}

/* ---------
 * LINK SUBMISSION PAGE
 * --------- */

function SwitchType(type)
{
	switch(type)
	{
		case 'photo':
			$("#type").val('photo');
			$("#story_type").attr("src", "/img/submission_type/story.png");
			$("#photo_type").attr("src", "/img/submission_type/photo_selected.png");
			$("#video_type").attr("src", "/img/submission_type/video.png");
			$("#podcast_type").attr("src", "/img/submission_type/podcast.png");
			break;
		case 'video':
			$("#type").val('video');
			$("#story_type").attr("src", "/img/submission_type/story.png");
			$("#photo_type").attr("src", "/img/submission_type/photo.png");
			$("#video_type").attr("src", "/img/submission_type/video_selected.png");
			$("#podcast_type").attr("src", "/img/submission_type/podcast.png");
			break;
		case 'podcast':
			$("#type").val('podcast');
			$("#story_type").attr("src", "/img/submission_type/story.png");
			$("#photo_type").attr("src", "/img/submission_type/photo.png");
			$("#video_type").attr("src", "/img/submission_type/video.png");
			$("#podcast_type").attr("src", "/img/submission_type/podcast_selected.png");
			break;
		case 'self':
			$("#submit-step-1").fadeOut();
			$("#submit-step-2").fadeIn();
			$("#thumbnail_chooser").hide(0);
			$("#submission-type").val('self');
			var charsLeft = $("#summary").attr('charsleft');
	        $('#' + charsLeft).hide(0);
			CancelSummaryLimit();
			break;
		case 'story':
		default:
			$("#type").val('story');
			$("#story_type").attr("src", "/img/submission_type/story_selected.png");
			$("#photo_type").attr("src", "/img/submission_type/photo.png");
			$("#video_type").attr("src", "/img/submission_type/video.png");
			$("#podcast_type").attr("src", "/img/submission_type/podcast.png");
			break;
	}
}

function CancelSummaryLimit() {
	$('#summary').off('keyup');
}

function ToggleSubcategories(divId, aId)
{
	if (document.getElementById(divId).style.display == 'none') {
		// hidden, now show
		$("#" + divId).slideDown('slow');
		$("#" + aId).html("(View Less)");
	} else {
		$("#" + divId).slideUp('slow');
		$("#" + aId).html("(View More)");
	}
}

function ChooseCategory(checkbox)
{
	var objCategory = document.getElementsByName('category[]') ;
	var nCategories = 0 ;
	for(var i = 0; i < objCategory.length; i++)
	{
		if( objCategory[i].checked == true )
		{
			nCategories++ ;
		}
	}
	
	if (nCategories > 6) {
		checkbox.checked = false;
	}
}

function ChooseImage(img)
{
	$(".submission-image-choice").css('background', '#fff');
	$("#" + img.id).css('background', '#00f');
	
	$("#thumbnail_url").val(img.src);
}

function ClearThumbnail() {
	$(".submission-image-choice").css('background', '#fff');
	
	$("#thumbnail_url").val('');
}

function submitTo(section) {
	$("#title_error").css("display", "none");
	$("#title").attr("style", "border-color:#dedede");
	$("#tag_error").css("display", "none");
	$("#tags").attr("style", "border-color:#dedede");


	var validates = true;
	var url = $("#submission-url").val();
	var type = $("#submission-type").val();
	var title = $("#title").val();
	var tags = $("#tags").val();

	if (jQuery.trim(url) == "" || jQuery.trim(type) == "") {
		window.location.href = "/submit";
	}
	
	if (jQuery.trim(title) == "") {
		$("#title_error").html("You must give your submission a title");
		$("#title_error").css("display", "block");
		$("#title").attr("style", "border-color:#ff4444");
		validates = false;
	}

	if (jQuery.trim(tags) == "") {
		$("#tag_error").html("You must give your submission at least one tag");
		$("#tag_error").css("display", "block");
		$("#tags").attr("style", "border-color:#ff4444");
		validates = false;
	}

	if (validates) {
		$('#submit-to').val(section);

		switch(section) {
			case 'site':
				$("#submit-step-2").fadeOut('fast');
				$("#submit-step-3-site").fadeIn('fast');
				break;
			case 'group':
				$("#submit-step-2").fadeOut('fast');
				$("#submit-step-3-group").fadeIn('fast');
				break;
		}
	}
}

/* ---------
 * SUBMISSION PAGE
 * --------- */

 function ConfirmDeleteSubmission() {
	noty({
		text: 'Are you sure you want to delete your submission?',
		buttons: [
			{type: 'normal-button', text: 'Ok', click: function() { DeleteSubmission(); } },
			{type: 'cancel-button', text: 'Cancel', click: function() { /**/ } }
		],
		closable: false,
		timeout: false
	});
}

function toggleCommentTextArea(action) {
	if (action == "collapse") {
		// collapse
		$('#post-comment-body').animate({ height: 40 }, 1500);
	} else {
		// expand
		$('#post-comment-body').animate({ height: 170 }, 1500);
	}
}

var editCommentHtml = "<textarea class=\"edit-comment\" id=\"edit-comment-body\">%COMMENTBODY%</textarea><div style=\"margin-top:10px;text-align:right\"><button style=\"margin-right:5px\" class=\"cancel-button\" onclick=\"CancelEditComment()\">Cancel</button><button class=\"normal-button\" onclick=\"EditComment()\">Save Changes</button></div>";

function DisplayEditComment(commentId) {
	var commentBody = $('#comment-body-' + commentId).html();

	// assign body to temp textarea as placeholder
	$('#edit-comment-temp-body').val(commentBody);
	commentBody = $('#comment-body-' + commentId).text();

	commentBody = commentBody.replace(new RegExp("<br />", "g"), "");

	var editHtml = editCommentHtml.replace('%COMMENTBODY%', commentBody);
	$('#comment-body-' + commentId).html(editHtml);

	$('#comment-' + commentId + ' div.actions').hide();

	$('#comment-to-edit-id').val(commentId);
}

function CancelEditComment() {
	var commentBody = $('#edit-comment-temp-body').val(),
		commentId = $('#comment-to-edit-id').val();

	$('#comment-body-' + commentId).html(commentBody);
	$('#comment-' + commentId + ' div.actions').show();
}

function ConfirmDeleteComment(commentId) {
	noty({
		text: 'Are you sure you want to delete your comment?',
		buttons: [
			{type: 'normal-button', text: 'Ok', click: function() { DeleteComment(commentId); } },
			{type: 'cancel-button', text: 'Cancel', click: function() { /**/ } }
		],
		closable: false,
		timeout: false
	});
}

function launchReplyForm(commentId, replyToId) {
	var user = $('#comment-user-' + commentId).text(),
		body = $('#comment-body-' + commentId).html();

	var prepopulateReply = false;
	if ($('#prepopulate_reply').val() == "yes") {
		prepopulateReply = true;
	}
	
	if (prepopulateReply) {
		$('#reply-text').val("@" + user + " ");
	}
	
	$('#reply-text').focus();  

	$('#reply-body').html(body);
	$('#reply-user').text(user);
	$('#reply-id').val(replyToId);

	$('#reply-text').putCursorAtEnd();
}

/* ---------
 * USER PAGE
 * --------- */

function ToggleFriendsList(pageToGoTo) {
	if (pageToGoTo == "following") {
		$("#userFollowers").slideUp();
		$("#userFollowing").slideDown();
		$("#menuFollowing").addClass("active");
		$("#menuFollowers").removeClass("active");
	} else {
		$("#userFollowing").slideUp();
		$("#userFollowers").slideDown();
		$("#menuFollowing").removeClass("active");
		$("#menuFollowers").addClass("active");
	}
}

/* ---------
 * FORCE PASSWORD RESET PAGE
 * --------- */
function ValidateResetUserPassword() {
	
	$("#password1_error").css("display", "none");
	$("#password2_error").css("display", "none");
	$("#password1").attr("style", "border-color:#3a3ad2");
	$("#password2").attr("style", "border-color:#3a3ad2");
	$("#answer_error").css("display", "none");
	$("#securityquestion").attr("style", "border-color:#3a3ad2");
	$("#securityanswer").attr("style", "border-color:#3a3ad2");
	
	var 
	password1 = $('#password1').val(),
	password2 = $('#password2').val(),
	secQuestion = $("#securityquestion").val(),
	secAnswer = $("#securityanswer").val(),
	bValidates = true;
	
	if (password1.length < 6 || password1.length > 20) {
		$("#password1_error").html("Passwords must be between 6 and 20 characters");
		$("#password1_error").css("display", "block");
		$("#password1").attr("style", "border-color:#ff4444");
		$("#password2_error").html("Passwords must be between 6 and 20 characters");
		$("#password2_error").css("display", "block");
		$("#password2").attr("style", "border-color:#ff4444");
		bValidates = false;
	}
	
	if (jQuery.trim(password1) == "") {
		$("#password1_error").html("You need to enter a password");
		$("#password1_error").css("display", "block");
		$("#password1").attr("style", "border-color:#ff4444");
		bValidates = false;
	}
	
	if (jQuery.trim(password2) ==  "") {
		$("#password2_error").html("You need to enter a password");
		$("#password2_error").css("display", "block");
		$("#password2").attr("style", "border-color:#ff4444");
		bValidates = false;
	}
	
	if (password1 != password2) {
		$("#password1_error").html("Passwords do not match");
		$("#password1_error").css("display", "block");
		$("#password1").attr("style", "border-color:#ff4444");
		$("#password2_error").html("Passwords do not match");
		$("#password2_error").css("display", "block");
		$("#password2").attr("style", "border-color:#ff4444");
		bValidates = false;
	}
	
	if (jQuery.trim(secQuestion) == "" || jQuery.trim(secAnswer) == "") {
		$("#answer_error").html("You must choose a security question and provide an answer");
		$("#answer_error").css("display", "block");
		$("#securityquestion").attr("style", "border-color:#ff4444");
		$("#securityanswer").attr("style", "border-color:#ff4444");
		bValidates = false;
	} 
	
	return bValidates;
}

/* ---------
 * MANAGE LISTS PAGE
 * --------- */

function viewListDetails(listId) {
	$('.list-detail').hide();
	$('#list-details-' + listId).fadeIn('fast');
}

function launchEditForm(listId, listName, listPrivate) {
	$('#edit-list-name').val(listName);
	$('#edit-list-id').val(listId);
	$('#edit-list-private').attr('checked', listPrivate);
}

function launchDeleteListForm(listId, listName) {
	$('#list-delete-name').html(listName);
	$('#list-delete-id').val(listId);
}
