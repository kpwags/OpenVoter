<?php
	ini_set("include_path", ".:./:./ov-include:./../ov-include:./../../ov-include:./ov-admin/ov-include:./../ov-admin/ov-include:./../:./../../:./usercontrols:./../usercontrols:./../../usercontrols");
	
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
	
	require_once 'ov-config.php';
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	require_once 'ovadminsecurity.php';
	$ovAdminSecurity = new ovAdminSecurity();
	
	if (!$ovAdminSecurity->IsAdminLoggedIn()) {
		header("Location: /ov-admin/login");
		exit();
	}
	
	if (!$ovAdminSecurity->CanAccessContent()) {
		header("Location: /ov-admin");
		exit();
	}
	
	require_once 'ovadminreporting.php';
	$ovAdminReporting = new ovAdminReporting();
	
	require_once 'ovadminbans.php';
	$ovAdminBans = new ovAdminBans();
	
	if (isset($_GET['type'])) {
		$bans_page = $_GET['type'];
	} else {
		$bans_page = "user";
	}
	
	switch($bans_page) {
		case "user-ip":
			$ban_list = $ovAdminBans->GetBannedIPs();
			break;
		case "ip":
			$ban_list = $ovAdminBans->GetIndependentBannedIPs();
			break;
		case "domain":
			$ban_list = $ovAdminBans->GetBannedDomains();
			break;
		case "restricted_domain":
			$ban_list = $ovAdminBans->GetRestrictedDomains();
			break;
		case "user":
		default:
			$ban_list = $ovAdminBans->GetBannedUsers();
			break;
	}
	
	$current_section = "bans";
	$current_page = $bans_page;
?>

<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Admin Console | <?php echo $ovSettings->Title(); ?></title>
	<link rel="stylesheet" href="/css/jquery-ui/jquery-ui-1.8.5.custom.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="/ov-admin/css/master.css" />
	<link rel="shortcut icon" href="/img/favicon.ico" />
	<script type="text/javascript" src="/js/jquery-1.6.4.min.js"></script>
	<script type="text/javascript" src="/js/jquery-ui-1.8.5.custom.min.js"></script>
	<script type="text/javascript" src="/ov-admin/js/openvoteradmin.js"></script>
</head>
<body>
	<?php include 'admin_header.php'; ?>
	<div class="content">
		<div id="sidebar"><?php include 'admin_sidebar.php'; ?></div>
		<div id="main-content">
			<?php
				switch($bans_page) {
					case "user-ip":
						include 'bans/ips.php';
						break;
					case "ip":
						include 'bans/ip_addresses.php';
						break;
					case "domain":
						include 'bans/domains.php';
						break;
					case "restricted_domain":
						include 'bans/restricted_domains.php';
						break;
					case "user":
					default:
						include 'bans/users.php';
						break;
				}
			?>
		</div>
	</div>
	<?php //include 'footer.php'; ?>
	
	<div class="modal_form" title="Ban Domain" id="add_banned_domain">
		<div class="margin_tb_15">
			<label for="domain_name">Domain Name</label>
			<br/>
			<input type="text" name="domain_name" id="domain_name" value="" maxlength="255" style="width:402px"/>
		</div>
		<div class="margin_tb_15">
			<label for="ban_domain_reason">Reason for Ban</label>
			<br/>
			<textarea ID="ban_domain_reason" name="ban_domain_reason" style="height:100px;width:100%" class="limit255" charsleft="add_ban_chars_left"></textarea>
			<div class="align_right" id="add_ban_chars_left">255 characters remaining</div>
		</div>

		<div class="align_right">
			<button onclick="AddBannedDomain()" class="normal-button">Add</button>
		</div>
	</div>
	
	<div class="modal_form" title="Restrict Domain" id="add_restricted_domain">
		<div class="margin_tb_15">
			<label for="restricted_domain_name">Domain Name</label>
			<br/>
			<input type="text" name="restricted_domain_name" id="restricted_domain_name" value="" maxlength="255" style="width:402px"/>
		</div>
		<div class="margin_tb_15">
			<label for="restrict_domain_reason">Reason for Restriction</label>
			<br/>
			<textarea ID="restrict_domain_reason" name="restrict_domain_reason" style="height:100px;width:100%" class="limit255" charsleft="add_restricted_domain_chars_left"></textarea>
			<div class="align_right" id="add_restricted_domain_chars_left">255 characters remaining</div>
		</div>

		<div class="align_right">
			<button onclick="AddRestrictedDomain()" class="normal-button">Add</button>
		</div>
	</div>
	
	<div class="modal_form" title="Ban IP Address" id="add_banned_ip">
		<form method="post" action="/ov-admin/php/ban_independent_ip.php">
			<div class="margin_tb_15">
				<label for="ip_address_ban">IP Address</label>
				<br/>
				<input type="text" name="ip_address_ban" id="ip_address_ban" value="" maxlength="25" style="width:402px"/>
				<br/>
				<div class="form-hint">You can ban with wildcards, just use the * (e.g. 192.168.1.* OR 193.44.*)</div>
			</div>
			
			<div class="margin_tb_15">
				<label for="ban_ip_address_reason">Reason for Restriction</label>
				<br/>
				<textarea ID="ban_ip_address_reason" name="ban_ip_address_reason" style="height:100px;width:100%" class="limit255" charsleft="ban_ip_address_reason_chars_left"></textarea>
				<div class="align_right" id="ban_ip_address_reason_chars_left">255 characters remaining</div>
			</div>

			<div class="align_right">
				<button type="submit" class="normal-button">Ban</button>
			</div>
		</form>
	</div>
</body>
</html>