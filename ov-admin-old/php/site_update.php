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
	require_once 'ovdbconnector.php';
	
	/* NEW TABLES */
	
	$create_tables = "";
	
	if (!CheckTable(DB_PREFIX . "banned_ip_address")) {
		$create_tables .= "CREATE TABLE " . DB_PREFIX . "banned_ip_address (
			id INT NOT NULL AUTO_INCREMENT,
			ip_address VARCHAR(255) NOT NULL,
			reason VARCHAR(255) NULL,
			active TINYINT(3) NOT NULL DEFAULT 1,
			date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	}
	
	$queries = explode(";", $create_tables);
	
	foreach($queries as $query) {
		if ($query != "") {
			ovDBConnector::ExecuteNonQuery($query . ";");
		}
	}
	
	/* TABLE UPDATES */

	$table_updates = "";	
	if (!CheckColumn(DB_PREFIX . "site", "theme_dir")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site ADD COLUMN theme_dir VARCHAR(255) NOT NULL DEFAULT 'base' AFTER theme;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "enable_api")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site ADD COLUMN enable_api TINYINT(3) NOT NULL DEFAULT 1 AFTER google_analytics_code;";
	}
	
	if (!CheckColumn(DB_PREFIX . "user", "force_password_reset")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "user ADD COLUMN force_password_reset TINYINT(3) NOT NULL DEFAULT 0 AFTER location;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "use_header_image")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site DROP COLUMN use_header_image;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "header_image")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site DROP COLUMN header_image;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "favicon")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site DROP COLUMN favicon;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "error_page")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site DROP COLUMN error_page;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "top_ten_page_enabled")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site DROP COLUMN top_ten_page_enabled;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "show_voting_buttons_friends_page")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site DROP COLUMN show_voting_buttons_friends_page;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "show_voting_buttons_user_profile")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site DROP COLUMN show_voting_buttons_user_profile;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "friends_page_enabled")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site DROP COLUMN friends_page_enabled;";
	}
	
	if (!CheckColumn(DB_PREFIX . "site", "show_down_votes")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "site DROP COLUMN show_down_votes;";
	}
	
	if (!CheckColumn(DB_PREFIX . "user_settings", "prepopulate_reply")) {
		$table_updates .= "ALTER TABLE " . DB_PREFIX . "user_settings ADD COLUMN prepopulate_reply TINYINT(3) NOT NULL DEFAULT 0 AFTER subscribe_on_comment;";
	}

	$table_updates .= "ALTER TABLE " . DB_PREFIX . "user CHANGE details details VARCHAR(300) NULL;";
	
	$table_updates .= "DROP PROCEDURE IF EXISTS AddCommentVote;";
	$table_updates .= "DROP PROCEDURE IF EXISTS AddSubmissionVote;";
	$table_updates .= "DROP PROCEDURE IF EXISTS UpdateSubmissionVote;";
	$table_updates .= "DROP PROCEDURE IF EXISTS GetScores;";
	$table_updates .= "DROP PROCEDURE IF EXISTS ClearCurrentTheme;";
	$table_updates .= "DROP PROCEDURE IF EXISTS ApplyThemeCSS;";
	$table_updates .= "DROP PROCEDURE IF EXISTS ApplyThemeTemplate;";
	$table_updates .= "DROP PROCEDURE IF EXISTS GetTopTenUsers;";
	
	$queries = explode(";", $table_updates);
	
	foreach($queries as $query) {
		if ($query != "") {
			ovDBConnector::ExecuteNonQuery($query . ";");
		}
	}
	
	/* STORED PROCEDURES */
	
	$stored_procs = "
		DROP PROCEDURE IF EXISTS GetSettings//
		CREATE PROCEDURE GetSettings(IN siteId INT)
		BEGIN
			SELECT 
				root_url, mobile_root_url, title, theme, theme_dir, email_new_report, auto_report_keywords,
				blog, use_karma_system, karma_name, points_submission, points_comment, points_vote, points_popular, default_avatar, 
				default_photo_thumbnail, default_video_thumbnail, algorithm, threshold, comment_modify_time, pagination, show_votes, 
				enable_recaptcha, recaptcha_private_key, recaptcha_public_key, recaptcha_theme, about_site, privacy_policy, 
				terms_of_use, site_help, google_analytics_code, enable_api, version 
			FROM " . DB_PREFIX . "site 
			WHERE id = siteId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS GetSubmissionsForCategory//
		CREATE PROCEDURE GetSubmissionsForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN subType VARCHAR(10), IN selectOffset INT, IN selectLimit INT, IN loggedInUserId INT)
		BEGIN
			DECLARE typeClause VARCHAR(50);
			DECLARE blockedClause VARCHAR(255);

			IF loggedInUserId IS NULL THEN
				SET blockedClause = '';
			ELSE
				SET blockedClause = concat('AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM " . DB_PREFIX . "blocked_user WHERE site_id = ', siteId, ' AND user_id = ', loggedInUserId, ' AND active = 1)');
			END IF;

			IF subType = '' THEN 
				SET typeClause = '';
			ELSE
				SET typeClause = concat('AND s.type = \'', subType, '\'');
			END IF;

			SET @sql = concat('
				SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
					s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
				FROM " . DB_PREFIX . "submission s 
				INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
				WHERE 
					s.id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_category 
								WHERE category_id IN (SELECT id FROM " . DB_PREFIX . "category WHERE url_name = \'', categoryUrlName, '\' AND site_id = ', siteId, ' AND active = 1) AND 
									active = 1) 
					 ', typeClause, 
					' ', 
					blockedClause, ' 
					AND 
					s.popular = 0
					AND 
					s.active = 1 
					AND 
					s.site_id = ', siteId, 
				' ORDER BY s.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);

			PREPARE stmt FROM @sql;
			EXECUTE stmt;
		END//

		DROP PROCEDURE IF EXISTS GetSubmissionsForTag//
		CREATE PROCEDURE GetSubmissionsForTag(IN siteId INT, IN tagUrlName VARCHAR(35), IN subType VARCHAR(10), IN selectOffset INT, IN selectLimit INT, IN loggedInUserId INT)
		BEGIN
			DECLARE typeClause VARCHAR(50);
			DECLARE blockedClause VARCHAR(255);

			IF loggedInUserId IS NULL THEN
				SET blockedClause = '';
			ELSE
				SET blockedClause = concat('AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM " . DB_PREFIX . "blocked_user WHERE site_id = ', siteId, ' AND user_id = ', loggedInUserId, ' AND active = 1)');
			END IF;

			IF subType = '' THEN 
				SET typeClause = '';
			ELSE
				SET typeClause = concat('AND s.type = \'', subType, '\'');
			END IF;

			SET @sql = concat('
				SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
					s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
				FROM " . DB_PREFIX . "submission s 
				INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
				WHERE 
					s.id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_tag 
								WHERE tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE url_name = \'', tagUrlName, '\' AND site_id = ', siteId, ' AND active = 1) AND 
									active = 1) 
					 ', typeClause, 
					' ', 
					blockedClause, ' 
					AND 
					s.popular = 0 
					AND 
					s.active = 1 
					AND 
					s.site_id = ', siteId, 
				' ORDER BY s.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);

			PREPARE stmt FROM @sql;
			EXECUTE stmt;
		END//

		DROP PROCEDURE IF EXISTS GetSubmissionCountForCategory//
		CREATE PROCEDURE GetSubmissionCountForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN subType VARCHAR(10), IN loggedInUserId INT)
		BEGIN
			IF loggedInUserId IS NULL THEN
				IF subType = '' THEN 
					SELECT count(id) as num_subs FROM " . DB_PREFIX . "submission 
					WHERE 
						id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_category 
								WHERE 
									category_id IN (SELECT id FROM " . DB_PREFIX . "category WHERE url_name = categoryUrlName AND active = 1)
									AND site_id = siteId 
									AND active = 1)
						AND site_id = siteId AND active = 1 AND popular = 0;
				ELSE
						SELECT count(id) as num_subs FROM " . DB_PREFIX . "submission 
						WHERE 
							id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_category 
									WHERE 
										category_id IN (SELECT id FROM " . DB_PREFIX . "category WHERE url_name = categoryUrlName AND active = 1)
										AND site_id = siteId 
										AND active = 1)
							AND site_id = siteId AND type = subType AND active = 1 AND popular = 0;
				END IF;
			ELSE
				IF subType = '' THEN 
					SELECT count(id) as num_subs FROM " . DB_PREFIX . "submission 
					WHERE 
						id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_category 
								WHERE 
									category_id IN (SELECT id FROM " . DB_PREFIX . "category WHERE url_name = categoryUrlName AND active = 1)
									AND site_id = siteId 
									AND active = 1)
						AND site_id = siteId 
						AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM " . DB_PREFIX . "blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
						AND active = 1 
						AND popular = 0;
				ELSE
						SELECT count(id) as num_subs FROM " . DB_PREFIX . "submission 
						WHERE 
							id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_category 
									WHERE 
										category_id IN (SELECT id FROM " . DB_PREFIX . "category WHERE url_name = categoryUrlName AND active = 1)
										AND site_id = siteId 
										AND active = 1)
							AND site_id = siteId 
							AND type = subType 
							AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM " . DB_PREFIX . "blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
							AND active = 1 
							AND popular = 0;
				END IF;
			END IF;
		END//

		DROP PROCEDURE IF EXISTS GetSubmissionCountForTag//
		CREATE PROCEDURE GetSubmissionCountForTag(IN siteId INT, IN tagUrlName VARCHAR(255), IN subType VARCHAR(10), IN loggedInUserId INT)
		BEGIN
			IF loggedInUserId IS NULL THEN
				IF subType = '' THEN 
					SELECT count(id) as num_subs FROM " . DB_PREFIX . "submission 
					WHERE 
						id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_tag 
								WHERE 
									tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE url_name = tagUrlName AND active = 1)
									AND site_id = siteId 
									AND active = 1)
						AND site_id = siteId AND active = 1 AND popular = 0;
				ELSE
						SELECT count(id) as num_subs FROM " . DB_PREFIX . "submission 
						WHERE 
							id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_tag 
									WHERE 
										tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE url_name = tagUrlName AND active = 1)
										AND site_id = siteId 
										AND active = 1)
							AND site_id = siteId AND type = subType AND active = 1 AND popular = 0;
				END IF;
			ELSE
				IF subType = '' THEN 
					SELECT count(id) as num_subs FROM " . DB_PREFIX . "submission 
					WHERE 
						id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_tag 
								WHERE 
									tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE url_name = tagUrlName AND active = 1)
									AND site_id = siteId 
									AND active = 1)
						AND site_id = siteId 
						AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM " . DB_PREFIX . "blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
						AND active = 1 
						AND popular = 0;
				ELSE
						SELECT count(id) as num_subs FROM " . DB_PREFIX . "submission 
						WHERE 
							id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_tag 
									WHERE 
										tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE url_name = tagUrlName AND active = 1)
										AND site_id = siteId 
										AND active = 1)
							AND site_id = siteId 
							AND type = subType 
							AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM " . DB_PREFIX . "blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
							AND active = 1 
							AND popular = 0;
				END IF;
			END IF;
		END//

		DROP PROCEDURE IF EXISTS GetUserLikedSubmissionCount//
		CREATE PROCEDURE GetUserLikedSubmissionCount(IN siteId INT, IN userId INT)
		BEGIN
			SELECT COUNT(id) as num_submissions 
			FROM " . DB_PREFIX . "submission 
			WHERE site_id = siteId AND id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_vote WHERE user_id = userId AND direction = 1 AND active = 1) AND submitted_by_user_id != userId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS GetUserLikedSubmissions//
		CREATE PROCEDURE GetUserLikedSubmissions(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
		BEGIN
			SET @sql = concat('
				SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
					s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
				FROM " . DB_PREFIX . "submission s 
				INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
				WHERE 
					s.id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_vote WHERE user_id = ', userId, ' AND direction = 1 AND active = 1) 
					AND 
					s.active = 1 
					AND 
					submitted_by_user_id != ', userId, ' 
					AND 
					s.site_id = ', siteId, 
				' ORDER BY s.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);

			PREPARE stmt FROM @sql;
			EXECUTE stmt;
		END//

		DROP PROCEDURE IF EXISTS GetUserDislikedSubmissionCount//
		CREATE PROCEDURE GetUserDislikedSubmissionCount(IN siteId INT, IN userId INT)
		BEGIN
			SELECT COUNT(id) as num_submissions 
			FROM " . DB_PREFIX . "submission 
			WHERE site_id = siteId AND id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_vote WHERE user_id = userId AND direction = -1 AND active = 1) AND submitted_by_user_id != userId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS GetUserDislikedSubmissions//
		CREATE PROCEDURE GetUserDislikedSubmissions(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
		BEGIN
			SET @sql = concat('
				SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
					s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
				FROM " . DB_PREFIX . "submission s 
				INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
				WHERE 
					s.id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_vote WHERE user_id = ', userId, ' AND direction = -1 AND active = 1) 
					AND 
					s.active = 1 
					AND 
					submitted_by_user_id != ', userId, ' 
					AND 
					s.site_id = ', siteId, 
				' ORDER BY s.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);

			PREPARE stmt FROM @sql;
			EXECUTE stmt;
		END//

		DROP PROCEDURE IF EXISTS GetUserFavorites//
		CREATE PROCEDURE GetUserFavorites(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
		BEGIN
			SET @sql = concat('
				SELECT 
					\'submission\' AS favorite_type, 
					s.id AS submission_id, 
					s.type AS submission_type, 
					s.title AS submission_title, 
					f.date_created AS favorite_date, 
					\'\' AS comment_username 
				FROM " . DB_PREFIX . "submission s 
				INNER JOIN " . DB_PREFIX . "submission_favorite f ON f.submission_id = s.id 
				WHERE 
					f.user_id = ', userId, '
					AND 
					f.active = 1 
					AND 
					s.active = 1 
					AND 
					s.site_id = ', siteId, ' 
					AND 
					f.site_id = ', siteId, ' 
				UNION 
				SELECT 
					\'comment\' AS favorite_type, 
					s.id AS submission_id, 
					s.type AS submission_type, 
					s.title AS submission_title, 
					f.date_created AS favorite_date, 
					u.username AS comment_username 
				FROM " . DB_PREFIX . "comment c 
				INNER JOIN " . DB_PREFIX . "comment_favorite f ON f.comment_id = c.id 
				INNER JOIN " . DB_PREFIX . "submission s ON s.id = c.submission_id 
				INNER JOIN " . DB_PREFIX . "user u ON u.id = c.user_id 
				WHERE 
					f.user_id = ', userId, '
					AND 
					f.active = 1 
					AND 
					s.active = 1 
					AND 
					u.active = 1 
					AND 
					c.active = 1 
					AND 
					c.site_id = ', siteId, ' 
				ORDER BY favorite_date DESC LIMIT ', selectOffset , ', ', selectLimit);

			PREPARE stmt FROM @sql;
			EXECUTE stmt;
		END//

		DROP PROCEDURE IF EXISTS GetUserRecentActivityCount//
		CREATE PROCEDURE GetUserRecentActivityCount(IN siteId INT, IN userId INT)
		BEGIN
			DECLARE countFavoriteSubmissions INT;
			DECLARE countFavoriteComments INT;
			DECLARE countComments INT;
			DECLARE countSubmissions INT;

			SELECT 
				COUNT(s.id) INTO countFavoriteSubmissions
			FROM " . DB_PREFIX . "submission s 
			INNER JOIN " . DB_PREFIX . "submission_favorite f ON f.submission_id = s.id 
			WHERE 
				f.user_id = userId 
				AND 
				f.active = 1 
				AND 
				s.active = 1 
				AND 
				s.site_id = siteId 
				AND 
				f.site_id = siteId;

			SELECT 
				COUNT(c.id) INTO countFavoriteComments 
			FROM " . DB_PREFIX . "comment c 
			INNER JOIN " . DB_PREFIX . "comment_favorite f ON f.comment_id = c.id 
			INNER JOIN " . DB_PREFIX . "submission s ON s.id = c.submission_id 
			INNER JOIN " . DB_PREFIX . "user u ON u.id = c.user_id 
			WHERE 
				f.user_id = userId 
				AND 
				f.active = 1 
				AND 
				s.active = 1 
				AND 
				u.active = 1 
				AND 
				c.active = 1 
				AND 
				c.site_id = siteId 
				AND 
				u.site_id = siteId 
				AND 
				s.site_id = siteId 
				AND 
				f.site_id = siteId;

			SELECT COUNT(id) INTO countComments 
			FROM " . DB_PREFIX . "comment 
			WHERE site_id = siteId AND user_id = userId AND active = 1;

			SELECT COUNT(id) INTO countSubmissions
			FROM " . DB_PREFIX . "submission 
			WHERE site_id = siteId AND submitted_by_user_id = userId AND active = 1;

			SELECT (countFavoriteSubmissions + countFavoriteComments + countComments + countSubmissions) AS num_activities;
		END//

		DROP PROCEDURE IF EXISTS GetUserRecentActivity//
		CREATE PROCEDURE GetUserRecentActivity(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
		BEGIN
			SET @sql = CONCAT('
				SELECT 
					\'favorite\' AS activity_type,
					\'submission\' AS activity_sub_type, 
					s.id AS submission_id, 
					s.type AS submission_type, 
					s.title AS submission_title, 
					\'\' AS submission_summary, 
					\'\' AS submission_url, 
					\'\' AS submission_score, 
					\'\' AS submission_thumbnail, 
					\'\' AS submission_popular, 
					\'\' AS submission_user_id, 
					\'\' AS submission_location, 
					\'\' AS submission_username, 
					\'\' AS submission_user_avatar, 
					\'\' AS comment_id, 
					\'\' AS comment_body,
					\'\' AS comment_score,
					\'\' AS comment_username, 
					f.date_created AS activity_date 
				FROM " . DB_PREFIX . "submission s 
				INNER JOIN " . DB_PREFIX . "submission_favorite f ON f.submission_id = s.id 
				WHERE 
					f.user_id = ', userId, '
					AND 
					f.active = 1 
					AND 
					s.active = 1 
					AND 
					s.site_id = ', siteId, ' 
					AND 
					f.site_id = ', siteId, ' 
				UNION 
				SELECT 
					\'favorite\' AS activity_type,
					\'comment\' AS activity_sub_type, 
					s.id AS submission_id, 
					s.type AS submission_type, 
					s.title AS submission_title, 
					\'\' AS submission_summary, 
					\'\' AS submission_url, 
					\'\' AS submission_score, 
					\'\' AS submission_thumbnail, 
					\'\' AS submission_popular,
					\'\' AS submission_user_id, 
					\'\' AS submission_location, 
					\'\' AS submission_username, 
					\'\' AS submission_user_avatar, 
					\'\' AS comment_id, 
					\'\' AS comment_body,
					\'\' AS comment_score,
					u.username AS comment_username,
					f.date_created AS activity_date
				FROM " . DB_PREFIX . "comment c 
				INNER JOIN " . DB_PREFIX . "comment_favorite f ON f.comment_id = c.id 
				INNER JOIN " . DB_PREFIX . "submission s ON s.id = c.submission_id 
				INNER JOIN " . DB_PREFIX . "user u ON u.id = c.user_id 
				WHERE 
					f.user_id = ', userId, '
					AND 
					f.active = 1 
					AND 
					s.active = 1 
					AND 
					u.active = 1 
					AND 
					c.active = 1 
					AND 
					c.site_id = ', siteId, ' 
				UNION 
				SELECT 
					\'comment\' AS activity_type,
					\'\' AS activity_sub_type,
					s.id AS submission_id, 
					s.type AS submission_type, 
					s.title AS submission_title, 
					\'\' AS submission_summary, 
					\'\' AS submission_url, 
					\'\' AS submission_score, 
					\'\' AS submission_thumbnail, 
					\'\' AS submission_popular,
					\'\' AS submission_user_id, 
					\'\' AS submission_location, 
					\'\' AS submission_username, 
					\'\' AS submission_user_avatar,
					c.id AS comment_id, 
					c.body AS comment_body, 
					c.score AS comment_score, 
					\'\' AS comment_username, 
					c.date_created AS activity_date
				FROM " . DB_PREFIX . "comment c 
				INNER JOIN " . DB_PREFIX . "user u ON u.id = c.user_id 
				INNER JOIN " . DB_PREFIX . "submission s ON s.id = c.submission_id
				WHERE 
					c.site_id = ', siteId, ' 
					AND 
					u.site_id = ', siteId, ' 
					AND 
					s.site_id = ', siteId, ' 
					AND 
					c.user_id = ', userId, ' 
					AND 
					c.active = 1 
					AND 
					u.active = 1 
					AND 
					s.active = 1 
				UNION
				SELECT 
					\'submission\' AS activity_type,
					\'\' AS activity_sub_type, 
					s.id AS submission_id, 
					s.type AS submission_type, 
					s.title AS submission_title, 
					s.summary AS submission_summary, 
					s.url AS submission_url, 
					s.score AS submission_score, 
					s.thumbnail AS submission_thumbnail, 
					s.popular AS submission_popular,
					s.submitted_by_user_id AS submission_user_id, 
					s.location AS submission_location, 
					u.username AS submission_username, 
					u.avatar AS submission_user_avatar,
					\'\' AS comment_id, 
					\'\' AS comment_body, 
					\'\' AS comment_score,
					\'\' AS comment_username, 
					s.date_created AS activity_date
				FROM " . DB_PREFIX . "submission s 
				INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
				WHERE 
					s.submitted_by_user_id = ', userId, ' 
					AND 
					s.active = 1 
					AND 
					s.site_id = ', siteId, '
				ORDER BY activity_date DESC LIMIT ', selectOffset , ', ', selectLimit

				);
			PREPARE stmt FROM @sql;
			EXECUTE stmt;
		END//

		DROP PROCEDURE IF EXISTS  GetUserStats//
		CREATE PROCEDURE GetUserStats(IN siteId INT, IN userId INT)
		BEGIN
			DECLARE numSubmissions INT;
			DECLARE numComments INT;
			DECLARE numLikes INT;
			DECLARE numDislikes INT;
			DECLARE numVotes INT;
			DECLARE numSubmissionFavorites INT;
			DECLARE numCommentFavorites INT;
			DECLARE joinDate VARCHAR(100);

			SELECT 
				COUNT(id) INTO numSubmissions
			FROM " . DB_PREFIX . "submission 
			WHERE submitted_by_user_id = userId AND site_id = siteId AND active = 1;

			SELECT 
				COUNT(id) INTO numComments
			FROM " . DB_PREFIX . "comment 
			WHERE user_id = userId AND site_id = siteId AND active = 1;

			SELECT 
				COUNT(submission_id) INTO numLikes
			FROM " . DB_PREFIX . "submission_vote 
			WHERE user_id = userId AND site_id = siteId AND direction = 1 AND active = 1;

			SELECT 
				COUNT(submission_id) INTO numDislikes
			FROM " . DB_PREFIX . "submission_vote 
			WHERE user_id = userId AND site_id = siteId AND direction = -1 AND active = 1;

			SELECT 
				COUNT(submission_id) INTO numVotes
			FROM " . DB_PREFIX . "submission_vote 
			WHERE user_id = userId AND site_id = siteId AND active = 1;

			SELECT 
				COUNT(submission_id) INTO numSubmissionFavorites 
			FROM " . DB_PREFIX . "submission_favorite
			WHERE user_id = userId AND site_id = siteId AND active = 1;

			SELECT 
				COUNT(comment_id) INTO numCommentFavorites 
			FROM " . DB_PREFIX . "comment_favorite
			WHERE user_id = userId AND site_id = siteId AND active = 1;

			SELECT date_FORMAT(date_created, '%M %e, %Y') INTO joinDate 
			FROM " . DB_PREFIX . "user
			WHERE id = userId AND active = 1;

			SELECT 
				numSubmissions AS num_submissions, 
				numComments AS num_comments, 
				numLikes AS num_likes, 
				numDislikes AS num_dislikes, 
				numVotes AS num_votes,
				(numSubmissionFavorites + numCommentFavorites) AS num_favorites,
				joinDate AS join_date;
		END//

		DROP PROCEDURE IF EXISTS  GetAlertCategoryCounts//
		CREATE PROCEDURE GetAlertCategoryCounts(IN siteId INT, IN userId INT)
		BEGIN
			DECLARE numShares INT;
			DECLARE numComments INT;
			DECLARE numFollowers INT;
			DECLARE numFavorites INT;

			SELECT 
				COUNT(id) INTO numShares
			FROM " . DB_PREFIX . "alerts_share 
			WHERE alert_user_id = userId AND site_id = siteId AND active = 1;

			SELECT 
				COUNT(id) INTO numComments
			FROM " . DB_PREFIX . "alerts_comment 
			WHERE alert_user_id = userId AND site_id = siteId AND active = 1;

			SELECT 
				COUNT(id) INTO numFollowers
			FROM " . DB_PREFIX . "alerts_follower 
			WHERE alert_user_id = userId AND site_id = siteId AND active = 1;

			SELECT 
				COUNT(id) INTO numFavorites
			FROM " . DB_PREFIX . "alerts_favorite 
			WHERE alert_user_id = userId AND site_id = siteId AND active = 1;

			SELECT 
				numShares AS share_alert_count,
				numComments AS comment_alert_count,
				numFollowers AS follower_alert_count,
				numFavorites AS favorite_alert_count;
		END//

		DROP PROCEDURE IF EXISTS UpdateUserProfile//
		CREATE PROCEDURE UpdateUserProfile(IN siteId INT, IN userId INT, IN userDetails TEXT, IN userEmail VARCHAR(255), IN userWebsite VARCHAR(255), IN userLocation VARCHAR(255))
		BEGIN
			UPDATE " . DB_PREFIX . "user SET 
				details = userDetails, 
				email = userEmail, 
				website = userWebsite, 
				location = userLocation 
			WHERE 
				id = userId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS PostComment//
		CREATE PROCEDURE PostComment(IN siteId INT, IN submissionId INT, IN userId INT, IN commentBody TEXT)
		BEGIN
			DECLARE userActive TINYINT;
			DECLARE commentId INT;
			DECLARE karmaPoints DECIMAL(3,2);
			DECLARE currentTime TIMESTAMP;

			SELECT CURRENT_TIMESTAMP() INTO currentTime;

			SELECT active INTO userActive FROM " . DB_PREFIX . "user WHERE id = userId;

			IF userActive = 1 THEN
				SELECT 
					points_comment INTO karmaPoints
				FROM " . DB_PREFIX . "site 
				WHERE id = siteId AND active = 1;

				INSERT INTO " . DB_PREFIX . "comment (site_id, submission_id, user_id, body, date_created) VALUES (siteId, submissionId, userId, commentBody, currentTime);

				SELECT id INTO commentId FROM " . DB_PREFIX . "comment WHERE site_id = siteId AND submission_id = submissionId AND user_id = userId AND body = commentBody AND date_created = currentTime AND active = 1;

				CALL CommentVote(siteId, userId, commentId, 1);

				CALL AdjustUserKarma(userId, karmaPoints);

				SELECT commentId AS comment_id;
			ELSE
				SELECT 0 AS comment_id;
			END IF;
		END//

		DROP PROCEDURE IF EXISTS PostCommentReply//
		CREATE PROCEDURE PostCommentReply(IN siteId INT, IN submissionId INT, IN userId INT, IN commentBody TEXT, IN commentRepliedToId INT)
		BEGIN
			DECLARE userActive TINYINT;
			DECLARE commentId INT;
			DECLARE karmaPoints DECIMAL(3,2);
			DECLARE currentTime TIMESTAMP;

			SELECT CURRENT_TIMESTAMP() INTO currentTime;

			SELECT active INTO userActive FROM " . DB_PREFIX . "user WHERE id = userId;

			IF userActive = 1 THEN
				SELECT 
					points_comment INTO karmaPoints
				FROM " . DB_PREFIX . "site 
				WHERE id = siteId AND active = 1;

				INSERT INTO " . DB_PREFIX . "comment (site_id, submission_id, user_id, body) VALUES (siteId, submissionId, userId, commentBody);

				SELECT id INTO commentId FROM " . DB_PREFIX . "comment WHERE site_id = siteId AND submission_id = submissionId AND user_id = userId AND body = commentBody AND date_created = currentTime AND active = 1;

				CALL CommentVote(siteId, userId, commentId, 1);
				CALL LinkCommentReply(siteId, commentId, commentRepliedToId);

				CALL AdjustUserKarma(userId, karmaPoints);

				SELECT commentId AS comment_id;
			ELSE
				SELECT 0 AS comment_id;
			END IF;
		END//

		DROP PROCEDURE IF EXISTS GetSubmissionVote//
		CREATE PROCEDURE GetSubmissionVote(IN siteId INT, IN userId INT, IN submissionId INT)
		BEGIN 
			SELECT direction FROM " . DB_PREFIX . "submission_vote WHERE user_id = userId AND submission_id = submissionId;
		END//

		DROP PROCEDURE IF EXISTS SubmissionVote//
		CREATE PROCEDURE SubmissionVote(IN siteId INT, IN userId INT, IN submissionId INT, IN voteDirection TINYINT)
		BEGIN 
			DECLARE voteCount INT;
			DECLARE previousVote TINYINT;
			DECLARE pointsVote DECIMAL(3,2);
			DECLARE submissionUser INT;

			SELECT 
				points_vote INTO pointsVote
			FROM " . DB_PREFIX . "site 
			WHERE id = siteId AND active = 1;

			SELECT 
				submitted_by_user_id INTO submissionUser 
			FROM " . DB_PREFIX . "submission 
			WHERE id = submissionId AND active = 1;

			SELECT COUNT(submission_id) INTO voteCount FROM " . DB_PREFIX . "submission_vote WHERE user_id = userId AND submission_id = submissionId;

			IF voteCount > 0 THEN
				SELECT direction INTO previousVote FROM " . DB_PREFIX . "submission_vote WHERE site_id = siteId AND user_id = userId AND submission_id = submissionId;
				UPDATE " . DB_PREFIX . "submission_vote SET direction = voteDirection, active = 1 WHERE site_id = siteId AND user_id = userId AND submission_id = submissionId;

				IF previousVote = -1 AND voteDirection = 0 THEN
					IF submissionUser != userId THEN
						CALL AdjustUserKarma(userId, (-1 * pointsVote));
					END IF;
					UPDATE " . DB_PREFIX . "submission_vote SET active = 0 WHERE site_id = siteId AND user_id = userId AND submission_id = submissionId;
				END IF;
				IF previousVote = 1 AND voteDirection = 0 THEN
					IF submissionUser != userId THEN
						CALL AdjustUserKarma(userId, (-1 * pointsVote));
					END IF;
					UPDATE " . DB_PREFIX . "submission_vote SET active = 0 WHERE site_id = siteId AND user_id = userId AND submission_id = submissionId;
				END IF;
				IF previousVote = 0 AND voteDirection = 1 AND userId != submissionUser THEN
					CALL AdjustUserKarma(userId, pointsVote);
				END IF;
				IF previousVote = 0 AND voteDirection = -1 AND userId != submissionUser THEN
					CALL AdjustUserKarma(userId, pointsVote);
				END IF;
			ELSE
				INSERT INTO " . DB_PREFIX . "submission_vote (site_id, user_id, submission_id, direction) VALUES(siteId, userId, submissionId, voteDirection);
				IF submissionUser != userId THEN
					CALL AdjustUserKarma(userId, pointsVote);
				END IF;
			END IF;
		END//

		DROP PROCEDURE IF EXISTS GetSubmissionScore//
		CREATE PROCEDURE GetSubmissionScore(IN submissionId INT)
		BEGIN
			DECLARE numUpVotes INT;
			DECLARE numDownVotes INT;

			SELECT COUNT(submission_id) INTO numUpVotes FROM " . DB_PREFIX . "submission_vote WHERE submission_id = submissionId AND direction = 1 AND active = 1;
			SELECT COUNT(submission_id) INTO numDownVotes FROM " . DB_PREFIX . "submission_vote WHERE submission_id = submissionId AND direction = -1 AND active = 1;

			SELECT (numUpVotes - numDownVotes) AS score;
		END//

		DROP PROCEDURE IF EXISTS GetCommentScore//
		CREATE PROCEDURE GetCommentScore(IN commentId INT)
		BEGIN
			DECLARE numUpVotes INT;
			DECLARE numDownVotes INT;

			SELECT COUNT(comment_id) INTO numUpVotes FROM " . DB_PREFIX . "comment_vote WHERE comment_id = commentId AND direction = 1 AND active = 1;
			SELECT COUNT(comment_id) INTO numDownVotes FROM " . DB_PREFIX . "comment_vote WHERE comment_id = commentId AND direction = -1 AND active = 1;

			SELECT (numUpVotes - numDownVotes) AS score;
		END//

		DROP PROCEDURE IF EXISTS CommentVote//
		CREATE PROCEDURE CommentVote(IN siteId INT, IN userId INT, IN commentId INT, IN voteDirection TINYINT)
		BEGIN 
			DECLARE voteCount INT;
			DECLARE previousVote TINYINT;
			DECLARE pointsCommentUp DECIMAL(3,2);
			DECLARE pointsCommentDown DECIMAL(3,2);
			DECLARE commentUser INT;

			SELECT 
				points_comment_vote_down INTO pointsCommentDown 
			FROM " . DB_PREFIX . "site 
			WHERE id = siteId AND active = 1;

			SELECT 
				points_comment_vote_up INTO pointsCommentUp
			FROM " . DB_PREFIX . "site 
			WHERE id = siteId AND active = 1;

			SELECT 
				user_id INTO commentUser 
			FROM " . DB_PREFIX . "comment 
			WHERE id = commentId AND active = 1;	

			SELECT COUNT(comment_id) INTO voteCount FROM " . DB_PREFIX . "comment_vote WHERE user_id = userId AND comment_id = commentId;

			IF voteCount > 0 THEN
				SELECT direction INTO previousVote FROM " . DB_PREFIX . "comment_vote WHERE site_id = siteId AND user_id = userId AND comment_id = commentId;

				IF previousVote = 0 AND voteDirection = 1 AND commentUser != userId THEN
					CALL AdjustUserKarma(commentUser, pointsCommentUp);
				END IF;

				IF previousVote = 0 AND voteDirection = -1 AND commentUser != userId  THEN
					CALL AdjustUserKarma(commentUser, pointsCommentDown);
				END IF;

				IF previousVote = 1 AND voteDirection = 0 AND commentUser != userId  THEN
					CALL AdjustUserKarma(commentUser, (-1 * pointsCommentUp));
				END IF;

				IF previousVote = 1 AND voteDirection = -1 AND commentUser != userId  THEN
					CALL AdjustUserKarma(commentUser, ((-1 * pointsCommentUp) - pointsCommentDown));
				END IF;

				IF previousVote = -1 AND voteDirection = 0 AND commentUser != userId  THEN
					CALL AdjustUserKarma(commentUser, (-1 * pointsCommentDown));
				END IF;

				IF previousVote = -1 AND voteDirection = 1 AND commentUser != userId  THEN
					CALL AdjustUserKarma(commentUser, ((-1 * pointsCommentDown) + pointsCommentUp));
				END IF;

				IF voteDirection = 0 THEN
					UPDATE " . DB_PREFIX . "comment_vote SET direction = voteDirection, active = 0 WHERE site_id = siteId AND user_id = userId AND comment_id = commentId;
				ELSE
					UPDATE " . DB_PREFIX . "comment_vote SET direction = voteDirection, active = 1 WHERE site_id = siteId AND user_id = userId AND comment_id = commentId;
				END IF;
			ELSE
				INSERT INTO " . DB_PREFIX . "comment_vote (site_id, user_id, comment_id, direction) VALUES(siteId, userId, commentId, voteDirection);
				IF commentUser != userId THEN
					IF voteDirection = 1 THEN
						CALL AdjustUserKarma(commentUser, pointsCommentUp);
					ELSE
						CALL AdjustUserKarma(commentUser, pointsCommentDown);
					END IF;
				END IF;
			END IF;
		END//

		DROP PROCEDURE IF EXISTS GetAverageSubmissionScore//
		CREATE PROCEDURE GetAverageSubmissionScore(IN siteId INT)
		BEGIN
			DECLARE startDate DATE;
			DECLARE numUpVotes INT;
			DECLARE numDownVotes INT;
			DECLARE numSubmissions INT;

			SET startDate = DATE_SUB(NOW(), INTERVAL 7 DAY);

			SELECT COUNT(id) INTO numSubmissions FROM " . DB_PREFIX . "submission WHERE date_created > startDate AND site_id = siteId AND active = 1;
			SELECT COUNT(submission_id) INTO numUpVotes FROM " . DB_PREFIX . "submission_vote WHERE direction = 1 AND active = 1;
			SELECT COUNT(submission_id) INTO numDownVotes FROM " . DB_PREFIX . "submission_vote WHERE direction = -1 AND active = 1;

			IF numSubmissions = 0 THEN
				SELECT 0 AS average_score;
			ELSE
				SELECT (numUpVotes - numDownVotes) / numSubmissions AS average_score;
			END IF;
		END//

		DROP PROCEDURE IF EXISTS AdminGetCategories//
		CREATE PROCEDURE AdminGetCategories(IN siteId INT)
		BEGIN
			SELECT 
				c.id, c.name, c.url_name, c.sort_order, 
				(SELECT COUNT(id) FROM category WHERE site_id = siteId AND id IN (SELECT child_category_id FROM " . DB_PREFIX . "subcategory WHERE site_id = siteId AND active = 1 AND parent_category_id = c.id) AND active = 1) AS num_subcategories 
			FROM category c
			WHERE 
				c.site_id = siteId AND 
				c.active = 1 AND 
				c.id NOT IN (SELECT child_category_id FROM " . DB_PREFIX . "subcategory WHERE site_id = siteId AND active = 1) 
			ORDER BY c.sort_order ASC;
		END//

		DROP PROCEDURE IF EXISTS GetUserReportDetails//
		CREATE PROCEDURE GetUserReportDetails(IN reportId INT)
		BEGIN
			SELECT 
				r.id, u.id AS user_id, u.username, u.email, u.details, u.website, u.avatar 
			FROM " . DB_PREFIX . "report_object r 
			INNER JOIN " . DB_PREFIX . "user u ON u.id = r.object_id 
			WHERE 
				r.id = reportId 
				AND r.object_type = 'user' 
				AND r.active = 1;
		END//

		DROP PROCEDURE IF EXISTS IsUserLinkedToIP//
		CREATE PROCEDURE IsUserLinkedToIP(IN userId INT, IN ipAddress VARCHAR(255))
		BEGIN
			SELECT COUNT(id) AS count_ip FROM " . DB_PREFIX . "user_ip_address WHERE ip_address = ipAddress AND user_id = userId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS SaveBaseSettings//
		CREATE PROCEDURE SaveBaseSettings(IN siteId INT, IN rootUrl VARCHAR(255), IN siteTitle VARCHAR(255), IN siteBlog VARCHAR(255), IN enableAPI TINYINT)
		BEGIN
			UPDATE " . DB_PREFIX . "site SET 
				root_url = rootUrl,
				title = siteTitle, 
				blog = siteBlog, 
				enable_api = enableAPI 
			WHERE id = siteId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS GetBaseSettings//
		CREATE PROCEDURE GetBaseSettings(IN siteId INT)
		BEGIN
			SELECT root_url, title, blog, enable_api FROM " . DB_PREFIX . "site WHERE id = siteId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS GetKarmaSettings//
		CREATE PROCEDURE GetKarmaSettings(IN siteId INT)
		BEGIN
			SELECT use_karma_system, karma_name, points_submission, points_comment, points_vote, 
				points_popular, points_comment_vote_up, points_comment_vote_down, 
				karma_penalties, karma_penalty_1_threshold, karma_penalty_1_comments, karma_penalty_1_submissions, 
				karma_penalty_2_threshold, karma_penalty_2_comments, karma_penalty_2_submissions 
			FROM " . DB_PREFIX . "site WHERE id = siteId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS SaveKarmaSettings//
		CREATE PROCEDURE SaveKarmaSettings(IN siteId INT, IN useKarmaSystem TINYINT, IN karmaName VARCHAR(20), IN pointsSubmission DECIMAL(3,2), IN pointsComment DECIMAL(3,2), IN pointsVote DECIMAL(3,2), IN pointsPopular DECIMAL(3,2), IN pointsCommentUpVote DECIMAL(3,2), IN pointsCommentDownVote DECIMAL(3,2), IN karmaPenalties TINYINT, IN karma1Threshold INT, IN karma1Submissions INT, IN karma1Comments INT, IN karma2Threshold INT, IN karma2Submissions INT, IN karma2Comments INT)
		BEGIN
			UPDATE " . DB_PREFIX . "site SET 
				use_karma_system = useKarmaSystem,
				karma_name = karmaName,
				points_submission = pointsSubmission,
				points_comment = pointsComment,
				points_vote = pointsVote,
				points_popular = pointsPopular,
				points_comment_vote_up = pointsCommentUpVote,
				points_comment_vote_down = pointsCommentDownVote,
				karma_penalties = karmaPenalties,
				karma_penalty_1_threshold = karma1Threshold, 
				karma_penalty_1_submissions = karma1Submissions,
				karma_penalty_1_comments = karma1Comments, 
				karma_penalty_2_threshold = karma2Threshold, 
				karma_penalty_2_submissions = karma2Submissions,
				karma_penalty_2_comments = karma2Comments 
			WHERE id = siteId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS GetSubmissionSettings//
		CREATE PROCEDURE GetSubmissionSettings(IN siteId INT)
		BEGIN
			SELECT pagination, show_votes FROM " . DB_PREFIX . "site WHERE id = siteId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS SaveSubmissionSettings//
		CREATE PROCEDURE SaveSubmissionSettings(IN siteId INT, IN sitePagination INT, IN showVotes TINYINT)
		BEGIN
			UPDATE " . DB_PREFIX . "site SET 
				pagination = sitePagination,
				show_votes = showVotes 
			WHERE id = siteId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS UpdateSiteTheme//
		CREATE PROCEDURE UpdateSiteTheme(IN siteId INT, IN xmlFile VARCHAR(255), IN rootDir VARCHAR(255))
		BEGIN
			UPDATE " . DB_PREFIX . "site SET theme = xmlFile, theme_dir = rootDir WHERE id = siteId;
		END//

		DROP PROCEDURE IF EXISTS ResetUserPassword//
		CREATE PROCEDURE ResetUserPassword(IN userId INT, IN password VARCHAR(255), IN passwordSalt VARCHAR(255), IN passwordKey VARCHAR(255))
		BEGIN
			UPDATE " . DB_PREFIX . "user SET password = password, password_salt = passwordSalt, password_key = passwordKey, force_password_reset = 1 WHERE id = userId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS EnforcePasswordChange//
		CREATE PROCEDURE EnforcePasswordChange(IN userId INT)
		BEGIN
			SELECT force_password_reset FROM " . DB_PREFIX . "user WHERE id = userId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS ResetUserPasswordAndQuestion//
		CREATE PROCEDURE ResetUserPasswordAndQuestion(IN userId INT, IN password VARCHAR(255), IN question VARCHAR(255), IN answer VARCHAR(255), IN passwordSalt VARCHAR(255), IN passwordKey VARCHAR(255))
		BEGIN
			UPDATE " . DB_PREFIX . "user SET 
				password = password, 
				password_salt = passwordSalt, 
				password_key = passwordKey, 
				force_password_reset = 0, 
				security_question = question,
				security_answer = answer,
				security_answer_salt = passwordSalt,
				security_answer_key = passwordKey
			WHERE id = userId AND active = 1;
		END//

		DROP PROCEDURE IF EXISTS GetTopPopularSubmissionsForCategory//
		CREATE PROCEDURE GetTopPopularSubmissionsForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN subType VARCHAR(10))
		BEGIN
				IF subType = '' THEN
					SELECT id, type, title, score FROM " . DB_PREFIX . "submission
					WHERE
						site_id = siteId AND 
						active = 1 AND
						popular = 1 AND 
						id IN (SELECT submission_id 
								FROM " . DB_PREFIX . "submission_category 
								WHERE site_id = siteId AND 
									active = 1 AND 
									category_id IN (SELECT id FROM " . DB_PREFIX . "category WHERE site_id = siteId AND active = 1 AND url_name = categoryUrlName)
						) AND 
						popular_date >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
						ORDER BY score DESC 
						LIMIT 0, 10;
				ELSE
					SELECT id, type, title, score FROM " . DB_PREFIX . "submission
					WHERE
						site_id = siteId AND 
						type = subType AND 
						active = 1 AND
						popular = 1 AND 
						id IN (SELECT submission_id 
								FROM " . DB_PREFIX . "submission_category 
								WHERE site_id = siteId AND 
									active = 1 AND 
									category_id IN (SELECT id FROM " . DB_PREFIX . "category WHERE site_id = siteId AND active = 1 AND url_name = categoryUrlName)
						) AND 
						popular_date >= DATE_SUB(NOW(),INTERVAL 4 DAY)
						ORDER BY score DESC 
						LIMIT 0, 10;
				END IF;
		END//

		DROP PROCEDURE IF EXISTS GetTopPopularSubmissionsForTag//
		CREATE PROCEDURE GetTopPopularSubmissionsForTag(IN siteId INT, IN tagUrlName VARCHAR(35), IN subType VARCHAR(10))
		BEGIN
			IF subType = '' THEN
				SELECT id, type, title, score FROM " . DB_PREFIX . "submission
				WHERE
					site_id = siteId AND 
					active = 1 AND
					popular = 1 AND 
					id IN (SELECT submission_id 
							FROM " . DB_PREFIX . "submission_tag 
							WHERE site_id = siteId AND 
								active = 1 AND 
								tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE site_id = siteId AND active = 1 AND url_name = tagUrlName)
					) AND 
					popular_date >= DATE_SUB(NOW(),INTERVAL 4 DAY)  
					ORDER BY score DESC 
					LIMIT 0, 10;
			ELSE
				SELECT id, type, title, score FROM " . DB_PREFIX . "submission
				WHERE
					site_id = siteId AND 
					type = subType AND 
					active = 1 AND
					popular = 1 AND 
					id IN (SELECT submission_id 
							FROM " . DB_PREFIX . "submission_tag 
							WHERE site_id = siteId AND 
								active = 1 AND 
								tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE site_id = siteId AND active = 1 AND url_name = tagUrlName)
					) AND 
					popular_date >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
					ORDER BY score DESC 
					LIMIT 0, 10;
			END IF;
		END//

		DROP PROCEDURE IF EXISTS GetTopUpcomingSubmissionsForCategory//
		CREATE PROCEDURE GetTopUpcomingSubmissionsForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN subType VARCHAR(10))
		BEGIN
			IF subType = 'all' THEN
				SELECT id, type, title, score FROM " . DB_PREFIX . "submission
				WHERE
					site_id = siteId AND 
					active = 1 AND
					popular = 0 AND 
					id IN (SELECT submission_id 
							FROM " . DB_PREFIX . "submission_category 
							WHERE site_id = siteId AND 
								active = 1 AND 
								category_id IN (SELECT id FROM " . DB_PREFIX . "category WHERE site_id = siteId AND active = 1 AND url_name = categoryUrlName)
					) AND 
					date_created >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
					ORDER BY score DESC 
					LIMIT 0, 10;
			ELSE
				SELECT id, type, title, score FROM " . DB_PREFIX . "submission
				WHERE
					site_id = siteId AND 
					type = subType AND 
					active = 1 AND
					popular = 0 AND 
					id IN (SELECT submission_id 
							FROM " . DB_PREFIX . "submission_category 
							WHERE site_id = siteId AND 
								active = 1 AND 
								category_id IN (SELECT id FROM " . DB_PREFIX . "category WHERE site_id = siteId AND active = 1 AND url_name = categoryUrlName)
					) AND 
					date_created >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
					ORDER BY score DESC 
					LIMIT 0, 10;
			END IF;
		END//

		DROP PROCEDURE IF EXISTS GetTopUpcomingSubmissionsForTag//
		CREATE PROCEDURE GetTopUpcomingSubmissionsForTag(IN siteId INT, IN tagUrlName VARCHAR(35), IN subType VARCHAR(10))
		BEGIN
			IF subType = 'all' THEN
				SELECT id, type, title, score FROM " . DB_PREFIX . "submission
				WHERE
					site_id = siteId AND 
					active = 1 AND
					popular = 0 AND 
					id IN (SELECT submission_id 
							FROM " . DB_PREFIX . "submission_tag 
							WHERE site_id = siteId AND 
								active = 1 AND 
								tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE site_id = siteId AND active = 1 AND url_name = tagUrlName)
					) AND 
					date_created >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
					ORDER BY score DESC 
					LIMIT 0, 10;
			ELSE
				SELECT id, type, title, score FROM " . DB_PREFIX . "submission
				WHERE
					site_id = siteId AND 
					type = subType AND 
					active = 1 AND
					popular = 0 AND 
					id IN (SELECT submission_id 
							FROM " . DB_PREFIX . "submission_tag 
							WHERE site_id = siteId AND 
								active = 1 AND 
								tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE site_id = siteId AND active = 1 AND url_name = tagUrlName)
					) AND 
					date_created >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
					ORDER BY score DESC 
					LIMIT 0, 10;
			END IF;
		END//
		
		DROP PROCEDURE IF EXISTS DeleteUser//
		CREATE PROCEDURE DeleteUser(IN userId INT, IN byUser TINYINT)
		BEGIN
			CALL DeleteUserComments(userId, byUser);
			CALL DeleteUserSubmissions(userId);
			CALL DeleteUserFriends(userId);
			CALL DeleteUserBlockedUsers(userId);
			CALL DeleteUserCommentFavorites(userId);
			CALL DeleteUserSubscriptions(userId);
			CALL DeleteUserSubmsissionFavorites(userId);
			CALL DeleteUserSubmsissionVotes(userId);
			CALL DeleteUserReports(userId);
			CALL DeleteUserAlerts(userId);
			CALL DeleteUserInfo(userId);
		END//

		DROP PROCEDURE IF EXISTS DeleteUserAlerts//
		CREATE PROCEDURE DeleteUserAlerts(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "alerts_favorite SET active = 0 WHERE alert_user_id = userId OR favorite_user_id = userId;
			UPDATE " . DB_PREFIX . "alerts_follower SET active = 0 WHERE alert_user_id = userId OR follower_user_id = userId;
			UPDATE " . DB_PREFIX . "alerts_share SET active = 0 WHERE alert_user_id = userId OR share_user_id = userId;
			UPDATE " . DB_PREFIX . "alerts_comment SET active = 0 WHERE alert_user_id = userId OR comment_user_id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserBlockedUsers//
		CREATE PROCEDURE DeleteUserBlockedUsers(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "blocked_user SET active = 0 WHERE user_id = userId OR user_is_blocking_id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserCommentFavorites//
		CREATE PROCEDURE DeleteUserCommentFavorites(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "comment_favorite SET active = 0 WHERE user_id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserComments//
		CREATE PROCEDURE DeleteUserComments(IN userId INT, byUser TINYINT)
		BEGIN
			UPDATE " . DB_PREFIX . "comment SET active = 0, deleted_by_user = byUser WHERE user_id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserCommentVotes//
		CREATE PROCEDURE DeleteUserCommentVotes(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "comment_vote SET active = 0, direction = 0 WHERE user_id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserFriends//
		CREATE PROCEDURE DeleteUserFriends(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "friend SET active = 0 WHERE user_id = userId OR user_is_following_id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserInfo//
		CREATE PROCEDURE DeleteUserInfo(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "user_settings SET active = 0 WHERE user_id = userId;
			UPDATE " . DB_PREFIX . "user_ip_address SET active = 0 WHERE user_id = userId;
			UPDATE " . DB_PREFIX . "user SET active = 0 WHERE id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserReports//
		CREATE PROCEDURE DeleteUserReports(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "report SET active = 0 WHERE reporting_user_id = userId;
			UPDATE " . DB_PREFIX . "report_object SET active = 0 WHERE object_type = 'user' AND object_id = userId;
			UPDATE " . DB_PREFIX . "report_object SET active = 0 WHERE object_type = 'submission' AND object_id IN (SELECT id FROM " . DB_PREFIX . "submission WHERE submitted_by_user_id = userId);
			UPDATE " . DB_PREFIX . "report_object SET active = 0 WHERE object_type = 'comment' AND object_id IN (SELECT id FROM " . DB_PREFIX . "comment WHERE user_id = userId);
		END//

		DROP PROCEDURE IF EXISTS DeleteUserSubmissions//
		CREATE PROCEDURE DeleteUserSubmissions(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "submission_category SET active = 0 WHERE submission_id IN (SELECT id FROM " . DB_PREFIX . "submission WHERE submitted_by_user_id = userId);
			UPDATE " . DB_PREFIX . "submission_tag SET active = 0 WHERE submission_id IN (SELECT id FROM " . DB_PREFIX . "submission WHERE submitted_by_user_id = userId);
			UPDATE " . DB_PREFIX . "submission SET active = 0 WHERE submitted_by_user_id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserSubmsissionFavorites//
		CREATE PROCEDURE DeleteUserSubmsissionFavorites(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "submission_favorite SET active = 0 WHERE user_id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserSubmsissionVotes//
		CREATE PROCEDURE DeleteUserSubmsissionVotes(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "submission_vote SET active = 0, direction = 0 WHERE user_id = userId;
		END//

		DROP PROCEDURE IF EXISTS DeleteUserSubscriptions//
		CREATE PROCEDURE DeleteUserSubscriptions(IN userId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "subscription SET active = 0 WHERE user_id = userId;
		END//
		
		DROP PROCEDURE IF EXISTS BanUser//
		CREATE PROCEDURE BanUser(IN userId INT, IN adminId INT, IN banReason VARCHAR(255))
		BEGIN
			UPDATE " . DB_PREFIX . "user SET 
				banned = 1, 
				ban_reason = banReason,
				banned_by_admin_id = adminId 
			WHERE id = userId;

			CALL DeleteUser(userId, 0);

			UPDATE " . DB_PREFIX . "user_ip_address SET banned = 1 WHERE user_id = userId;
		END//
		
		DROP PROCEDURE IF EXISTS GetUserSettings//
		CREATE PROCEDURE GetUserSettings(IN siteId INT, IN userId INT)
		BEGIN
			SELECT start_page, start_page_title, alert_comments, alert_shares, alert_messages, alert_followers, alert_favorites, open_links_in, subscribe_on_submit, 
				subscribe_on_comment, comment_threshold, prepopulate_reply  
			FROM " . DB_PREFIX . "user_settings 
			WHERE user_id = userId AND active = 1;
		END//
		
		DROP PROCEDURE IF EXISTS UpdateUserSettings//
		CREATE PROCEDURE UpdateUserSettings(IN siteId INT, IN userId INT, IN openLinksIn VARCHAR(10), IN subscribeSubmit TINYINT, IN subscribeComment TINYINT, IN commentThreshold INT, IN prepopulateReply TINYINT)
		BEGIN
			UPDATE " . DB_PREFIX . "user_settings SET 
				open_links_in = openLinksIn, 
				subscribe_on_submit = subscribeSubmit, 
				subscribe_on_comment = subscribeComment, 
				comment_threshold = commentThreshold, 
				prepopulate_reply = prepopulateReply 
			WHERE user_id = userId AND active = 1;
		END//
		
		DROP PROCEDURE IF EXISTS SaveAdminProfileSettings//
		CREATE PROCEDURE SaveAdminProfileSettings(IN adminUserId INT, IN fullName VARCHAR(255), IN adminEmail VARCHAR(255), IN emailReports TINYINT, IN emailFeedback TINYINT)
		BEGIN
			UPDATE " . DB_PREFIX . "admin_user SET 
				full_name = fullName, 
				email = adminEmail, 
				email_reports = emailReports, 
				email_feedback = emailFeedback 
			WHERE id = adminUserId AND active = 1;
		END//
		
		DROP PROCEDURE IF EXISTS GetIndependentBannedIPs//
		CREATE PROCEDURE GetIndependentBannedIPs()
		BEGIN
			SELECT 
				id, ip_address, reason 
			FROM " . DB_PREFIX . "banned_ip_address 
			WHERE active = 1;
		END//

		DROP PROCEDURE IF EXISTS UnbanIndependentIPAddress//
		CREATE PROCEDURE UnbanIndependentIPAddress(IN bannedIPId INT)
		BEGIN
			UPDATE " . DB_PREFIX . "banned_ip_address SET active = 0 WHERE id = bannedIPId;
		END//

		DROP PROCEDURE IF EXISTS BanIndependentIPAddress//
		CREATE PROCEDURE BanIndependentIPAddress(IN ipAddress VARCHAR(255), IN banReason VARCHAR(255))
		BEGIN
			INSERT INTO " . DB_PREFIX . "banned_ip_address (ip_address, reason) VALUES (ipAddress, banReason);
		END//
	";
	
	
	$queries = explode("//", $stored_procs);
	
	foreach($queries as $query) {
		if ($query != "") {
			ovDBConnector::ExecuteNonQuery($query . ";");
		}
	}
	
	// version
	$update_version = "UPDATE " . DB_PREFIX . "site SET version = '3.2.3' WHERE id = " . SITE_ID;
	ovDBConnector::ExecuteNonQuery($update_version);
	
	header("Location: /ov-admin/update.php?success=yes");
	exit();
	
	function CheckColumn($table, $column)
	{
		$query = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = '$table'	AND COLUMN_NAME = '$column'";
		$result = ovDBConnector::Query($query);
		
		if ($result) {
			if ($result->num_rows > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	function CheckTable($table)
	{
		$query = "SELECT * FROM information_schema.TABLES WHERE TABLE_NAME = '$table'";
		$result = ovDBConnector::Query($query);
		
		if ($result) {
			if ($result->num_rows > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
?>