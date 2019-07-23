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

function ParseXML(xmlString) {
	var xmlObject = $.parseXML(xmlString);
	return xmlObject;
}

function ValidateEmail(email)
{
	var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
	if( !email.match(emailExp) || email == "" )	{
		return false;
	} else {
		return true;
	}
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

(function($)
{
    jQuery.fn.putCursorAtEnd = function()
    {
        return this.each(function()
        {
            $(this).focus()

            // If this function exists...
            if (this.setSelectionRange)
            {
                // ... then use it
                // (Doesn't work in IE)

                // Double the length because Opera is inconsistent about whether a carriage return is one character or two. Sigh.
                var len = $(this).val().length * 2;
                this.setSelectionRange(len, len);
            }
            else
            {
                // ... otherwise replace the contents with itself
                // (Doesn't work in Google Chrome)
                $(this).val($(this).val());
            }

            // Scroll to the bottom, in case we're in a tall textarea
            // (Necessary for Firefox and Google Chrome)
            this.scrollTop = 999999;
        });
    };
})(jQuery);

function GetAlertCount(alertCount) {
	alertCount = alertCount.replace("(", "");
	alertCount = alertCount.replace(")", "");
	alertCount = alertCount.replace(" ", "");
	if (alertCount == "") {
		alertCount = 0;
	} else {
		alertCount = parseInt(alertCount);
	}
	
	return alertCount;
}

function GetSiteName() {
	var pageTitle = document.title;
	var titleArray = pageTitle.split("|");
	pageTitle = titleArray[1];
	pageTitle = pageTitle.substr(1);
	return pageTitle;
}

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}
