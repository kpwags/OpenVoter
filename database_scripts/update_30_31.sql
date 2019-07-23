ALTER TABLE site ADD COLUMN comment_modify_time INT NOT NULL DEFAULT 15 AFTER comment_threshold;
ALTER TABLE site ADD COLUMN karma_penalties TINYINT(3) NOT NULL DEFAULT 0 AFTER show_voting_buttons_user_profile;
ALTER TABLE site ADD COLUMN karma_penalty_1_threshold INT NOT NULL DEFAULT -50 AFTER karma_penalties;
ALTER TABLE site ADD COLUMN karma_penalty_1_comments INT NOT NULL DEFAULT 5 AFTER karma_penalty_1_threshold;
ALTER TABLE site ADD COLUMN karma_penalty_1_submissions INT NOT NULL DEFAULT 5 AFTER karma_penalty_1_comments;
ALTER TABLE site ADD COLUMN karma_penalty_2_threshold INT NOT NULL DEFAULT -100 AFTER karma_penalty_1_submissions;
ALTER TABLE site ADD COLUMN karma_penalty_2_comments INT NOT NULL DEFAULT 5 AFTER karma_penalty_2_threshold;
ALTER TABLE site ADD COLUMN karma_penalty_2_submissions INT NOT NULL DEFAULT 5 AFTER karma_penalty_2_comments;
ALTER TABLE comment CHANGE active active TINYINT(3) NOT NULL DEFAULT 1;
ALTER TABLE comment ADD COLUMN edited TINYINT(3) NOT NULL DEFAULT 0 AFTER score;
ALTER TABLE comment ADD COLUMN deleted_by_user TINYINT(3) NOT NULL DEFAULT 1 AFTER edited;

DROP PROCEDURE IF EXISTS GetSettings//
CREATE PROCEDURE GetSettings(IN siteId INT)
BEGIN
	SELECT 
		root_url, mobile_root_url, title, theme, use_header_image, header_image, favicon, email_new_report, auto_report_keywords,
		blog, error_page, use_karma_system, karma_name, points_submission, points_comment, points_vote, points_popular, default_avatar, 
		default_photo_thumbnail, default_video_thumbnail, algorithm, threshold, comment_modify_time, pagination, show_votes, 
		show_down_votes, friends_page_enabled, top_ten_page_enabled, show_voting_buttons_friends_page, show_voting_buttons_user_profile, enable_recaptcha, 
		recaptcha_private_key, recaptcha_public_key, recaptcha_theme, about_site, privacy_policy, terms_of_use, site_help, 
		google_analytics_code, version 
	FROM site 
	WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetCommentCount//
CREATE PROCEDURE GetCommentCount(IN submissionId INT)
BEGIN
	SELECT COUNT(id) as num_comments FROM comment WHERE submission_id = submissionId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetComments//
CREATE PROCEDURE GetComments(IN siteId INT, IN submissionId INT, IN loggedInUserId INT)
BEGIN
	IF loggedInUserId IS NULL THEN
		SELECT 
			c.id AS comment_id, c.body, c.score, c.date_created, u.id AS user_id, u.username, u.avatar, c.active, 0 AS is_blocked, c.edited, c.deleted_by_user 
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id 
		WHERE 
			c.submission_id = submissionId 
			AND 
			c.id NOT IN (SELECT comment_id FROM comment_reply WHERE active = 1)
		ORDER BY c.date_created;
	ELSE
		SELECT 
			c.id AS comment_id, 
			c.body, 
			c.score, 
			c.date_created, 
			u.id AS user_id, 
			u.username, 
			u.avatar, 
			c.active, 
			(SELECT COUNT(user_id) FROM blocked_user WHERE user_id = loggedInUserId AND user_is_blocking_id = u.id AND active = 1) AS is_blocked, 
			c.edited, 
			c.deleted_by_user
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id 
		WHERE 
			c.submission_id = submissionId
			AND 
			c.id NOT IN (SELECT comment_id FROM comment_reply WHERE active = 1)
		ORDER BY c.date_created;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetCommentReplies//
CREATE PROCEDURE GetCommentReplies(IN siteId INT, IN commentId INT, IN loggedInUserId INT)
BEGIN
	IF loggedInUserId IS NULL THEN
		SELECT 
			c.id AS comment_id, c.body, c.score, c.date_created, u.id AS user_id, u.username, u.avatar, c.active, 0 AS is_blocked, c.edited, c.deleted_by_user 
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id 
		WHERE 
			c.id IN (SELECT comment_id FROM comment_reply WHERE active = 1 AND comment_replied_to_id = commentId) 
		ORDER BY c.date_created;
	ELSE
		SELECT 
			c.id AS comment_id, 
			c.body, 
			c.score, 
			c.date_created, 
			u.id AS user_id, 
			u.username, 
			u.avatar, 
			c.active,
			(SELECT COUNT(user_id) FROM blocked_user WHERE user_id = loggedInUserId AND user_is_blocking_id = u.id AND active = 1) AS is_blocked, 
			c.edited, 
			c.deleted_by_user
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id 
		WHERE 
			c.id IN (SELECT comment_id FROM comment_reply WHERE active = 1 AND comment_replied_to_id = commentId) 
		ORDER BY c.date_created;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetCommentDetails//
CREATE PROCEDURE GetCommentDetails(IN siteId INT, IN commentId INT, IN loggedInUserId INT)
BEGIN
	IF loggedInUserId IS NULL THEN
		SELECT 
			c.id AS comment_id, c.body, c.score, c.date_created, u.id AS user_id, u.username, u.avatar, c.active, 0 AS is_blocked, c.edited, c.deleted_by_user 
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id
		WHERE c.id = commentId;
	ELSE
		SELECT 
			c.id AS comment_id, 
			c.body, 
			c.score, 
			c.date_created, 
			u.id AS user_id, 
			u.username, 
			u.avatar, 
			c.active,
			(SELECT COUNT(user_id) FROM blocked_user WHERE user_id = loggedInUserId AND user_is_blocking_id = u.id AND active = 1) AS is_blocked, 
			c.edited,
			c.deleted_by_user  
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id
		WHERE c.id = commentId;
	END IF;
END//

DROP PROCEDURE IF EXISTS DeleteComment//
CREATE PROCEDURE DeleteComment(IN siteId INT, IN commentId INT, IN deletedByUser TINYINT)
BEGIN
	DECLARE userId INT;
	DECLARE pointsComment DECIMAL(3,2);
	
	SELECT 
		points_comment INTO pointsComment
	FROM site 
	WHERE id = siteId AND active = 1;
	
	SELECT user_id INTO userId FROM comment WHERE id = commentId;
	
	CALL AdjustUserKarma(userId, (-1 * pointsComment));

	UPDATE comment_favorite SET active = 0 WHERE comment_id = commentId;
	UPDATE comment SET active = 0, deleted_by_user = deletedByUser WHERE id = commentId;
END//

DROP PROCEDURE IF EXISTS EditComment//
CREATE PROCEDURE EditComment(IN commentId INT, IN commentBody TEXT)
BEGIN
	UPDATE comment SET body = commentBody, edited = 1 WHERE id = commentId;
END//

DROP PROCEDURE IF EXISTS GetCommentSettings//
CREATE PROCEDURE GetCommentSettings(IN siteId INT)
BEGIN
	SELECT comment_modify_time FROM site WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SaveCommentSettings//
CREATE PROCEDURE SaveCommentSettings(IN siteId INT, IN commentModifyTime INT)
BEGIN
	UPDATE site SET comment_modify_time = commentModifyTime WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetKarmaSettings//
CREATE PROCEDURE GetKarmaSettings(IN siteId INT)
BEGIN
	SELECT use_karma_system, karma_name, points_submission, points_comment, points_vote, 
		points_popular, points_comment_up_vote, points_comment_down_vote, top_ten_page_enabled, 
		karma_penalties, karma_penalty_1_threshold, karma_penalty_1_comments, karma_penalty_1_submissions, 
		karma_penalty_2_threshold, karma_penalty_2_comments, karma_penalty_2_submissions 
	FROM site WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SaveKarmaSettings//
CREATE PROCEDURE SaveKarmaSettings(IN siteId INT, IN useKarmaSystem TINYINT, IN karmaName VARCHAR(20), IN pointsSubmission DECIMAL(3,2), IN pointsComment DECIMAL(3,2), IN pointsVote DECIMAL(3,2), IN pointsPopular DECIMAL(3,2), IN pointsCommentUpVote DECIMAL(3,2), IN pointsCommentDownVote DECIMAL(3,2), IN topTenEnabled TINYINT, IN karmaPenalties TINYINT, IN karma1Threshold INT, IN karma1Submissions INT, IN karma1Comments INT, IN karma2Threshold INT, IN karma2Submissions INT, IN karma2Comments INT)
BEGIN
	UPDATE site SET 
		use_karma_system = useKarmaSystem,
		karma_name = karmaName,
		points_submission = pointsSubmission,
		points_comment = pointsComment,
		points_vote = pointsVote,
		points_popular = pointsPopular,
		points_comment_up_vote = pointsCommentUpVote,
		points_comment_down_vote = pointsCommentDownVote,
		top_ten_page_enabled = topTenEnabled, 
		karma_penalties = karmaPenalties,
		karma_penalty_1_threshold = karma1Threshold, 
		karma_penalty_1_submissions = karma1Submissions,
		karma_penalty_1_comments = karma1Comments, 
		karma_penalty_2_threshold = karma2Threshold, 
		karma_penalty_2_submissions = karma2Submissions,
		karma_penalty_2_comments = karma2Comments 
	WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS DeleteUser//
CREATE PROCEDURE DeleteUser(IN userId INT, IN byUser TINYINT)
BEGIN
	CALL DeleteUserFriends(userId);
	CALL DeleteUserBlockedUsers(userId);
	CALL DeleteUserCommentFavorites(userId);
	CALL DeleteUserComments(userId, byUser);
	CALL DeleteUserSubscriptions(userId);
	CALL DeleteUserSubmsissionFavorites(userId);
	CALL DeleteUserSubmsissionVotes(userId);
	CALL DeleteUserSubmissions(userId);
	CALL DeleteUserReports(userId);
	CALL DeleteUserAlerts(userId);
	CALL DeleteUserInfo(userId);
END//

DROP PROCEDURE IF EXISTS DeleteUserComments//
CREATE PROCEDURE DeleteUserComments(IN userId INT, byUser TINYINT)
BEGIN
	UPDATE comment SET active = 0, deleted_by_user = byUser WHERE user_id = userId;
		
	UPDATE comment_reply SET active = 0 WHERE comment_replied_to_id IN (SELECT id FROM comment WHERE user_id = userId);
END//

DROP PROCEDURE IF EXISTS BanUser//
CREATE PROCEDURE BanUser(IN userId INT, IN adminId INT, IN banReason VARCHAR(255))
BEGIN
	UPDATE user SET 
		banned = 1, 
		ban_reason = banReason,
		banned_by_admin_id = 1 
	WHERE id = userId;
	
	CALL DeleteUser(userId, 0);
	
	UPDATE user_ip_address SET banned = 1 WHERE user_id = userId;
END//

/* 3.1 Alpha 2 */

ALTER TABLE submission CHANGE summary summary TEXT NULL;
ALTER TABLE user_settings ADD COLUMN comment_threshold INT NOT NULL DEFAULT -2 AFTER hide_blocked_submissions;

DROP PROCEDURE IF EXISTS CanUserPostComment//
CREATE PROCEDURE CanUserPostComment(IN siteId INT, IN userId INT)
BEGIN
	DECLARE userKarma DECIMAL(20,2);
	DECLARE karmaPenalties TINYINT;
	DECLARE karmaThreshold1 INT;
	DECLARE karmaThreshold2 INT;
	DECLARE karma1Comments INT;
	DECLARE karma2Comments INT;
	DECLARE userCommentCount INT;
	
	SELECT karma_penalties INTO karmaPenalties FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_penalty_1_threshold INTO karmaThreshold1 FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_penalty_2_threshold INTO karmaThreshold2 FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_penalty_1_comments INTO karma1Comments FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_penalty_2_comments INTO karma2Comments FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_points INTO userKarma FROM user WHERE id = userId LIMIT 0,1;
	SELECT COUNT(id) INTO userCommentCount FROM comment WHERE user_id = userId AND date_created >= DATE_SUB(NOW(),INTERVAL 1 DAY);
	
	IF karmaPenalties = 1 THEN
		IF userKarma < karmaThreshold1 THEN
			IF userKarma < karmaThreshold2 THEN
				IF userCommentCount < karma2Comments THEN
					SELECT 'YES' AS can_post;
				ELSE
					SELECT 'NO' AS can_post;
				END IF;
			ELSE
				IF userCommentCount < karma1Comments THEN
					SELECT 'YES' AS can_post;
				ELSE
					SELECT 'NO' AS can_post;
				END IF;
			END IF;
		ELSE
			SELECT 'YES' AS can_post;
		END IF;
	ELSE
		SELECT 'YES' AS can_post;
	END IF;
END//

DROP PROCEDURE IF EXISTS CanUserPostSubmission//
CREATE PROCEDURE CanUserPostSubmission(IN siteId INT, IN userId INT)
BEGIN
	DECLARE userKarma DECIMAL(20,2);
	DECLARE karmaPenalties TINYINT;
	DECLARE karmaThreshold1 INT;
	DECLARE karmaThreshold2 INT;
	DECLARE karma1Submissions INT;
	DECLARE karma2Submissions INT;
	DECLARE userSubmissionCount INT;
	
	SELECT karma_penalties INTO karmaPenalties FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_penalty_1_threshold INTO karmaThreshold1 FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_penalty_2_threshold INTO karmaThreshold2 FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_penalty_1_submissions INTO karma1Submissions FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_penalty_2_submissions INTO karma2Submissions FROM site WHERE id = siteId LIMIT 0,1;
	SELECT karma_points INTO userKarma FROM user WHERE id = userId LIMIT 0,1;
	SELECT COUNT(id) INTO userSubmissionCount FROM submission WHERE submitted_by_user_id = userId AND date_created >= DATE_SUB(NOW(),INTERVAL 1 DAY);
	
	IF karmaPenalties = 1 THEN
		IF userKarma < karmaThreshold1 THEN
			IF userKarma < karmaThreshold2 THEN
				IF userSubmissionCount < karma2Submissions THEN
					SELECT 'YES' AS can_post;
				ELSE
					SELECT 'NO' AS can_post;
				END IF;
			ELSE
				IF userSubmissionCount < karma1Submissions THEN
					SELECT 'YES' AS can_post;
				ELSE
					SELECT 'NO' AS can_post;
				END IF;
			END IF;
		ELSE
			SELECT 'YES' AS can_post;
		END IF;
	ELSE
		SELECT 'YES' AS can_post;
	END IF;
END//

DROP PROCEDURE IF EXISTS AddSubmission//
CREATE PROCEDURE AddSubmission(IN siteId INT, IN userId INT, IN subType VARCHAR(10), IN subTitle VARCHAR(255), IN subSummary TEXT, IN subUrl VARCHAR(255))
BEGIN
	DECLARE submissionId INT;
	DECLARE pointsSubmission DECIMAL(3,2);
	
	SELECT 
		points_submission INTO pointsSubmission
	FROM site 
	WHERE id = siteId AND active = 1;

	INSERT INTO submission (site_id, submitted_by_user_id, type, title, summary, url) 
	VALUES(siteId, userId, subType, subTitle, subSummary, subUrl);
	
	SELECT id INTO submissionId FROM submission WHERE title = subTitle AND summary = subSummary AND url = subUrl AND submitted_by_user_id = userId AND active = 1 AND site_id = siteId LIMIT 0,1;
	
	INSERT INTO search (site_id, submission_id, title, summary) VALUES (siteId, submissionId, subTitle, subSummary);
	
	CALL AdjustUserKarma(userId, pointsSubmission);
	
	SELECT submissionId AS id;
END//

DROP PROCEDURE IF EXISTS GetUserSettings//
CREATE PROCEDURE GetUserSettings(IN siteId INT, IN userId INT)
BEGIN
	SELECT start_page, start_page_title, alert_comments, alert_shares, alert_messages, alert_followers, alert_favorites, open_links_in, subscribe_on_submit, 
		subscribe_on_comment, comment_threshold  
	FROM user_settings 
	WHERE user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS UpdateUserSettings//
CREATE PROCEDURE UpdateUserSettings(IN siteId INT, IN userId INT, IN openLinksIn VARCHAR(10), IN subscribeSubmit TINYINT, IN subscribeComment TINYINT, IN commentThreshold INT)
BEGIN
	UPDATE user_settings SET open_links_in = openLinksIn, subscribe_on_submit = subscribeSubmit, subscribe_on_comment = subscribeComment, comment_threshold = commentThreshold WHERE user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS ContentGetUserCount//
CREATE PROCEDURE ContentGetUserCount(IN siteId INT, IN userFilter VARCHAR(255))
BEGIN
	IF userFilter == '' THEN
		SELECT COUNT(id) AS num_users FROM user WHERE site_id = siteId AND active = 1;
	ELSE
		SET @sql = concat('
			SELECT COUNT(id) AS num_users FROM user WHERE site_id = ', siteId, ' AND username LIKE \'%', userFilter, '%\' AND active = 1');

		PREPARE stmt FROM @sql;
		EXECUTE stmt;
	END IF;
END//

DROP PROCEDURE IF EXISTS ContentGetUsers//
CREATE PROCEDURE ContentGetUsers(IN siteId INT, IN selectOffset INT, IN selectLimit INT, IN userFilter VARCHAR(255))
BEGIN
	IF userFilter == '' THEN
		SET @sql = concat('
			SELECT id, username, suspended, date_FORMAT(suspended_date, \'%c/%e/%Y %h:%i %p\') AS date_suspended 
			FROM user 
			WHERE site_id = ', siteId, ' AND active = 1 
			ORDER BY username ASC LIMIT ', selectOffset , ', ', selectLimit);
	ELSE
		SET @sql = concat('
			SELECT id, username, suspended, date_FORMAT(suspended_date, \'%c/%e/%Y %h:%i %p\') AS date_suspended 
			FROM user 
			WHERE site_id = ', siteId, ' AND username LIKE \'%', userFilter, '%\' AND active = 1 
			ORDER BY username ASC LIMIT ', selectOffset , ', ', selectLimit);
	END IF;

	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS GetUserIPAddresses//
CREATE PROCEDURE GetUserIPAddresses(IN userId INT)
BEGIN
	SELECT ip_address FROM user_ip_address WHERE user_id = userId;
END//

/* 3.1 BETA 1 */
ALTER TABLE feedback ADD COLUMN unread TINYINT(3) NOT NULL DEFAULT 1 AFTER message;

CREATE TABLE restricted_domain (
	id INT NOT NULL AUTO_INCREMENT,
	site_id INT NOT NULL,
	domain_name VARCHAR(255) NOT NULL,
	reason VARCHAR(255) NULL,
	active BIT NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY fk_restricted_domain_site_id (site_id),
	CONSTRAINT fk_restricted_domain_site_id FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP PROCEDURE IF EXISTS GetRestrictedDomains//
CREATE PROCEDURE GetRestrictedDomains(IN siteId INT)
BEGIN
	SELECT id, domain_name, reason FROM restricted_domain WHERE site_id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS UnrestrictDomain//
CREATE PROCEDURE UnrestrictDomain(IN siteId INT, IN domainId INT)
BEGIN 
	UPDATE restricted_domain SET active = 0 WHERE site_id = siteId AND id = domainId;
END//

DROP PROCEDURE IF EXISTS AddRestrictedDomain//
CREATE PROCEDURE AddRestrictedDomain(IN siteId INT, IN domainName VARCHAR(255), IN banReason VARCHAR(255))
BEGIN
	INSERT INTO restricted_domain (site_id, domain_name, reason) VALUES (siteId, domainName, banReason);
END//

DROP PROCEDURE IF EXISTS IsDomainRestricted//
CREATE PROCEDURE IsDomainRestricted(IN siteId INT, IN domainName VARCHAR(255))
BEGIN
	SELECT id FROM restricted_domain WHERE site_id = siteId AND domain_name = domainName AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserCommentVotingRecord//
CREATE PROCEDURE GetUserCommentVotingRecord(IN siteId INT, IN userName VARCHAR(25), IN startDate VARCHAR(25), IN voteDirection TINYINT)
BEGIN
	IF startDate = '' THEN
		IF voteDirection = 0 THEN
			SELECT 
				cv.direction, cv.date_created, u.username, s.id, s.type, s.title, c.id AS comment_id 
			FROM comment_vote cv 
			INNER JOIN comment c ON c.id = cv.comment_id 
			INNER JOIN user u ON u.id = c.user_id 
			INNER JOIN user vu ON vu.id = cv.user_id 
			INNER JOIN submission s ON s.id = c.submission_id 
			WHERE 
				c.site_id = siteId 
				AND 
				cv.active = 1 
				AND 
				vu.username = userName 
				AND 
				vu.username != u.username 
			ORDER BY cv.date_created DESC;
		ELSE
			SELECT 
				cv.direction, cv.date_created, u.username, s.id, s.type, s.title, c.id AS comment_id 
			FROM comment_vote cv 
			INNER JOIN comment c ON c.id = cv.comment_id 
			INNER JOIN user u ON u.id = c.user_id 
			INNER JOIN user vu ON vu.id = cv.user_id 
			INNER JOIN submission s ON s.id = c.submission_id 
			WHERE 
				c.site_id = siteId 
				AND 
				cv.active = 1 
				AND 
				vu.username = userName 
				AND 
				vu.username != u.username 
				AND 
				cv.direction = voteDirection 
			ORDER BY cv.date_created DESC;
		END IF;
	ELSE
		IF voteDirection = 0 THEN 
			SELECT 
				cv.direction, cv.date_created, u.username, s.id, s.type, s.title, c.id AS comment_id 
			FROM comment_vote cv 
			INNER JOIN comment c ON c.id = cv.comment_id 
			INNER JOIN user u ON u.id = c.user_id 
			INNER JOIN user vu ON vu.id = cv.user_id 
			INNER JOIN submission s ON s.id = c.submission_id 
			WHERE 
				c.site_id = siteId 
				AND 
				cv.active = 1 
				AND 
				vu.username = userName 
				AND 
				vu.username != u.username 
				AND 
				cv.date_created > startDate 
			ORDER BY cv.date_created DESC;
		ELSE
			SELECT 
				cv.direction, cv.date_created, u.username, s.id, s.type, s.title, c.id AS comment_id 
			FROM comment_vote cv 
			INNER JOIN comment c ON c.id = cv.comment_id 
			INNER JOIN user u ON u.id = c.user_id 
			INNER JOIN user vu ON vu.id = cv.user_id 
			INNER JOIN submission s ON s.id = c.submission_id 
			WHERE 
				c.site_id = siteId 
				AND 
				cv.active = 1 
				AND 
				vu.username = userName 
				AND 
				vu.username != u.username 
				AND 
				cv.date_created > startDate 
				AND 
				cv.direction = voteDirection
			ORDER BY cv.date_created DESC;
		END IF;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetUserSubmissionVotingRecord//
CREATE PROCEDURE GetUserSubmissionVotingRecord(IN siteId INT, IN userName VARCHAR(25), IN startDate VARCHAR(25), IN voteDirection TINYINT)
BEGIN
	IF startDate = '' THEN
		IF voteDirection = 0 THEN
			SELECT 
				sv.direction, sv.date_created, u.username, s.id, s.type, s.title 
			FROM submission_vote sv 
			INNER JOIN submission s ON s.id = sv.submission_id
			INNER JOIN user u ON u.id = s.submitted_by_user_id 
			INNER JOIN user su ON su.id = sv.user_id 
			WHERE 
				s.site_id = siteId 
				AND 
				sv.active = 1 
				AND 
				su.username = userName 
				AND 
				su.username != u.username 
			ORDER BY sv.date_created DESC;
		ELSE
			SELECT 
				sv.direction, sv.date_created, u.username, s.id, s.type, s.title 
			FROM submission_vote sv 
			INNER JOIN submission s ON s.id = sv.submission_id
			INNER JOIN user u ON u.id = s.submitted_by_user_id 
			INNER JOIN user su ON su.id = sv.user_id 
			WHERE 
				s.site_id = siteId 
				AND 
				sv.active = 1 
				AND 
				su.username = userName 
				AND 
				su.username != u.username 
				AND 
				sv.direction = voteDirection 
			ORDER BY sv.date_created DESC;
		END IF;
	ELSE
		IF voteDirection = 0 THEN 
			SELECT 
				sv.direction, sv.date_created, u.username, s.id, s.type, s.title 
			FROM submission_vote sv 
			INNER JOIN submission s ON s.id = sv.submission_id
			INNER JOIN user u ON u.id = s.submitted_by_user_id 
			INNER JOIN user su ON su.id = sv.user_id 
			WHERE 
				s.site_id = siteId 
				AND 
				sv.active = 1 
				AND 
				su.username = userName 
				AND 
				su.username != u.username 
				AND 
				sv.date_created > startDate 
			ORDER BY sv.date_created DESC;
		ELSE
			SELECT 
				sv.direction, sv.date_created, u.username, s.id, s.type, s.title 
			FROM submission_vote sv 
			INNER JOIN submission s ON s.id = sv.submission_id
			INNER JOIN user u ON u.id = s.submitted_by_user_id 
			INNER JOIN user su ON su.id = sv.user_id 
			WHERE 
				s.site_id = siteId 
				AND 
				sv.active = 1 
				AND 
				su.username = userName 
				AND 
				su.username != u.username 
				AND 
				sv.date_created > startDate 
				AND 
				sv.direction = voteDirection
			ORDER BY sv.date_created DESC;
		END IF;
	END IF;
END//

DROP PROCEDURE IF EXISTS MarkFeedbackRead//
CREATE PROCEDURE MarkFeedbackRead(IN feedbackId INT)
BEGIN
	UPDATE feedback SET unread = 0 WHERE id = feedbackId;
END//

DROP PROCEDURE IF EXISTS MarkFeedbackUnread//
CREATE PROCEDURE MarkFeedbackUnread(IN feedbackId INT)
BEGIN
	UPDATE feedback SET unread = 1 WHERE id = feedbackId;
END//

DROP PROCEDURE IF EXISTS GetFeedback//
CREATE PROCEDURE GetFeedback(IN siteId INT)
BEGIN
	SELECT id, name, email, reason, message, date_FORMAT(date_created, '%c/%e/%Y %h:%i %p') AS message_date, unread FROM feedback WHERE site_id = siteId AND active = 1 ORDER BY date_created DESC;
END//

DROP PROCEDURE IF EXISTS GetUnreadFeedbackCount//
CREATE PROCEDURE GetUnreadFeedbackCount(IN siteId INT)
BEGIN
	SELECT COUNT(id) AS num_messages FROM feedback WHERE site_id = siteId AND unread = 1 AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetReportCount//
CREATE PROCEDURE GetReportCount(IN siteId INT, IN reportType VARCHAR(25))
BEGIN
	IF reportType = 'ALL' THEN
		SELECT COUNT(id) AS num_reports FROM report_object WHERE site_id = siteId AND active = 1;
	ELSE
		SELECT COUNT(id) AS num_reports FROM report_object WHERE site_id = siteId AND object_type = reportType AND active = 1;
	END IF;
END//

DROP PROCEDURE IF EXISTS DeleteUserReports//
CREATE PROCEDURE DeleteUserReports(IN userId INT)
BEGIN
	UPDATE report SET active = 0 WHERE reporting_user_id = userId;
	UPDATE report_object SET active = 0 WHERE object_type = 'user' AND object_id = userId;
	UPDATE report_object SET active = 0 WHERE object_type = 'submission' AND object_id IN (SELECT id FROM submission WHERE submitted_by_user_id = userId);
	UPDATE report_object SET active = 0 WHERE object_type = 'comment' AND object_id IN (SELECT id FROM comment WHERE user_id = userId);
END//