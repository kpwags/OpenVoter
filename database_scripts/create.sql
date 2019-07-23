CREATE TABLE site (
	id INT NOT NULL auto_increment,
	root_url VARCHAR(255) NOT NULL,
	mobile_root_url VARCHAR(255) NULL,
	title VARCHAR(255) NOT NULL,
	theme VARCHAR(255) NOT NULL,
	theme_dir VARCHAR(255) NOT NULL DEFAULT 'base',
	email_new_report BIT NOT NULL DEFAULT 1,
	auto_report_keywords VARCHAR(2000) NULL,
	blog VARCHAR(255) NULL,
	use_karma_system BIT NOT NULL DEFAULT 1,
	karma_name VARCHAR(20) NOT NULL DEFAULT 'points',
	points_submission DECIMAL(3,2) NOT NULL DEFAULT 2,
	points_comment DECIMAL(3,2) NOT NULL DEFAULT 3,
	points_vote DECIMAL(3,2) NOT NULL DEFAULT 0,
	points_popular DECIMAL(3,2) NOT NULL DEFAULT 0,
	points_comment_vote_up DECIMAL(3,2) NOT NULL DEFAULT 1,
	points_comment_vote_down DECIMAL(3,2) NOT NULL DEFAULT 0,
	default_avatar BLOB NULL,
	default_photo_thumbnail BLOB NULL,
	default_video_thumbnail BLOB NULL,
	algorithm VARCHAR(15) NOT NULL DEFAULT 'static',
	threshold DECIMAL(4,2) NOT NULL DEFAULT 10,
	comment_modify_time int(11) NOT NULL DEFAULT '15',
	pagination INT NOT NULL DEFAULT 10,
	show_votes BIT NOT NULL DEFAULT 1,
	karma_penalties TINYINT(3) NOT NULL DEFAULT 0,
	karma_penalty_1_threshold INT NOT NULL DEFAULT -50,
	karma_penalty_1_comments INT NOT NULL DEFAULT 5,
	karma_penalty_1_submissions INT NOT NULL DEFAULT 5,
	karma_penalty_2_threshold INT NOT NULL DEFAULT -100,
	karma_penalty_2_comments INT NOT NULL DEFAULT 5,
	karma_penalty_2_submissions INT NOT NULL DEFAULT 5,
	enable_recaptcha BIT NOT NULL DEFAULT 0,
	recaptcha_private_key VARCHAR(255) NULL,
	recaptcha_public_key VARCHAR(255) NULL,
	recaptcha_theme VARCHAR(255) NULL DEFAULT 'clean',
	about_site TEXT NULL,
	privacy_policy TEXT NULL,
	terms_of_use TEXT NULL,
	site_help TEXT NULL,
	google_analytics_code TEXT NULL,
	enable_api TINYINT(3) NOT NULL DEFAULT 1,
	version VARCHAR(20) NOT NULL DEFAULT '3.0',
	active BIT NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE site_email (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	email_name VARCHAR(255) NOT NULL,
	email_address VARCHAR(255) NOT NULL,
	active BIT NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_site_email_site_id (site_id),
	CONSTRAINT fk_site_email_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE site_ads (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	top_ad TEXT NULL,
	side_ad TEXT NULL,
	top_full_ad TEXT NULL,
	ad_pages VARCHAR(2000) NULL,
	active BIT NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_site_ads_site_id (site_id),
	CONSTRAINT fk_site_ads_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE admin_role (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	role_name VARCHAR(50) NOT NULL,
	site_preferences BIT NOT NULL DEFAULT 1,
	content_management BIT NOT NULL DEFAULT 1,
	manage_admins BIT NOT NULL DEFAULT 1,
	active BIT NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_admin_role_site_id (site_id),
	CONSTRAINT fk_admin_role_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE admin_user (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	username VARCHAR(20) NOT NULL,
	full_name VARCHAR(75) NULL,
	email VARCHAR(255) NOT NULL,
	password VARCHAR(255) NOT NULL,
	password_salt VARCHAR(25) NOT NULL,
	password_key VARCHAR(25) NOT NULL,
	email_feedback BIT NOT NULL DEFAULT 1,
	email_reports BIT NOT NULL DEFAULT 1,
	role INT NOT NULL,
	active BIT NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_admin_user_role (role),
	KEY fk_admin_user_site_id (site_id),
	CONSTRAINT fk_admin_user_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_admin_user_role FOREIGN KEY (role) REFERENCES admin_role (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE user (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	username VARCHAR(20) NOT NULL,
	password VARCHAR(255) NOT NULL,
	password_salt VARCHAR(25) NOT NULL,
	password_key VARCHAR(25) NOT NULL,
	email VARCHAR(255) NOT NULL,
	avatar VARCHAR(255) NULL DEFAULT '/img/default_user.png',
	security_question VARCHAR(255) NOT NULL,
	security_answer_salt VARCHAR(255) NOT NULL,
	security_answer_key VARCHAR(25) NOT NULL,
	password_key VARCHAR(25) NOT NULL,
	details VARCHAR(300) NULL,
	karma_points DECIMAL(20,2) NOT NULL DEFAULT 0,
	website VARCHAR(255) NULL,
	location VARCHAR(255) NULL,
	force_password_reset TINYINT(3) NOT NULL DEFAULT 0,
	suspended BIT NOT NULL DEFAULT 0,
	suspended_date TIMESTAMP NULL,
	banned BIT NOT NULL DEFAULT 0,
	ban_reason TEXT NULL,
	banned_by_admin_id INT NULL,
	active BIT NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_user_banned_by_admin_id (banned_by_admin_id),
	KEY fk_user_site_id (site_id),
	CONSTRAINT fk_user_banned_by_admin_id FOREIGN KEY (banned_by_admin_id) REFERENCES admin_user (id) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT fk_user_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE user_settings (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	user_id INT NOT NULL,
	start_page VARCHAR(255) NOT NULL DEFAULT '/',
	start_page_title VARCHAR(255) NOT NULL DEFAULT 'Home',
	alert_comments VARCHAR(10) NOT NULL DEFAULT 'SITE',
	alert_shares VARCHAR(10) NOT NULL DEFAULT 'SITE',
	alert_messages VARCHAR(10) NOT NULL DEFAULT 'SITE',
	alert_followers VARCHAR(10) NOT NULL DEFAULT 'SITE',
	alert_favorites VARCHAR(10) NOT NULL DEFAULT 'SITE',
	open_links_in VARCHAR(10) NOT NULL DEFAULT '_blank',
	subscribe_on_submit TINYINT(3) NOT NULL DEFAULT 1,
	subscribe_on_comment TINYINT(3) NOT NULL DEFAULT 1,
	prepopulate_reply TINYINT(3) NOT NULL DEFAULT 0,
	filter VARCHAR(1000) NULL,
	hide_blocked_comments TINYINT(3) NOT NULL DEFAULT 1,
	hide_blocked_submissions TINYINT(3) NOT NULL DEFAULT 1,
	comment_threshold INT NOT NULL DEFAULT -2,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_user_settings_user_id (user_id),
	KEY fk_user_settings_site_id (site_id),
	CONSTRAINT fk_user_settings_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_user_settings_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE user_ip_address (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	user_id INT NOT NULL,
	ip_address VARCHAR(50) NOT NULL,
	banned TINYINT(3) NOT NULL DEFAULT 0,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_user_ip_address_user_id (user_id),
	KEY fk_user_ip_address_site_id (site_id),
	CONSTRAINT fk_user_ip_address_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_user_ip_address_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE friend (
	user_id INT NOT NULL,
	site_id INT NOT NULL,
	user_is_following_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (user_id, user_is_following_id),
	KEY fk_friend_user_id (user_id),
	KEY fk_friend_user_is_following_id (user_is_following_id),
	KEY fk_friend_site_id (site_id),
	CONSTRAINT fk_friend_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_friend_user_is_following_id FOREIGN KEY (user_is_following_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_friend_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE blocked_user (
	user_id INT NOT NULL,
	site_id INT NOT NULL,
	user_is_blocking_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (user_id, user_is_blocking_id),
	KEY fk_blocked_user_user_id (user_id),
	KEY fk_blocked_user_user_is_blocking_id (user_is_blocking_id),
	KEY fk_blocked_user_site_id (site_id),
	CONSTRAINT fk_blocked_user_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_blocked_user_user_is_blocking_id FOREIGN KEY (user_is_blocking_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_blocked_user_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE category (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	name VARCHAR(35) NOT NULL,
	url_name VARCHAR(35) NOT NULL,
	sort_order TINYINT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_category_site_id (site_id),
	CONSTRAINT fk_category_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE subcategory (
	parent_category_id INT NOT NULL,
	child_category_id INT NOT NULL,
	site_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (parent_category_id, child_category_id),
	KEY fk_subcategory_site_id (site_id),
	KEY fk_subcategory_parent_category_id (parent_category_id),
	KEY fk_subcategory_child_category_id (child_category_id),
	CONSTRAINT fk_subcategory_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_subcategory_parent_category_id FOREIGN KEY (parent_category_id) REFERENCES category (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_subcategory_child_category_id FOREIGN KEY (child_category_id) REFERENCES category (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE tag (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	name VARCHAR(255) NOT NULL,
	url_name VARCHAR(255) NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_tag_site_id (site_id),
	CONSTRAINT fk_tag_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE submission (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	submitted_by_user_id INT NOT NULL,
	type VARCHAR(10) NOT NULL DEFAULT 'STORY',
	title VARCHAR(255) NOT NULL,
	summary TEXT NULL,
	url VARCHAR(255) NOT NULL,
	score INT NOT NULL DEFAULT 0,
	thumbnail BLOB NULL,
	popular TINYINT(3) NOT NULL DEFAULT 0,
	popular_date TIMESTAMP NULL,
	can_edit TINYINT(3) NOT NULL DEFAULT 1,
	location VARCHAR(255) NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_submission_site_id (site_id),
	KEY fk_submission_submitted_by_user_id (submitted_by_user_id),
	CONSTRAINT fk_submission_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_submission_submitted_by_user_id FOREIGN KEY (submitted_by_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE submission_category (
	submission_id INT NOT NULL,
	category_id INT NOT NULL,
	site_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (submission_id, category_id),
	KEY fk_submission_category_site_id (site_id),
	KEY fk_submission_category_submission_id (submission_id),
	KEY fk_submission_category_category_id (category_id),
	CONSTRAINT fk_submission_category_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_submission_category_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_submission_category_category_id FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE submission_tag (
	submission_id INT NOT NULL,
	tag_id INT NOT NULL,
	site_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (submission_id, tag_id),
	KEY fk_submission_tag_site_id (site_id),
	KEY fk_submission_tag_submission_id (submission_id),
	KEY fk_submission_tag_tag_id (tag_id),
	CONSTRAINT fk_submission_tag_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_submission_tag_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_submission_tag_tag_id FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE submission_vote (
	submission_id INT NOT NULL,
	user_id INT NOT NULL,
	site_id INT NOT NULL,
	direction TINYINT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (submission_id, user_id),
	KEY fk_submission_vote_site_id (site_id),
	KEY fk_submission_vote_submission_id (submission_id),
	KEY fk_submission_vote_user_id (user_id),
	CONSTRAINT fk_submission_vote_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_submission_vote_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_submission_vote_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE submission_favorite (
	submission_id INT NOT NULL,
	user_id INT NOT NULL,
	site_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (submission_id, user_id),
	KEY fk_submission_favorite_site_id (site_id),
	KEY fk_submission_favorite_submission_id (submission_id),
	KEY fk_submission_favorite_user_id (user_id),
	CONSTRAINT fk_submission_favorite_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_submission_favorite_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_submission_favorite_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE subscription (
	submission_id INT NOT NULL,
	user_id INT NOT NULL,
	site_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (submission_id, user_id),
	KEY fk_subscription_site_id (site_id),
	KEY fk_subscription_submission_id (submission_id),
	KEY fk_subscription_user_id (user_id),
	CONSTRAINT fk_subscription_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_subscription_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_subscription_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE theme (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	part VARCHAR(25) NOT NULL,
	filename VARCHAR(255) NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_theme_site_id (site_id),
	CONSTRAINT fk_theme_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE search (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	submission_id INT NOT NULL,
	title VARCHAR(255) NOT NULL,
	summary VARCHAR(255) NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_search_site_id (site_id),
	KEY fk_search_submission_id (submission_id),
	CONSTRAINT fk_search_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_seach_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE,
	FULLTEXT KEY fulltext_search_title_summary (title,summary)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE comment (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	submission_id INT NOT NULL,
	user_id INT NOT NULL,
	body TEXT NOT NULL,
	score INT NOT NULL DEFAULT 1,
	edited TINYINT(3) NOT NULL DEFAULT 0,
	deleted_by_user TINYINT(3) NOT NULL DEFAULT 1,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_comment_site_id (site_id),
	KEY fk_comment_submission_id (submission_id),
	KEY fk_comment_user_id (user_id),
	CONSTRAINT fk_comment_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_comment_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_comment_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE comment_reply (
	comment_id INT NOT NULL,
	comment_replied_to_id INT NOT NULL,
	site_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (comment_id, comment_replied_to_id),
	KEY fk_comment_reply_site_id (site_id),
	KEY fk_comment_reply_comment_id (comment_id),
	KEY fk_comment_reply_comment_replied_to_id (comment_replied_to_id),
	CONSTRAINT fk_comment_reply_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_comment_reply_comment_id FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_comment_reply_comment_replied_to_id FOREIGN KEY (comment_replied_to_id) REFERENCES comment (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE comment_vote (
	comment_id INT NOT NULL,
	user_id INT NOT NULL,
	site_id INT NOT NULL,
	direction TINYINT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (comment_id, user_id),
	KEY fk_comment_vote_site_id (site_id),
	KEY fk_comment_vote_comment_id (comment_id),
	KEY fk_comment_vote_user_id (user_id),
	CONSTRAINT fk_comment_vote_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_comment_vote_comment_id FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_comment_vote_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE comment_favorite (
	comment_id INT NOT NULL,
	user_id INT NOT NULL,
	site_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (comment_id, user_id),
	KEY fk_comment_favorite_site_id (site_id),
	KEY fk_comment_favorite_comment_id (comment_id),
	KEY fk_comment_favorite_user_id (user_id),
	CONSTRAINT fk_comment_favorite_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_comment_favorite_comment_id FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_comment_favorite_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE banned_domain (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	domain_name VARCHAR(255) NOT NULL,
	reason VARCHAR(255) NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_banned_domain_site_id (site_id),
	CONSTRAINT fk_banned_domain_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE faq (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	question VARCHAR(500) NOT NULL,
	answer TEXT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_site_id (site_id),
	CONSTRAINT fk_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE report_object (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	object_type VARCHAR(25) NOT NULL,
	object_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_report_object_site_id (site_id),
	CONSTRAINT fk_report_object_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE report (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	reason VARCHAR(25) NOT NULL,
	details VARCHAR(255),
	reporting_user_id INT NOT NULL,
	report_object_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_report_site_id (site_id),
	KEY fk_report_reporting_user_id (reporting_user_id),
	CONSTRAINT fk_report_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_report_reporting_user_id FOREIGN KEY (reporting_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE feedback(
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	name VARCHAR(255) NOT NULL,
	email VARCHAR(255) NOT NULL,
	reason VARCHAR(50) NOT NULL,
	message TEXT NOT NULL,
	unread TINYINT(3) NOT NULL DEFAULT 1,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_feedback_site_id (site_id),
	CONSTRAINT fk_feedback_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE alerts_comment (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	alert_user_id INT NOT NULL,
	comment_user_id INT NOT NULL,
	submission_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_alerts_comment_site_id (site_id),
	KEY fk_alerts_comment_submission_id (submission_id),
	KEY fk_alerts_comment_comment_user_id (comment_user_id),
	CONSTRAINT fk_alerts_comment_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_comment_alert_user_id FOREIGN KEY (alert_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_comment_comment_user_id FOREIGN KEY (comment_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_comment_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE alerts_share (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	alert_user_id INT NOT NULL,
	share_user_id INT NOT NULL,
	submission_id INT NOT NULL,
	message VARCHAR(255) NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_alerts_share_site_id (site_id),
	KEY fk_alerts_share_submission_id (submission_id),
	KEY fk_alerts_share_share_user_id (share_user_id),
	CONSTRAINT fk_alerts_share_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_share_alert_user_id FOREIGN KEY (alert_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_share_share_user_id FOREIGN KEY (share_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_share_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE alerts_follower (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	alert_user_id INT NOT NULL,
	follower_user_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_alerts_follower_site_id (site_id),
	KEY fk_alerts_follower_alert_user_id (alert_user_id),
	KEY fk_alerts_follower_follower_user_id (follower_user_id),
	CONSTRAINT fk_alerts_follower_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_follower_alert_user_id FOREIGN KEY (alert_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_follower_follower_user_id FOREIGN KEY (follower_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE alerts_favorite (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	alert_user_id INT NOT NULL,
	favorite_user_id INT NOT NULL,
	submission_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_alerts_favorite_site_id (site_id),
	KEY fk_alerts_favorite_submission_id (submission_id),
	KEY fk_alerts_favorite_favorite_user_id (favorite_user_id),
	CONSTRAINT fk_alerts_favorite_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_favorite_alert_user_id FOREIGN KEY (alert_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_favorite_favorite_user_id FOREIGN KEY (favorite_user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_alerts_favorite_submission_id FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE restricted_domain (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	domain_name VARCHAR(255) NOT NULL,
	reason VARCHAR(255) NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_restricted_domain_site_id (site_id),
	CONSTRAINT fk_restricted_domain_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_site_id (site_id),
	CONSTRAINT fk_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;