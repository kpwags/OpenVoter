<?php
if (!$ovAdminSecurity->IsAdminLoggedIn()) {
	header("Location: /ov-admin/login");
	exit();
}

$new_feedback = $ovAdminContent->GetUnreadFeedbackCount();
$new_reports = $ovAdminReporting->GetReportCount();

$current_section = "";
$current_page = "";

include(get_admin_head());
?>

</head>
<body>
	<div id="container">
		<?php include(get_admin_header()); ?>

	</div>
</body>
</html>