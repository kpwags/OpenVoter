$(document).ready(function() {
	$(".modal_form").dialog({
		autoOpen : false,
		modal : true,
		resizable : false,
		width : 426
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
        $('#' + charsLeft).text(255 - len + " characters remaining");
    });
});

function GetXHTTPRequest()
{
	var requestObject;
	try
	{
		requestObject = new XMLHttpRequest() ;
	}
	catch (e)
	{
		try
		{
			requestObject = new ActiveXObject("Msxml2.XMLHTTP") ;
		}
		catch (e)
		{
			try
			{
				requestObject = new ActiveXObject("Microsoft.XMLHTTP") ;
			}
			catch (e)
			{
				return false ;
			}
		}
	}
	
	return requestObject;
}

function ShowMessage(msgTitle, msgMessage)
{
	$("#error_message_line").html(msgMessage);
	$( "#modalMessageBox" ).dialog( "option", "title", msgTitle );
	$("#modalMessageBox").dialog("open");
	return false;
}

function ConfirmAction(message)
{
	if (message == "") {
		message = "Are you sure?";
	}
	
	var answer = confirm(message);
	
	if(answer) {
		return true ;
	} else {
		return false ;
	}
}

function ConvertToUrl(str)
{
	str = str.toLowerCase();
	str = str.replace(/[^a-z0-9-]/, "-");
	str = str.replace(/-+/, "-");
	return str;
}

function GetDomain(url)
{
	return url.split(/\/+/g)[1];
}

function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

function ShowAdminSidebar(menuOption)
{
	if ($("#" + menuOption).css('display') == 'none') {
		$("#" + menuOption).slideDown('slow');
	} else {
		$("#" + menuOption).slideUp('slow');
	}
}

var isValid = true;

function OpenEditAdminForm(id, name, email, role)
{
	$("#edit_full_name").val(name);
	$("#edit_email").val(email);
	$("#edit_admin_id").val(id);
	
	if (role == "Administrator") {
		$("#edit_role_admin").attr("checked", "checked");
	}
	
	if (role == "Moderator") {
		$("#edit_role_mod").attr("checked", "checked");
	}
	
	$("#edit_admin_form").dialog("open");
}

function OpenAddAdminForm()
{
	$("#add_admin_form").dialog("open");
}

function EditAdmin()
{
	var id = $("#edit_admin_id").val();
	var fullName = $("#edit_full_name").val();
	var email = $("#edit_email").val();
	var role = 2;
	
	if ($("#edit_role_admin").is(':checked')) {
		role = 1;
	}
	
	var editRequest = GetXHTTPRequest();
	
	editRequest.onreadystatechange = function()
	{
		if(editRequest.readyState == 4)
		{
			if (editRequest.responseText != "") {
				location.reload();
			}
		}
	}

	var requestString = '/ov-admin/php/edit_admin.php?id=' + id + "&name=" + escape(fullName) + "&email=" + escape(email) + "&role=" + escape(role);
	editRequest.open("GET", requestString, true) ;
	editRequest.send(null) ;
}

function ValidateAddAdmin()
{
	isValid = true;
	
	ClearAddAdminErrors();
	
	var username = jQuery.trim($("#add_username").val());
	var fullName = jQuery.trim($("#add_full_name").val());
	var email = jQuery.trim($("#add_email").val());
	var password1 = jQuery.trim($("#add_password_1").val());
	var password2 = jQuery.trim($("#add_password_2").val());

	if (username == "") {
		isValid = false;
		$("#add_username_error").html("Required");
		$("#add_username_error").css("display", "block");
	} else {	
		var usernameRegex = new RegExp("^[a-zA-Z0-9_]{5,20}$");
		if (!usernameRegex.test(username)) {
			isValid = false;
			$("#add_username_error").html("Invalid");
			$("#add_username_error").css("display", "block");
		}
	}
	
	if (fullName == "") {
		isValid = false;
		$("#add_full_name_error").html("Required");
		$("#add_full_name_error").css("display", "block");
	}
	
	if (email == "") {
		isValid = false;
		$("#add_email_error").html("Required");
		$("#add_email_error").css("display", "block");
	} else {
		var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
		if( !emailExp.test(email)) {
			isValid = false;
			$("#add_email_error").html("Invalid");
			$("#add_email_error").css("display", "block");
		}
	}
		
	if (password1 != password2) {
		isValid = false;
		$("#add_password_1_error").html("Passwords Do Not Match");
		$("#add_password_1_error").css("display", "block");
		$("#add_password_2_error").html("Passwords Do Not Match");
		$("#add_password_2_error").css("display", "block");
	}
	
	if (password1.length < 6 || password1.length > 20) {
		isValid = false;
		$("#add_password_1_error").html("Passwords Must Be Between 6 and 20 Characters");
		$("#add_password_1_error").css("display", "block");
		$("#add_password_2_error").html("Passwords Must Be Between 6 and 20 Characters");
		$("#add_password_2_error").css("display", "block");
	}
	
	CheckEmail(email);
	CheckUsername(username);
	
	return isValid;
}

function CheckEmail(email)
{
	var checkRequest = GetXHTTPRequest();
	
	checkRequest.onreadystatechange = function()
	{
		if(checkRequest.readyState == 4)
		{
			if (checkRequest.responseText != "") {
				var response = checkRequest.responseText;
				
				if (response != "OK") {
					isValid = false;
					$("#add_email_error").html("Taken");
					$("#add_email_error").css("display", "block");
				}
			}
		}
	}

	var requestString = '/ov-admin/php/does_admin_exist.php?type=email&identifier=' + escape(email);
	checkRequest.open("GET", requestString, true) ;
	checkRequest.send(null) ;
}

function CheckUsername(username)
{
	var checkRequest = GetXHTTPRequest();
	
	checkRequest.onreadystatechange = function()
	{
		if(checkRequest.readyState == 4)
		{
			if (checkRequest.responseText != "") {
				var response = checkRequest.responseText;
				
				if (response != "OK") {
					isValid = false;
					$("#add_username_error").html("Taken");
					$("#add_username_error").css("display", "block");
				}
			}
		}
	}

	var requestString = '/ov-admin/php/does_admin_exist.php?type=username&identifier=' + escape(username);
	checkRequest.open("GET", requestString, true) ;
	checkRequest.send(null) ;
}

function AddAdmin() 
{
	ValidateAddAdmin();
	
	if (isValid) {
		var username = jQuery.trim($("#add_username").val());
		var fullName = jQuery.trim($("#add_full_name").val());
		var email = jQuery.trim($("#add_email").val());
		var password = jQuery.trim($("#add_password_1").val());
		var role = $('#role_field input:radio:checked').val();
	
		var addRequest = GetXHTTPRequest();
	
		addRequest.onreadystatechange = function()
		{
			if(addRequest.readyState == 4)
			{
				if (addRequest.responseText != "") {
					location.reload();
				}
			}
		}

		var requestString = '/ov-admin/php/add_admin.php?username=' + escape(username) + '&name=' + escape(fullName) + '&email=' + escape(email) + '&password=' + escape(password) + '&role=' + escape(role);
	
		addRequest.open("GET", requestString, true) ;
		addRequest.send(null) ;
	}
}

function ClearAddAdminErrors()
{
	$("#add_username_error").css("display", "none");
	$("#add_full_name_error").css("display", "none");
	$("#add_email_error").css("display", "none");
	$("#add_password_1_error").css("display", "none");
	$("#add_password_2_error").css("display", "none");
}

function ShowResetForm(adminId)
{
	$("#reset_admin_id").val(adminId);
	$("#reset_password_form").dialog("open");
}

function ClearResetErrors()
{
	$("#reset_password_1_error").css("display", "none");
	$("#reset_password_2_error").css("display", "none");
}

function ValidateReset()
{
	ClearResetErrors();
	
	var isValidPW = true;
	var password1 = $("#reset_password_1").val();
	var password2 = $("#reset_password_2").val();
	
	if (password1 != password2) {
		isValidPW = false;
		$("#reset_password_1_error").html("Passwords Do Not Match");
		$("#reset_password_1_error").css("display", "block");
		$("#reset_password_2_error").html("Passwords Do Not Match");
		$("#reset_password_2_error").css("display", "block");
	}
	
	if (password1.length < 6 || password1.length > 20) {
		isValidPW = false;
		$("#reset_password_1_error").html("Passwords Must Be Between 6 and 20 Characters");
		$("#reset_password_1_error").css("display", "block");
		$("#reset_password_2_error").html("Passwords Must Be Between 6 and 20 Characters");
		$("#reset_password_2_error").css("display", "block");
	}
	
	return isValidPW;
}

function ResetPassword()
{
	var adminId = $("#reset_admin_id").val();
	var password1 = $("#reset_password_1").val();
	var password2 = $("#reset_password_2").val();
	
	var resetRequest = GetXHTTPRequest();

	resetRequest.onreadystatechange = function()
	{
		if(resetRequest.readyState == 4)
		{
			if (resetRequest.responseText != "") {
				var resetResponse = resetRequest.responseText;
				$("#reset_password_form").dialog("close");
				if (resetResponse == "OK") {
					$("#error_line").css("color", "#0A0");
					$("#error_line").css("font-weight", "bold");
					$("#error_line").html("Password Reset Successfully");
					$("#error_line").slideDown('slow');
				} else {
					$("#error_line").css("color", "#F00");
					$("#error_line").css("font-weight", "bold");
					$("#error_line").html("An error has occurred");
					$("#error_line").slideDown('slow');
				}
			}
		}
	}

	var requestString = '/ov-admin/php/reset_password.php?id=' + escape(adminId) + '&pw1=' + escape(password1) + '&pw2=' + escape(password2);

	resetRequest.open("GET", requestString, true) ;
	resetRequest.send(null) ;
}

function OpenAddBannedDomainForm()
{
	$("#add_banned_domain").dialog("open");
}

function OpenBanDomainFormWithUrl(url)
{
	var domain = GetDomain(url);
	$("#domain_name").val(domain);
	$("#add_banned_domain").dialog("open");
}

function AddBannedDomain()
{
	var domainName = jQuery.trim($("#domain_name").val());
	var reason = jQuery.trim($("#ban_domain_reason").val());

	var addRequest = GetXHTTPRequest();

	addRequest.onreadystatechange = function()
	{
		if(addRequest.readyState == 4)
		{
			if (addRequest.responseText != "") {
				location.reload();
			}
		}
	}

	var requestString = '/ov-admin/php/add_banned_domain.php?domain_name=' + escape(domainName) + '&reason=' + escape(reason);
	addRequest.open("GET", requestString, true) ;
	addRequest.send(null) ;
}

function OpenAddRestrictedDomainForm()
{
	$("#add_restricted_domain").dialog("open");
}

function AddRestrictedDomain()
{
	var domainName = jQuery.trim($("#restricted_domain_name").val());
	var reason = jQuery.trim($("#restrict_domain_reason").val());

	var addRequest = GetXHTTPRequest();

	addRequest.onreadystatechange = function()
	{
		if(addRequest.readyState == 4)
		{
			if (addRequest.responseText != "") {
				location.reload();
			}
		}
	}
	
	var requestString = '/ov-admin/php/add_restricted_domain.php?domain_name=' + escape(domainName) + '&reason=' + escape(reason);
	addRequest.open("GET", requestString, true) ;
	addRequest.send(null) ;
}

function OpenAddBannedIPForm()
{
	$("#add_banned_ip").dialog("open");
}

function AddBannedIP()
{
	var ipAddress = jQuery.trim($("#ip_address_ban").val());

	var addRequest = GetXHTTPRequest();

	addRequest.onreadystatechange = function()
	{
		if(addRequest.readyState == 4)
		{
			if (addRequest.responseText != "") {
				location.reload();
			}
		}
	}
	
	var requestString = '/ov-admin/php/ban_ip_address.php?ip=' + escape(ipAddress);
	addRequest.open("GET", requestString, true) ;
	addRequest.send(null) ;
}

var isCategoryValid = true;

function OpenAddParentCategoryForm()
{
	$("#parent_category_id").val('');
	$("#add_category_form").dialog("open");
}

function OpenAddChildCategoryForm(parentCategoryId)
{
	$("#parent_category_id").val(parentCategoryId);
	$("#add_category_form").dialog("open");
}

function OpenEditCategoryForm(categoryId, categoryName, urlName, sortOrder)
{
	$("#edit_category_name").val(categoryName);
	$("#edit_category_url_name").val(urlName);
	$("#edit_category_id").val(categoryId);
	$("#edit_category_sort_order").val(sortOrder);
	$("#edit_category_form").dialog("open");
}

function CheckUrl(urlName)
{
	var checkRequest = GetXHTTPRequest();
	
	checkRequest.onreadystatechange = function()
	{
		if(checkRequest.readyState == 4)
		{
			if (checkRequest.responseText != "") {
				var response = checkRequest.responseText;
				
				if (response != "OK") {
					HandleCategoryExists();
					return false;
				}
			}
		}
	}

	var requestString = '/ov-admin/php/is_url_available.php?url_name=' + escape(urlName);
	checkRequest.open("GET", requestString, true) ;
	checkRequest.send(null) ;
}

function HandleCategoryExists()
{
	$("#category_url_name_error").html("Taken");
	$("#category_url_name_error").css("display", "block");
	isCategoryValid = false;
}

function ValidateCategory()
{
	var categoryName = jQuery.trim($("#category_name").val());
	var categoryUrlName = jQuery.trim($("#category_url_name").val());

	isCategoryValid = true;
	ClearAddCategoryErrors();
	
	if (categoryName == "") {
		isCategoryValid = false;
		$("#category_url_name_error").html("Required");
		$("#category_url_name_error").css("display", "block");
	}
	
	if (categoryUrlName == "") {
		isCategoryValid = false;
		$("#category_name_error").html("Required");
		$("#category_name_error").css("display", "block");
	}
	
	CheckUrl(categoryUrlName);
	
	return isCategoryValid;
}

function AddCategory()
{
	var categoryName = jQuery.trim($("#category_name").val());
	var categoryUrlName = jQuery.trim($("#category_url_name").val());
	var parentCategoryId = $("#parent_category_id").val();
	var sortOrder = $("#category_sort_order").val();
	
	var addRequest = GetXHTTPRequest();

	addRequest.onreadystatechange = function()
	{
		if(addRequest.readyState == 4)
		{
			if (addRequest.responseText != "") {
				location.reload();
			}
		}
	}

	var requestString = '/ov-admin/php/add_category.php?name=' + escape(categoryName) + '&url_name=' + escape(categoryUrlName) + '&parent_id=' + parentCategoryId + '&sort=' + escape(sortOrder);
	addRequest.open("GET", requestString, true);
	addRequest.send(null);
}

function EditCategory()
{
	var categoryName = jQuery.trim($("#edit_category_name").val());
	var categoryUrlName = jQuery.trim($("#edit_category_url_name").val());
	var sortOrder = $("#edit_category_sort_order").val();
	var categoryId = $("#edit_category_id").val();
	
	var addRequest = GetXHTTPRequest();

	addRequest.onreadystatechange = function()
	{
		if(addRequest.readyState == 4)
		{
			if (addRequest.responseText != "") {
				location.reload();
			}
		}
	}

	var requestString = '/ov-admin/php/edit_category.php?id=' + categoryId + '&name=' + escape(categoryName) + '&url_name=' + escape(categoryUrlName) + '&sort=' + escape(sortOrder);
	addRequest.open("GET", requestString, true);
	addRequest.send(null);
}

function ClearAddCategoryErrors()
{
	$("#category_name_error").css("display", "none");
	$("#category_url_name_error").css("display", "none");
}

function OpenEditSubmissionForm(id, title, summary, url)
{
	$("#edit_title").val(title);
	$("#edit_summary").val(summary);
	$("#edit_submission_url").val(url);
	$("#edit_submission_id").val(id);
	$("#edit_submission_form").dialog("open");
}

function OpenBanUserForm(userId, username)
{
	$("#ban_username").html(username);
	$("#ban_user_id").val(userId);
	$("#ban_user_form").dialog("open");
}

function toggleHeaderImage(chkbx)
{
	if (chkbx.checked) {
		$("#header_image_field").slideDown('slow');
	} else {
		$("#header_image_field").slideUp('slow');
	}
}

function toggleKarmaSettings(chkbx)
{
	if (chkbx.checked) {
		$("#karma_settings").slideDown('slow');
	} else {
		$("#karma_settings").slideUp('slow');
	}
}

function toggleCaptchaSettings(chkbx)
{
	if (chkbx.checked) {
		$("#captcha_settings").slideDown('slow');
	} else {
		$("#captcha_settings").slideUp('slow');
	}
}

function toggleAlgorithmSettings()
{
	if ($("#algorithm").val() == "static") {
		$("#threshold_field").slideDown('slow');
	} else {
		$("#threshold_field").slideUp('slow');
	}
}

function toggleKarmaPenaltySettings(chkbx)
{
	if (chkbx.checked) {
		$("#karma_penalty_settings").slideDown('slow');
	} else {
		$("#karma_penalty_settings").slideUp('slow');
	}
}

function toggleKarmaThreshold1Settings()
{
	if ($("#karma_penalty_1_threshold").val() != 999) {
		$("#karma_penalty_1").slideDown('slow');
	} else {
		$("#karma_penalty_1").slideUp('slow');
	}
}

function toggleKarmaThreshold2Settings()
{
	if ($("#karma_penalty_2_threshold").val() != 999) {
		$("#karma_penalty_2").slideDown('slow');
	} else {
		$("#karma_penalty_2").slideUp('slow');
	}
}

function OpenResetPasswordForm(userId, username) {
	$("#reset_pw_user").html(username);
	$("#reset_pw_user_id").val(userId);
	$("#reset_password_form").dialog("open");
}

function ValidateResetUserPassword() {
	$("#pw1_error").css("display", "none");
	$("#pw2_error").css("display", "none");
	
	var password1 = $('#reset_password_1').val();
	var password2 = $('#reset_password_2').val();
	
	var bValidates = true;
	
	if (jQuery.trim(password1) == "") {
		bValidates = false;
		$("#pw1_error").html("You must enter a password.");
		$("#pw1_error").css("display", "block");
	}
	
	if (jQuery.trim(password2) ==  "") {
		bValidates = false;
		$("#pw2_error").html("You must enter a password.");
		$("#pw2_error").css("display", "block");
	}
	
	if (password1 != password2) {
		bValidates = false;
		$("#pw1_error").html("Passwords do not match.");
		$("#pw2_error").html("Passwords do not match.");
		$("#pw1_error").css("display", "block");
		$("#pw2_error").css("display", "block");
	}
	
	if (password1.length < 6 || password1.length > 20) {
		bValidates = false;
		$("#pw1_error").html("Passwords must be between 6 and 20 characters.");
		$("#pw2_error").html("Passwords must be between 6 and 20 characters.");
		$("#pw1_error").css("display", "block");
		$("#pw2_error").css("display", "block");
	}
	
	return bValidates;
}


