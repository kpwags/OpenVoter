/* NEW TABLES */

CREATE TABLE list (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	name VARCHAR(125) NOT NULL,
	unique_name VARCHAR(125) NOT NULL,
	user_id INT NOT NULL,
	is_private TINYINT(1) NOT NULL DEFAULT 0,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_link_site_id (site_id),
	KEY fk_link_user_id (user_id),
	CONSTRAINT fk_link_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_link_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE list_user (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	list_id INT NOT NULL,
	user_id INT NOT NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_link_user_site_id (site_id),
	KEY fk_link_user_list_id (list_id),
	KEY fk_link_user_user_id (user_id),
	CONSTRAINT fk_link_user_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_link_user_list_id FOREIGN KEY (list_id) REFERENCES list (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_link_user_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/* TABLE EDITS */
ALTER TABLE submission ADD COLUMN group_submission TINYINT(1) NOT NULL DEFAULT 0 AFTER popular_date;
ALTER TABLE user ADD COLUMN twitter_username VARCHAR(255) NULL AFTER location;
ALTER TABLE user_settings ADD COLUMN publicly_display_likes TINYINT(1) NOT NULL DEFAULT 1 AFTER comment_threshold;
ALTER TABLE user CHANGE avatar avatar VARCHAR(255) NULL DEFAULT '/img/default_user.png'

/* NEW STORED PROCEDURES */
DROP PROCEDURE IF EXISTS GetUserLists//
CREATE PROCEDURE GetUserLists(IN siteId INT, IN userId INT)
BEGIN
	SELECT 
		id, name, unique_name, is_private 
	FROM list 
	WHERE 
		user_id = userId AND 
		site_id = siteId AND 
		active = 1
	ORDER BY name ASC;
END//

DROP PROCEDURE IF EXISTS GetAllSubmissionsCount//
CREATE PROCEDURE GetAllSubmissionsCount(IN siteId INT, IN subType VARCHAR(10), IN submissionPopular TINYINT, IN loggedInUserId INT)
BEGIN
	IF loggedInUserId IS NULL THEN
		IF subType = '' THEN 
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				site_id = siteId 
				AND group_submission = 0 
				AND active = 1 
				AND popular = submissionPopular;
		ELSE
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				site_id = siteId 
				AND group_submission = 0 
				AND type = subType 
				AND active = 1 
				AND popular = submissionPopular;
		END IF;
	ELSE
		IF subType = '' THEN 
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				site_id = siteId 
				AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
				AND active = 1 
				AND popular = submissionPopular 
				AND group_submission = 0;
		ELSE
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				site_id = siteId 
				AND type = subType 
				AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
				AND active = 1 
				AND popular = submissionPopular 
				AND group_submission = 0;
		END IF;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetAllSubmissions//
CREATE PROCEDURE GetAllSubmissions(IN siteId INT, IN subType VARCHAR(10), IN submissionPopular TINYINT, IN selectOffset INT, IN selectLimit INT, IN loggedInUserId INT)
BEGIN
	DECLARE typeClause VARCHAR(50);
	DECLARE blockedClause VARCHAR(255);

	IF loggedInUserId IS NULL THEN
		SET blockedClause = '';
	ELSE
		SET blockedClause = concat('AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = ', siteId, ' AND user_id = ', loggedInUserId, ' AND active = 1)');
	END IF;

	IF subType = '' THEN 
		SET typeClause = ' ';
	ELSE
		SET typeClause = concat(' AND s.type = \'', subType, '\' ');
	END IF;

	SET @sql = concat('
		SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
			s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
		WHERE 
		s.site_id = ', siteId, typeClause, blockedClause, ' AND s.popular = ', submissionPopular, ' AND s.active = 1 AND group_submission = 0 
			 ORDER BY s.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);
		
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS GetUserRecentActivityNoLikesCount//
CREATE PROCEDURE GetUserRecentActivityNoLikesCount(IN siteId INT, IN userId INT)
BEGIN
	DECLARE countFavoriteSubmissions INT;
	DECLARE countFavoriteComments INT;
	DECLARE countComments INT;
	DECLARE countSubmissions INT;
	
	SELECT 
		COUNT(s.id) INTO countFavoriteSubmissions
	FROM submission s 
	INNER JOIN submission_favorite f ON f.submission_id = s.id 
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
	FROM comment c 
	INNER JOIN comment_favorite f ON f.comment_id = c.id 
	INNER JOIN submission s ON s.id = c.submission_id 
	INNER JOIN user u ON u.id = c.user_id 
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
	FROM comment 
	WHERE site_id = siteId AND user_id = userId AND active = 1;
	
	SELECT COUNT(id) INTO countSubmissions
	FROM submission 
	WHERE site_id = siteId AND submitted_by_user_id = userId AND active = 1;
	
	SELECT (countFavoriteSubmissions + countFavoriteComments + countComments + countSubmissions) AS num_activities;
END//

DROP PROCEDURE IF EXISTS GetUserRecentNoLikesActivity//
CREATE PROCEDURE GetUserRecentNoLikesActivity(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
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
			\'\' AS comment_id, 
			\'\' AS comment_body,
			\'\' AS comment_username, 
			f.date_created AS activity_date 
		FROM submission s 
		INNER JOIN submission_favorite f ON f.submission_id = s.id 
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
			c.id AS comment_id, 
			c.body AS comment_body,
			u.username AS comment_username,
			f.date_created AS activity_date
		FROM comment c 
		INNER JOIN comment_favorite f ON f.comment_id = c.id 
		INNER JOIN submission s ON s.id = c.submission_id 
		INNER JOIN user u ON u.id = c.user_id 
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
			c.id AS comment_id, 
			c.body AS comment_body, 
			\'\' AS comment_username, 
			c.date_created AS activity_date
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id 
		INNER JOIN submission s ON s.id = c.submission_id
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
			\'\' AS comment_id, 
			\'\' AS comment_body, 
			\'\' AS comment_username, 
			s.date_created AS activity_date
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
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

DROP PROCEDURE IF EXISTS DoesListExist//
CREATE PROCEDURE DoesListExist(IN siteId INT, IN userId INT, IN listName VARCHAR(255))
BEGIN
	SELECT COUNT(id) AS num_lists FROM list WHERE site_id = siteId AND user_id = userId AND unique_name = listName;
END//

DROP PROCEDURE IF EXISTS DoesListExistExcludeList//
CREATE PROCEDURE DoesListExistExcludeList(IN siteId INT, IN listId INT, IN userId INT, IN listName VARCHAR(255))
BEGIN
	SELECT COUNT(id) AS num_lists FROM list WHERE site_id = siteId AND user_id = userId AND unique_name = listName AND id != listId;
END//

DROP PROCEDURE IF EXISTS AddList//
CREATE PROCEDURE AddList(IN siteId INT, IN listName VARCHAR(255), IN listUniqueName VARCHAR(255), IN isPrivate TINYINT(1), IN userId INT)
BEGIN
	INSERT INTO list (site_id, name, unique_name, is_private, user_id) VALUES (siteId, listName, listUniqueName, isPrivate, userId);

	SELECT
		id, name, unique_name, is_private 
	FROM list 
	WHERE user_id = userId AND unique_name = listUniqueName 
	LIMIT 1;
END//

DROP PROCEDURE IF EXISTS AddUserToList//
CREATE PROCEDURE AddUserToList(IN siteId INT, IN listId INT, IN userToAddId INT)
BEGIN
	INSERT INTO list_user (site_id, list_id, user_id) VALUES (siteId, listId, userToAddId);
END//

DROP PROCEDURE IF EXISTS DeleteUserFromList//
CREATE PROCEDURE DeleteUserFromList(IN siteId INT, IN listId INT, IN userToRemoveId INT)
BEGIN
	DELETE FROM list_user WHERE site_id = siteId AND list_id = listId AND user_id = userToRemoveId;
END//

DROP PROCEDURE IF EXISTS IsUserOwnerOfList//
CREATE PROCEDURE IsUserOwnerOfList(IN siteId INT, IN listId INT, IN userId INT)
BEGIN
	IF ((SELECT COUNT(id) FROM list WHERE site_id = siteId AND id = listId AND user_id = userId) > 0) THEN
		SELECT 'YES' AS owner;
	ELSE
		SELECT 'NO' AS owner;
	END IF;
END//

DROP PROCEDURE IF EXISTS IsUserInList//
CREATE PROCEDURE IsUserInList(IN siteId INT, IN userId INT, IN listId INT)
BEGIN
	SELECT COUNT(id) AS in_list FROM list_user WHERE site_id = siteId AND user_id = userId AND list_id = listId;
END//

DROP PROCEDURE IF EXISTS GetMembersInList//
CREATE PROCEDURE GetMembersInList(IN siteId INT, IN listId INT)
BEGIN
	SELECT 
		id, username, avatar 
	FROM user 
	WHERE 
		id IN (SELECT user_id FROM list_user WHERE list_id = listId) 
		AND 
		active = 1 
		AND 
		site_id = siteId 
	ORDER BY username ASC;
END//

DROP PROCEDURE IF EXISTS EditList//
CREATE PROCEDURE EditList(IN siteId INT, IN listId INT, IN listName VARCHAR(255), IN listUniqueName VARCHAR(255), IN isPrivate TINYINT(1), IN userId INT)
BEGIN
	UPDATE list SET 
		name = listName,
		unique_name = listUniqueName,
		is_private = isPrivate 
	WHERE id = listId;

	SELECT
		id, name, unique_name, is_private 
	FROM list 
	WHERE id = listId AND active = 1 
	LIMIT 1;
END//

DROP PROCEDURE IF EXISTS DeleteList//
CREATE PROCEDURE DeleteList(IN listId INT)
BEGIN
	DELETE FROM list_user WHERE list_id = listId;
	DELETE FROM list WHERE id = listId;
END//

DROP PROCEDURE IF EXISTS GetListDetailsByUserAndName//
CREATE PROCEDURE GetListDetailsByUserAndName(IN siteId INT, IN listUsername VARCHAR(255), IN listUniqueName VARCHAR(255))
BEGIN
	SELECT 
		l.id, l.name, l.unique_name, l.is_private, u.username 
	FROM list l 
	INNER JOIN user u ON u.id = l.user_id 
	WHERE 
		l.user_id IN (SELECT id FROM user WHERE username = listUsername AND active = 1)
		AND 
		l.unique_name = listUniqueName 
		AND 
		l.active = 1 
		AND 
		l.site_id = siteId 
	LIMIT 1;
END//

DROP PROCEDURE IF EXISTS GetSubmissionCountForList//
CREATE PROCEDURE GetSubmissionCountForList(IN siteId INT, IN listId INT, IN subType VARCHAR(10))
BEGIN
	IF subType = '' THEN 
		SELECT count(id) as num_subs FROM submission 
		WHERE 
			submitted_by_user_id IN (SELECT user_id FROM list_user WHERE list_id = listId) 
			AND site_id = siteId 
			AND active = 1;
	ELSE
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				submitted_by_user_id IN (SELECT user_id FROM list_user WHERE list_id = listId) 
				AND site_id = siteId 
				AND type = subType 
				AND active = 1;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetSubmissionsForList//
CREATE PROCEDURE GetSubmissionsForList(IN siteId INT, IN listId INT, IN subType VARCHAR(10), IN selectOffset INT, IN selectLimit INT)
BEGIN
	DECLARE typeClause VARCHAR(50);

	IF subType = '' THEN 
		SET typeClause = '';
	ELSE
		SET typeClause = concat('AND s.type = \'', subType, '\'');
	END IF;

	SET @sql = concat('
		SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
			s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
		WHERE 
			submitted_by_user_id IN (SELECT user_id FROM list_user WHERE list_id = ', listId , ') 
			 ', typeClause, ' 
			AND 
			s.active = 1 
			AND 
			s.site_id = ', siteId, 
		' ORDER BY s.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);
		
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS GetUserFollowersInList//
CREATE PROCEDURE GetUserFollowersInList(IN siteId INT, IN userId INT, IN listId INT)
BEGIN
	SELECT id, username, avatar, details 
	FROM user 
	WHERE 
		id IN (SELECT user_id FROM friend WHERE user_is_following_id = userId AND active = 1) 
		AND 
		id IN (SELECT user_id FROM list_user WHERE list_id = listId AND active = 1) 
		AND 
		active = 1 
	ORDER BY username ASC;
END//





/* STORED PROCEDURE EDITS */
DROP PROCEDURE IF EXISTS GetUserInfoByID//
CREATE PROCEDURE GetUserInfoByID(IN siteId INT, IN userId INT)
BEGIN
	SELECT 
		u.id, u.username, u.password, u.email, u.avatar, u.details, u.website, u.location, u.twitter_username, u.security_question, 
		u.security_answer, u.karma_points, u.suspended, u.banned, u.ban_reason, a.username AS banned_by_admin_username, 
		a.full_name AS banned_by_admin_full_name 
	FROM user u 
	LEFT OUTER JOIN admin_user a ON a.id = u.banned_by_admin_id 
	WHERE u.active = 1 AND u.id = userId;
END//

DROP PROCEDURE IF EXISTS GetUserInfoByIdentifier//
CREATE PROCEDURE GetUserInfoByIdentifier(IN siteId INT, IN identifier VARCHAR(255))
BEGIN
	SELECT 
		u.id, u.username, u.password, u.email, u.avatar, u.details, u.website, u.location, u.twitter_username, u.security_question, 
		u.security_answer, u.karma_points, u.suspended, u.banned, u.ban_reason, a.username AS banned_by_admin_username, 
		a.full_name AS banned_by_admin_full_name 
	FROM user u 
	LEFT OUTER JOIN admin_user a ON a.id = u.banned_by_admin_id 
	WHERE u.active = 1 AND (u.username = identifier OR u.email = identifier);
END//

DROP PROCEDURE IF EXISTS GetUserRecentActivityCount//
CREATE PROCEDURE GetUserRecentActivityCount(IN siteId INT, IN userId INT)
BEGIN
	DECLARE countFavoriteSubmissions INT;
	DECLARE countFavoriteComments INT;
	DECLARE countLikes INT;
	DECLARE countDislikes INT;
	DECLARE countComments INT;
	DECLARE countSubmissions INT;
	
	SELECT 
		COUNT(s.id) INTO countFavoriteSubmissions
	FROM submission s 
	INNER JOIN submission_favorite f ON f.submission_id = s.id 
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
	FROM comment c 
	INNER JOIN comment_favorite f ON f.comment_id = c.id 
	INNER JOIN submission s ON s.id = c.submission_id 
	INNER JOIN user u ON u.id = c.user_id 
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
	FROM comment 
	WHERE site_id = siteId AND user_id = userId AND active = 1;
	
	SELECT COUNT(id) INTO countSubmissions
	FROM submission 
	WHERE site_id = siteId AND submitted_by_user_id = userId AND active = 1;

	SELECT COUNT(submission_id) INTO countLikes 
	FROM submission_vote sv 
	INNER JOIN submission s ON s.id = sv.submission_id 
	WHERE 
		sv.user_id = userId 
		AND 
		sv.active = 1 
		AND 
		s.active = 1 
		AND 
		sv.direction = 1 
		AND 
		s.id NOT IN (SELECT id FROM submission WHERE submitted_by_user_id = userId) 
		AND s.site_id = siteId;

	SELECT COUNT(submission_id) INTO countDislikes 
	FROM submission_vote sv 
	INNER JOIN submission s ON s.id = sv.submission_id 
	WHERE 
		sv.user_id = userId 
		AND 
		sv.active = 1 
		AND 
		s.active = 1 
		AND 
		sv.direction = -1 
		AND 
		s.id NOT IN (SELECT id FROM submission WHERE submitted_by_user_id = userId) 
		AND s.site_id = siteId;
	
	SELECT (countFavoriteSubmissions + countFavoriteComments + countComments + countSubmissions + countLikes + countDislikes) AS num_activities;
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
			\'\' AS comment_id, 
			\'\' AS comment_body,
			\'\' AS comment_username, 
			f.date_created AS activity_date 
		FROM submission s 
		INNER JOIN submission_favorite f ON f.submission_id = s.id 
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
			c.id AS comment_id, 
			c.body AS comment_body,
			u.username AS comment_username,
			f.date_created AS activity_date
		FROM comment c 
		INNER JOIN comment_favorite f ON f.comment_id = c.id 
		INNER JOIN submission s ON s.id = c.submission_id 
		INNER JOIN user u ON u.id = c.user_id 
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
			c.id AS comment_id, 
			c.body AS comment_body, 
			\'\' AS comment_username, 
			c.date_created AS activity_date
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id 
		INNER JOIN submission s ON s.id = c.submission_id
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
			\'\' AS comment_id, 
			\'\' AS comment_body, 
			\'\' AS comment_username, 
			s.date_created AS activity_date
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
		WHERE 
			s.submitted_by_user_id = ', userId, ' 
			AND 
			s.active = 1 
			AND 
			s.site_id = ', siteId, ' 
		UNION
		SELECT 
			\'like\' AS activity_type,
			\'\' AS activity_sub_type, 
			s.id AS submission_id, 
			s.type AS submission_type, 
			s.title AS submission_title, 
			s.summary AS submission_summary, 
			s.url AS submission_url, 
			\'\' AS comment_id, 
			\'\' AS comment_body, 
			\'\' AS comment_username, 
			sv.date_created AS activity_date
		FROM submission_vote sv 
		INNER JOIN submission s ON s.id = sv.submission_id 
		WHERE 
			sv.user_id = ', userId, ' 
			AND 
			sv.active = 1 
			AND 
			s.active = 1 
			AND 
			sv.direction = 1 
			AND 
			s.id NOT IN (SELECT id FROM submission WHERE submitted_by_user_id = ', userId, ') 
			AND s.site_id = ', siteId, ' 
		UNION  
		SELECT 
			\'dislike\' AS activity_type,
			\'\' AS activity_sub_type, 
			s.id AS submission_id, 
			s.type AS submission_type, 
			s.title AS submission_title, 
			s.summary AS submission_summary, 
			s.url AS submission_url, 
			\'\' AS comment_id, 
			\'\' AS comment_body, 
			\'\' AS comment_username, 
			sv.date_created AS activity_date
		FROM submission_vote sv 
		INNER JOIN submission s ON s.id = sv.submission_id 
		WHERE 
			sv.user_id = ', userId, ' 
			AND 
			sv.active = 1 
			AND 
			s.active = 1 
			AND 
			sv.direction = -1 
			AND 
			s.id NOT IN (SELECT id FROM submission WHERE submitted_by_user_id = ', userId, ') 
			AND s.site_id = ', siteId, ' 
		ORDER BY activity_date DESC LIMIT ', selectOffset , ', ', selectLimit
		);
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS GetUserSettings//
CREATE PROCEDURE GetUserSettings(IN siteId INT, IN userId INT)
BEGIN
	SELECT start_page, start_page_title, alert_comments, alert_shares, alert_messages, alert_followers, alert_favorites, open_links_in, subscribe_on_submit, 
		subscribe_on_comment, comment_threshold, prepopulate_reply, publicly_display_likes  
	FROM user_settings 
	WHERE user_id = userId AND active = 1;
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
			s.summary AS submission_summary, 
			f.date_created AS favorite_date, 
			\'\' AS comment_username, 
			\'\' AS comment_id,
			\'\' AS comment_body 
		FROM submission s 
		INNER JOIN submission_favorite f ON f.submission_id = s.id 
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
			\'\' AS submission_summary, 
			f.date_created AS favorite_date, 
			u.username AS comment_username, 
			c.id AS comment_id, 
			c.body AS comment_body 
		FROM comment c 
		INNER JOIN comment_favorite f ON f.comment_id = c.id 
		INNER JOIN submission s ON s.id = c.submission_id 
		INNER JOIN user u ON u.id = c.user_id 
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

DROP PROCEDURE IF EXISTS GetUserRandomFollowers//
CREATE PROCEDURE GetUserRandomFollowers(IN siteId INT, IN userId INT)
BEGIN
	SELECT id, username, avatar
	FROM user 
	WHERE 
		id IN (SELECT user_id FROM friend WHERE user_is_following_id = userId AND active = 1) 
		AND 
		active = 1 
	ORDER BY RAND() 
	LIMIT 0, 10;
END//

DROP PROCEDURE IF EXISTS GetUserRandomFollowing//
CREATE PROCEDURE GetUserRandomFollowing(IN siteId INT, IN userId INT)
BEGIN
	SELECT id, username, avatar 
	FROM user 
	WHERE 
		id IN (SELECT user_is_following_id FROM friend WHERE user_id = userId AND active = 1) 
		AND 
		active = 1 
	ORDER BY RAND() 
	LIMIT 0,10;
END//

DROP PROCEDURE IF EXISTS UpdateUserProfile//
CREATE PROCEDURE UpdateUserProfile(IN siteId INT, IN userId INT, IN userDetails TEXT, IN userEmail VARCHAR(255), IN userWebsite VARCHAR(255), IN userLocation VARCHAR(255), IN twitterUsername VARCHAR(255))
BEGIN
	UPDATE user SET 
		details = userDetails, 
		email = userEmail, 
		website = userWebsite, 
		location = userLocation, 
		twitter_username = twitterUsername 
	WHERE 
		id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserSettings//
CREATE PROCEDURE GetUserSettings(IN siteId INT, IN userId INT)
BEGIN
	SELECT start_page, start_page_title, alert_comments, alert_shares, alert_messages, alert_followers, alert_favorites, open_links_in, subscribe_on_submit, 
		subscribe_on_comment, comment_threshold, prepopulate_reply, publicly_display_likes  
	FROM user_settings 
	WHERE user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS UpdateUserSettings//
CREATE PROCEDURE UpdateUserSettings(IN siteId INT, IN userId INT, IN openLinksIn VARCHAR(10), IN subscribeSubmit TINYINT, IN subscribeComment TINYINT, IN commentThreshold INT, IN prepopulateReply TINYINT, IN publiclyDisplayLikes TINYINT)
BEGIN
	UPDATE user_settings SET 
		open_links_in = openLinksIn, 
		subscribe_on_submit = subscribeSubmit, 
		subscribe_on_comment = subscribeComment, 
		comment_threshold = commentThreshold, 
		prepopulate_reply = prepopulateReply, 
		publicly_display_likes = publiclyDisplayLikes 
	WHERE user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS RSSGetAllSubmissions//
CREATE PROCEDURE RSSGetAllSubmissions(IN siteId INT, IN isPopular VARCHAR(5), IN submissionType VARCHAR(10))
BEGIN
	DECLARE popularClause VARCHAR(255);
	DECLARE typeClause VARCHAR(255);
	DECLARE sortField VARCHAR(15);

	IF isPopular = 'all' THEN
		SET popularClause = '';
		SET sortField = 'date_created';
	ELSE
		IF isPopular = 'yes' THEN
			SET popularClause = ' AND popular = 1 ';
			SET sortField = 'popular_date';
		ELSE
			SET popularClause = ' AND popular = 0 ';
			SET sortField = 'date_created';
		END IF;
	END IF;
	
	IF submissionType = 'all' THEN
		SET typeClause = '';
	ELSE
		SET typeClause = concat(' AND type = \'', submissionType, '\' ');
	END IF;

	SET @sql = concat('
		SELECT 
			id, type, title, summary 
		FROM submission 
		WHERE 
			site_id = ', siteId, 
			popularClause, 
			typeClause, 
			' AND group_submission = 0 AND active = 1 
		ORDER BY ', sortField, ' DESC LIMIT 0, 10');
		
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS RSSGetSubmissionsForCategory//
CREATE PROCEDURE RSSGetSubmissionsForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN submissionType VARCHAR(10), IN isPopular VARCHAR(5))
BEGIN
	DECLARE popularClause VARCHAR(255);
	DECLARE typeClause VARCHAR(255);
	DECLARE sortField VARCHAR(15);
	DECLARE categoryClause VARCHAR(300);

	IF isPopular = 'all' THEN
		SET popularClause = '';
		SET sortField = 'date_created';
	ELSE
		IF isPopular = 'yes' THEN
			SET popularClause = ' AND popular = 1 ';
			SET sortField = 'popular_date';
		ELSE
			SET popularClause = ' AND popular = 0 ';
			SET sortField = 'date_created';
		END IF;
	END IF;
	
	IF submissionType = 'all' THEN
		SET typeClause = '';
	ELSE
		SET typeClause = concat(' AND type = \'', submissionType, '\' ');
	END IF;

	SET categoryClause = concat(' AND id IN (SELECT submission_id FROM submission_category WHERE category_id = (SELECT id FROM category WHERE site_id = ', siteId, ' AND url_name = \'', categoryUrlName, '\' AND active = 1)) ');

	SET @sql = concat('
		SELECT 
			id, type, title, summary 
		FROM submission 
		WHERE 
			site_id = ', siteId, categoryClause, popularClause, typeClause, ' AND group_submission = 0 AND active = 1 
		ORDER BY ', sortField, ' DESC LIMIT 0, 10');

	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS RSSGetSubmissionsForTag//
CREATE PROCEDURE RSSGetSubmissionsForTag(IN siteId INT, IN tagUrlName VARCHAR(35), IN submissionType VARCHAR(10), IN isPopular VARCHAR(5))
BEGIN
	DECLARE popularClause VARCHAR(255);
	DECLARE typeClause VARCHAR(255);
	DECLARE sortField VARCHAR(15);
	DECLARE tagClause VARCHAR(300);

	IF isPopular = 'all' THEN
		SET popularClause = '';
		SET sortField = 'date_created';
	ELSE
		IF isPopular = 'yes' THEN
			SET popularClause = ' AND popular = 1 ';
			SET sortField = 'popular_date';
		ELSE
			SET popularClause = ' AND popular = 0 ';
			SET sortField = 'date_created';
		END IF;
	END IF;
	
	IF submissionType = 'all' THEN
		SET typeClause = '';
	ELSE
		SET typeClause = concat(' AND type = \'', submissionType, '\' ');
	END IF;

	SET tagClause = concat(' AND id IN (SELECT submission_id FROM submission_tag WHERE tag_id = (SELECT id FROM tag WHERE site_id = ', siteId, ' AND url_name = \'', tagUrlName, '\' AND active = 1)) ');

	SET @sql = concat('
		SELECT 
			id, type, title, summary 
		FROM submission 
		WHERE 
			site_id = ', siteId, tagClause, popularClause, typeClause, ' AND group_submission = 0 AND active = 1 
		ORDER BY ', sortField, ' DESC LIMIT 0, 10');
	
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS RSSGetSubmissionsForUser//
CREATE PROCEDURE RSSGetSubmissionsForUser(IN siteId INT, IN name VARCHAR(25))
BEGIN
	SELECT 
		id, type, title, summary 
	FROM submission 
	WHERE 
		site_id = siteId 
		AND submitted_by_user_id = (SELECT id FROM user WHERE site_id = siteId AND username = name AND active = 1) 
		AND active = 1 
		AND group_submission = 0 
	ORDER BY date_created DESC LIMIT 0, 10;
END//

DROP PROCEDURE IF EXISTS DeleteCommentsForSubmission//
CREATE PROCEDURE DeleteCommentsForSubmission(IN siteId INT, IN submissionId INT)
BEGIN
	UPDATE comment SET active = 0 WHERE site_id = siteId AND submission_id = submissionId;
	
	UPDATE comment_favorite SET 
		active = 0 
	WHERE 
		site_id = siteId 
		AND comment_id IN (SELECT id FROM comment WHERE site_id = siteId AND submission_id = submission_id);
	
	UPDATE comment_reply SET 
		active = 0 
	WHERE 
		site_id = siteId 
		AND (
				comment_id IN (SELECT id FROM comment WHERE site_id = siteId AND submission_id = submission_id) 
				OR 
				comment_replied_to_id IN (SELECT id FROM comment WHERE site_id = siteId AND submission_id = submission_id)
			);
END//

DROP PROCEDURE IF EXISTS GetSearchCount//
CREATE PROCEDURE GetSearchCount(IN siteId INT, IN typeClause VARCHAR(100), IN isPopular VARCHAR(10), IN searchClause TEXT, IN loggedInUserId INT)
BEGIN
	DECLARE blockedClause VARCHAR(255);
	DECLARE popularClause VARCHAR(255);
	
	IF loggedInUserId IS NULL THEN
		SET blockedClause = '';
	ELSE
		SET blockedClause = concat('AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = ', siteId, ' AND user_id = ', loggedInUserId, ' AND active = 1)');
	END IF;
	
	IF isPopular IS NOT NULL THEN
		IF isPopular = 'yes' THEN
			SET popularClause = "AND popular = 1";
		ELSE
			SET popularClause = "AND popular = 0";
		END IF;
	ELSE
		SET popularClause = '';
	END IF;

	SET @sql = concat('
		SELECT COUNT(s.id) as num_subs 
		FROM submission s 
		WHERE ', 
			searchClause, 
			' ',
			typeClause, 
			' ', 
			blockedClause, 
			' ', 
			popularClause, ' 
			AND 
			s.active = 1 
			AND 
			s.site_id = ', siteId);

	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//


/* DELETED STORED PROCEDURES */


/* MISCELLANEOUS */
DELETE FROM submission_category WHERE category_id IN (SELECT id FROM category WHERE url_name = 'popular');
DELETE FROM category WHERE url_name = 'popular';





