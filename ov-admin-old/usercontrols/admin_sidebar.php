<?php
	/*
		Copyright 2008-2010 OpenVoter
		
		This file is part of OpenVoter.
	
		OpenVoter is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, version 3.
	
		OpenVoter is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.
	
		You should have received a copy of the GNU General Public License
		along with OpenVoter.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	$report_count = $ovAdminReporting->GetReportCount();
	$submission_report_count = $ovAdminReporting->GetReportCount('submission');
	$comment_report_count = $ovAdminReporting->GetReportCount('comment');
	$user_report_count = $ovAdminReporting->GetReportCount('user');
?>


<?php if ($ovAdminSecurity->CanAccessContent()) { ?>
	<div class="sidebar_item">
		<img src="/ov-admin/img/icons/content.png" alt="" class="sidebar_image" />
		<a href="javascript:ShowAdminSidebar('sub_content')" title="Content">Manage Content</a>
	</div>
	<div class="admin_sidebar_submenu" id="sub_content" <?php if ($current_section == "content") { echo "style=\"display:block\""; } ?>>
		<div><a href="/ov-admin/content?type=submission" title="Submissions" <?php if ($current_section == "content" && $current_page == "submission") { echo "class=\"bold\""; } ?>>Submissions</a></div>
		<div><a href="/ov-admin/content?type=comment" title="Comments" <?php if ($current_section == "content" && $current_page == "comment") { echo "class=\"bold\""; } ?>>Comments</a></div>
		<div><a href="/ov-admin/content?type=user" title="Users" <?php if ($current_section == "content" && $current_page == "user") { echo "class=\"bold\""; } ?>>Users</a></div>
		<div><a href="/ov-admin/content?type=suspended_user" title="Suspended Users" <?php if ($current_section == "content" && $current_page == "suspended_user") { echo "class=\"bold\""; } ?>>Suspended Users</a></div>
		<div><a href="/ov-admin/voting-record" title="Voting Records"<?php if ($current_section == "content" && $current_page == "voting") { echo "class=\"bold\""; } ?>>Voting Records</a></div>
	</div>

	<div class="sidebar_item">
		<img src="/ov-admin/img/icons/reports.png" alt="" class="sidebar_image" />
		<a href="javascript:ShowAdminSidebar('sub_reports')" title="Reports">Reports<?php if ($report_count > 0) { echo " ($report_count New)"; } ?></a>
	</div>
	<div class="admin_sidebar_submenu" id="sub_reports" <?php if ($current_section == "reports") { echo "style=\"display:block\""; } ?>>
		<div><a href="/ov-admin/reports?type=submission" title="Submission Reports" <?php if ($current_section == "reports" && $current_page == "submission") { echo "class=\"bold\""; } ?>>Submissions<?php if ($submission_report_count > 0) { echo " ($submission_report_count)"; } ?></a></div>
		<div><a href="/ov-admin/reports?type=comment" title="Comment Reports" <?php if ($current_section == "reports" && $current_page == "comment") { echo "class=\"bold\""; } ?>>Comments<?php if ($comment_report_count > 0) { echo " ($comment_report_count)"; } ?></a></div>
		<div><a href="/ov-admin/reports?type=user" title="User Reports" <?php if ($current_section == "reports" && $current_page == "user") { echo "class=\"bold\""; } ?>>Users<?php if ($user_report_count > 0) { echo " ($user_report_count)"; } ?></a></div>
	</div>
	
	<div class="sidebar_item">
		<img src="/ov-admin/img/icons/bans.png" alt="" class="sidebar_image" />
		<a href="javascript:ShowAdminSidebar('sub_bans')" title="Bans">Manage Bans</a>
	</div>
	<div class="admin_sidebar_submenu" id="sub_bans" <?php if ($current_section == "bans") { echo "style=\"display:block\""; } ?>>
		<div><a href="/ov-admin/bans?type=user" title="Users" <?php if ($current_section == "bans" && $current_page == "user") { echo "class=\"bold\""; } ?>>Users</a></div>
		<div><a href="/ov-admin/bans?type=user-ip" title="IP Addresses" <?php if ($current_page == "user-ip") { echo "class=\"bold\""; } ?>>User IP Addresses</a></div>
		<div><a href="/ov-admin/bans?type=ip" title="Independent IP Addresses" <?php if ($current_page == "ip") { echo "class=\"bold\""; } ?>>IP Addresses</a></div>
		<div><a href="/ov-admin/bans?type=domain" title="Domains" <?php if ($current_page == "domain") { echo "class=\"bold\""; } ?>>Domains</a></div>
		<div><a href="/ov-admin/bans?type=restricted_domain" title="Restricted Domains" <?php if ($current_page == "restricted_domain") { echo "class=\"bold\""; } ?>>Restricted Domains</a></div>
	</div>
<?php } ?>
	
<?php if ($ovAdminSecurity->CanAccessPreferences()) { ?>
	<div class="sidebar_item">
		<img src="/ov-admin/img/icons/categories.png" alt="" class="sidebar_image" />
		<a href="/ov-admin/categories" title="Categories">Manage Categories</a>
	</div>
	<div class="sidebar_item">
		<img src="/ov-admin/img/icons/settings.png" alt="" class="sidebar_image" />
		<a href="javascript:ShowAdminSidebar('sub_settings')" title="Settings">Settings</a>
	</div>
	<div class="admin_sidebar_submenu" id="sub_settings" <?php if ($current_section == "settings") { echo "style=\"display:block\""; } ?>>
		<div><a href="/ov-admin/settings?page=base" title="Base Settings" <?php if ($current_page == "base") { echo "class=\"bold\""; } ?>>Base Settings</a></div>
		<div><a href="/ov-admin/settings?page=karma" title="Karma Settings" <?php if ($current_page == "karma") { echo "class=\"bold\""; } ?>>Karma Settings</a></div>
		<div><a href="/ov-admin/settings?page=algorithm" title="Algorithm" <?php if ($current_page == "algorithm") { echo "class=\"bold\""; } ?>>Algorithm</a></div>
		<div><a href="/ov-admin/settings?page=submission" title="Submission" <?php if ($current_page == "submission") { echo "class=\"bold\""; } ?>>Submission Settings</a></div>
		<div><a href="/ov-admin/settings?page=comments" title="Comments" <?php if ($current_page == "comments") { echo "class=\"bold\""; } ?>>Comment Settings</a></div>
		<div><a href="/ov-admin/settings?page=policies" title="Policies" <?php if ($current_page == "policies") { echo "class=\"bold\""; } ?>>Policies</a></div>
		<div><a href="/ov-admin/settings?page=captcha" title="captcha" <?php if ($current_page == "captcha") { echo "class=\"bold\""; } ?>>Captcha</a></div>
		<div><a href="/ov-admin/settings?page=themes" title="Themes" <?php if ($current_page == "themes") { echo "class=\"bold\""; } ?>>Themes</a></div>
		<div><a href="/ov-admin/settings?page=ads" title="Ads" <?php if ($current_page == "ads") { echo "class=\"bold\""; } ?>>Ads &amp; Analytics</a></div>
	</div>
<?php } ?>

<?php if ($ovAdminSecurity->CanAccessContent()) { ?>
	<div class="sidebar_item">
		<img src="/ov-admin/img/icons/feedback.png" alt="" class="sidebar_image" />
		<a href="/ov-admin/feedback" title="Feedback">View Feedback</a>
	</div>
<?php } ?>	
	
<?php if ($ovAdminSecurity->CanAccessAdmins()) { ?>
	<div class="sidebar_item">
		<img src="/ov-admin/img/icons/user.png" alt="" class="sidebar_image" />
		<a href="/ov-admin/admins" title="Admins">Manage Admins</a>
	</div>
<?php } ?>