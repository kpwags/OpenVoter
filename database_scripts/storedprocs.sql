DROP PROCEDURE IF EXISTS AddAdmin//
CREATE PROCEDURE AddAdmin(IN siteId INT, IN adminUsername VARCHAR(20), IN adminFullName VARCHAR(255), IN adminEmail VARCHAR(255), IN adminPassword VARCHAR(255), IN adminPasswordSalt VARCHAR(25), IN adminPasswordKey VARCHAR(25), IN adminRole INT)
BEGIN
	INSERT INTO admin_user (site_id, username, full_name, email, password, password_salt, password_key, role)
	VALUES (siteId, adminUsername, adminFullName, adminEmail, adminPassword, adminPasswordSalt, adminPasswordKey, adminRole);
END//

DROP PROCEDURE IF EXISTS AddBannedDomain//
CREATE PROCEDURE AddBannedDomain(IN siteId INT, IN domainName VARCHAR(255), IN banReason VARCHAR(255))
BEGIN
	INSERT INTO banned_domain (site_id, domain_name, reason) VALUES (siteId, domainName, banReason);
END//

DROP PROCEDURE IF EXISTS AddChildCategory//
CREATE PROCEDURE AddChildCategory(IN siteId INT, IN categoryName VARCHAR(35), IN urlName VARCHAR(35), IN sortOrder INT, IN parentCategoryId INT)
BEGIN
	DECLARE categoryId INT;

	INSERT INTO category (site_id, name, url_name, sort_order) VALUES (siteId, categoryName, urlName, sortOrder);
	
	SELECT id INTO categoryId FROM category WHERE site_id = siteId AND name = categoryName AND url_name = urlName AND active = 1;
	
	INSERT INTO subcategory (site_id, parent_category_id, child_category_id) VALUES (siteId, parentCategoryId, categoryId);
END//

DROP PROCEDURE IF EXISTS AddCommentAlert//

CREATE PROCEDURE AddCommentAlert(IN siteId INT, IN alertUserId INT, IN commentUserId INT, IN submissionId INT, IN commentId INT)
BEGIN
	INSERT INTO alerts_comment (site_id, alert_user_id, comment_user_id, submission_id, comment_id) VALUES (siteId, alertUserId, commentUserId, submissionId, commentId);
END//

DROP PROCEDURE IF EXISTS AddCommentFavorite//
CREATE PROCEDURE AddCommentFavorite(IN siteId INT, IN commentId INT, IN userId INT)
BEGIN
	IF ((SELECT COUNT(comment_id) FROM comment_favorite WHERE site_id = siteId AND  comment_id = commentId AND user_id = userId) > 0) THEN
		UPDATE comment_favorite SET active = 1, date_created = CURRENT_TIMESTAMP WHERE site_id = siteId AND comment_id = commentId AND user_id = userId;
	ELSE
		INSERT INTO comment_favorite (comment_id, user_id, site_id) VALUES (commentId, userId, siteId);
	END IF;
END//

DROP PROCEDURE IF EXISTS AddCommentReport//
CREATE PROCEDURE AddCommentReport(IN siteId INT, IN commentId INT, IN reportReason VARCHAR(25), IN reportDetails VARCHAR(255), IN userId INT)
BEGIN
	DECLARE reportObjectId INT;
	
	IF ((SELECT COUNT(id) FROM report_object WHERE site_id = siteId AND object_type = 'comment' AND object_id = commentId AND active = 1) = 0) THEN
		INSERT INTO report_object (site_id, object_type, object_id) VALUES (siteId, 'comment', commentId);		
	END IF;
	
	SELECT id INTO reportObjectId FROM report_object WHERE site_id = siteId AND object_type = 'comment' AND object_id = commentId AND active = 1 LIMIT 0,1;
	
	IF ((SELECT COUNT(id) FROM report WHERE site_id = siteId AND reporting_user_id = userId AND report_object_id = reportObjectId AND active = 1) = 0) THEN 
		INSERT INTO report (site_id, reason, details, reporting_user_id, report_object_id) VALUES (siteId, reportReason, reportDetails, userId, reportObjectId);
		SELECT 'OK' AS report_result;
	ELSE
		SELECT 'REPEAT' AS report_result;
	END IF;
END//

DROP PROCEDURE IF EXISTS AddFavoriteAlert//
CREATE PROCEDURE AddFavoriteAlert(IN siteId INT, IN alertUserId INT, IN favoriteUserId INT, IN submissionId INT)
BEGIN
	INSERT INTO alerts_favorite (site_id, alert_user_id, favorite_user_id, submission_id) VALUES (siteId, alertUserId, favoriteUserId, submissionId);
END//

DROP PROCEDURE IF EXISTS AddFollowerAlert//
CREATE PROCEDURE AddFollowerAlert(IN siteId INT, IN alertUserId INT, IN followerUserId INT)
BEGIN
	INSERT INTO alerts_follower (site_id, alert_user_id, follower_user_id) VALUES (siteId, alertUserId, followerUserId);
END//

DROP PROCEDURE IF EXISTS AddIPAddress//
CREATE PROCEDURE AddIPAddress(IN siteId INT, IN userId INT, IN ipAddress VARCHAR(50))
BEGIN
	IF ((SELECT COUNT(user_id) FROM user_ip_address WHERE site_id = siteId AND user_id = userId AND ip_address = ipAddress) = 0) THEN
		INSERT INTO user_ip_address (site_id, user_id, ip_address) VALUES(siteId, userId, ipAddress);
	END IF;
END//

DROP PROCEDURE IF EXISTS AddLocationToSubmission//
CREATE PROCEDURE AddLocationToSubmission(IN siteId INT, IN submissionId INT, IN newLocation VARCHAR(255))
BEGIN
	UPDATE submission SET location = newLocation WHERE site_id = siteId AND id = submissionId AND active = 1;
END//

DROP PROCEDURE IF EXISTS AddParentCategory//
CREATE PROCEDURE AddParentCategory(IN siteId INT, IN categoryName VARCHAR(35), IN urlName VARCHAR(35), IN sortOrder INT)
BEGIN
	INSERT INTO category (site_id, name, url_name, sort_order) VALUES (siteId, categoryName, urlName, sortOrder);
END//

DROP PROCEDURE IF EXISTS AddRestrictedDomain//
CREATE PROCEDURE AddRestrictedDomain(IN siteId INT, IN domainName VARCHAR(255), IN banReason VARCHAR(255))
BEGIN
	INSERT INTO restricted_domain (site_id, domain_name, reason) VALUES (siteId, domainName, banReason);
END//

DROP PROCEDURE IF EXISTS AddShareAlert//
CREATE PROCEDURE AddShareAlert(IN siteId INT, IN alertUserId INT, IN shareUserId INT, IN submissionId INT, IN shareMessage VARCHAR(255))
BEGIN
	IF ((SELECT COUNT(id) FROM alerts_share WHERE site_id = siteId AND submission_id = submissionId AND alert_user_id = alertUserId AND share_user_id = shareUserId) = 0) THEN
		INSERT INTO alerts_share (site_id, alert_user_id, share_user_id, submission_id, message) VALUES (siteId, alertUserId, shareUserId, submissionId, shareMessage);
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

DROP PROCEDURE IF EXISTS AddSubmissionCategory//
CREATE PROCEDURE AddSubmissionCategory(IN siteId INT, IN submissionId INT, IN categoryId INT)
BEGIN
	INSERT INTO submission_category (site_id, submission_id, category_id) VALUES (siteId, submissionId, categoryId);
END//

DROP PROCEDURE IF EXISTS AddSubmissionFavorite//
CREATE PROCEDURE AddSubmissionFavorite(IN siteId INT, IN submissionId INT, IN userId INT)
BEGIN
	IF ((SELECT COUNT(submission_id) FROM submission_favorite WHERE site_id = siteId AND  submission_id = submissionId AND user_id = userId) > 0) THEN
		UPDATE submission_favorite SET active = 1, date_created = CURRENT_TIMESTAMP WHERE site_id = siteId AND submission_id = submissionId AND user_id = userId;
	ELSE
		INSERT INTO submission_favorite (submission_id, user_id, site_id) VALUES (submissionId, userId, siteId);
	END IF;
END//

DROP PROCEDURE IF EXISTS AddSubmissionReport//
CREATE PROCEDURE AddSubmissionReport(IN siteId INT, IN submissionId INT, IN reportReason VARCHAR(25), IN reportDetails VARCHAR(255), IN userId INT)
BEGIN
	DECLARE reportObjectId INT;
	
	IF ((SELECT COUNT(id) FROM report_object WHERE site_id = siteId AND object_type = 'submission' AND object_id = submissionId AND active = 1) = 0) THEN
		INSERT INTO report_object (site_id, object_type, object_id) VALUES (siteId, 'submission', submissionId);		
	END IF;
	
	SELECT id INTO reportObjectId FROM report_object WHERE site_id = siteId AND object_type = 'submission' AND object_id = submissionId AND active = 1 LIMIT 0,1;
	
	IF ((SELECT COUNT(id) FROM report WHERE site_id = siteId AND reporting_user_id = userId AND report_object_id = reportObjectId AND active = 1) = 0) THEN 
		INSERT INTO report (site_id, reason, details, reporting_user_id, report_object_id) VALUES (siteId, reportReason, reportDetails, userId, reportObjectId);
		SELECT 'OK' AS report_result;
	ELSE
		SELECT 'REPEAT' AS report_result;
	END IF;
END//

DROP PROCEDURE IF EXISTS AddSubmissionTag//
CREATE PROCEDURE AddSubmissionTag(IN siteId INT, IN submissionId INT, IN tagId INT)
BEGIN
	INSERT INTO submission_tag (site_id, submission_id, tag_id) VALUES (siteId, submissionId, tagId);
END//

DROP PROCEDURE IF EXISTS AddTag//
CREATE PROCEDURE AddTag(IN siteId INT, IN tagName VARCHAR(255), IN tagUrlName VARCHAR(255))
BEGIN
	IF ((SELECT COUNT(id) FROM tag WHERE url_name = tagUrlName AND site_id = siteId AND active = 1) = 0) THEN
		INSERT INTO tag (site_id, name, url_name) VALUES (siteId, tagName, tagUrlName);
	END IF;
	
	SELECT id FROM tag WHERE url_name = tagUrlName AND site_id = siteId AND active = 1 LIMIT 0,1;
END//

DROP PROCEDURE IF EXISTS AddUser//
CREATE PROCEDURE AddUser(IN siteId INT, IN pUsername VARCHAR(20), IN pPassword VARCHAR(255), IN pPasswordSalt VARCHAR(25), IN pPasswordKey VARCHAR(25), IN pEmail VARCHAR(255), IN pSecQ VARCHAR(255), IN pSecA VARCHAR(255), IN pIP VARCHAR(25))
BEGIN
	DECLARE userId INT;

	INSERT INTO user (site_id, username, password, password_salt, password_key, email, security_question, security_answer, security_answer_salt, security_answer_key) 
		VALUES (siteId, pUsername, pPassword, pPasswordSalt, pPasswordKey, pEmail, pSecQ, pSecA, password_salt, password_key);
		
	SELECT id INTO userId FROM user WHERE email = pEmail AND active = 1 LIMIT 0,1;
	
	INSERT INTO user_settings (site_id, user_id) VALUES (siteId, userId);
	
	INSERT INTO user_ip_address (site_id, user_id, ip_address) VALUES (siteId, userId, pIP);
END//

DROP PROCEDURE IF EXISTS AddUserReport//
CREATE PROCEDURE AddUserReport(IN siteId INT, IN reportedUserId INT, IN reportReason VARCHAR(25), IN reportDetails VARCHAR(255), IN userId INT)
BEGIN
	DECLARE reportObjectId INT;
	
	IF ((SELECT COUNT(id) FROM report_object WHERE site_id = siteId AND object_type = 'user' AND object_id = reportedUserId AND active = 1) = 0) THEN
		INSERT INTO report_object (site_id, object_type, object_id) VALUES (siteId, 'user', reportedUserId);		
	END IF;
	
	SELECT id INTO reportObjectId FROM report_object WHERE site_id = siteId AND object_type = 'user' AND object_id = reportedUserId AND active = 1 LIMIT 0,1;
	
	IF ((SELECT COUNT(id) FROM report WHERE site_id = siteId AND reporting_user_id = userId AND report_object_id = reportObjectId AND active = 1) = 0) THEN 
		INSERT INTO report (site_id, reason, details, reporting_user_id, report_object_id) VALUES (siteId, reportReason, reportDetails, userId, reportObjectId);
		SELECT 'OK' AS report_result;
	ELSE
		SELECT 'REPEAT' AS report_result;
	END IF;
END//

DROP PROCEDURE IF EXISTS AdjustCommentScore//
CREATE PROCEDURE AdjustCommentScore(IN siteId INT, IN commentId INT, IN difference INT)
BEGIN
	UPDATE comment SET score = score + difference WHERE id = commentId AND active = 1;
END//

DROP PROCEDURE IF EXISTS AdjustSubmissionScore//
CREATE PROCEDURE AdjustSubmissionScore(IN siteId INT, IN submissionId INT, IN points INT)
BEGIN
	UPDATE submission SET score = score + points WHERE id = submissionId;
END//

DROP PROCEDURE IF EXISTS AdjustUserKarma//
CREATE PROCEDURE AdjustUserKarma(IN userId INT, IN pointsToAdjust DECIMAL(3,2))
BEGIN
	UPDATE user SET karma_points = karma_points + pointsToAdjust WHERE id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS AdminGetCategories//
CREATE PROCEDURE AdminGetCategories(IN siteId INT)
BEGIN
	SELECT 
		c.id, c.name, c.url_name, c.sort_order, 
		(SELECT COUNT(id) FROM category WHERE site_id = siteId AND id IN (SELECT child_category_id FROM subcategory WHERE site_id = siteId AND active = 1 AND parent_category_id = c.id) AND active = 1) AS num_subcategories 
	FROM category c
	WHERE 
		c.site_id = siteId AND 
		c.active = 1 AND 
		c.id NOT IN (SELECT child_category_id FROM subcategory WHERE site_id = siteId AND active = 1) 
	ORDER BY c.sort_order ASC;
END//

DROP PROCEDURE IF EXISTS AdminGetSubCategories//
CREATE PROCEDURE AdminGetSubCategories(IN siteId INT, IN categoryId INT)
BEGIN
	SELECT 
		id, name, url_name, sort_order 
	FROM category 
	WHERE 
		site_id = siteId AND 
		active = 1 AND 
		id IN (SELECT child_category_id FROM subcategory WHERE parent_category_id = categoryId AND site_id = siteId AND active = 1) 
	ORDER BY sort_order ASC;
END//

DROP PROCEDURE IF EXISTS BanIPAddress//
CREATE PROCEDURE BanIPAddress(IN siteId INT, IN ipAddress VARCHAR(50))
BEGIN
	UPDATE user_ip_address SET banned = 1 WHERE ip_address = ipAddress;
END//

DROP PROCEDURE IF EXISTS BanUser//
CREATE PROCEDURE BanUser(IN userId INT, IN adminId INT, IN banReason VARCHAR(255))
BEGIN
	UPDATE user SET 
		banned = 1, 
		ban_reason = banReason,
		banned_by_admin_id = adminId 
	WHERE id = userId;
	
	CALL DeleteUser(userId, 0);
	
	UPDATE user_ip_address SET banned = 1 WHERE user_id = userId;
END//

DROP PROCEDURE IF EXISTS BlockUser//
CREATE PROCEDURE BlockUser(IN siteId INT, IN userId INT, IN userToBlockId INT)
BEGIN
	IF ((SELECT COUNT(user_id) FROM blocked_user WHERE site_id = siteId AND user_id = userId AND user_is_blocking_id = userToBlockId) = 0) THEN
		INSERT INTO blocked_user (site_id, user_id, user_is_blocking_id) VALUES (siteId, userId, userToBlockId);
	ELSE
		UPDATE blocked_user SET active = 1 WHERE site_id = siteId AND user_id = userId AND user_is_blocking_id = userToBlockId;
	END IF;
	
	UPDATE friend SET active = 0 WHERE site_id = siteId AND user_id = userId AND user_is_following_id = userToBlockId;
	UPDATE friend SET active = 0 WHERE site_id = siteId AND user_id = userToBlockId AND user_is_following_id = userId;
END//

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

DROP PROCEDURE IF EXISTS CheckBannedDomain//
CREATE PROCEDURE CheckBannedDomain(IN siteId INT, IN domainName VARCHAR(255))
BEGIN
	SELECT domain_name, reason FROM banned_domain WHERE domain_name LIKE CONCAT('%', domainName, '%') AND active = 1;
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
	FROM site 
	WHERE id = siteId AND active = 1;
	
	SELECT 
		points_comment_vote_up INTO pointsCommentUp
	FROM site 
	WHERE id = siteId AND active = 1;
	
	SELECT 
		user_id INTO commentUser 
	FROM comment 
	WHERE id = commentId AND active = 1;	
	
	SELECT COUNT(comment_id) INTO voteCount FROM comment_vote WHERE user_id = userId AND comment_id = commentId;
	
	IF voteCount > 0 THEN
		SELECT direction INTO previousVote FROM comment_vote WHERE site_id = siteId AND user_id = userId AND comment_id = commentId;
	
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
			UPDATE comment_vote SET direction = voteDirection, active = 0 WHERE site_id = siteId AND user_id = userId AND comment_id = commentId;
		ELSE
			UPDATE comment_vote SET direction = voteDirection, active = 1 WHERE site_id = siteId AND user_id = userId AND comment_id = commentId;
		END IF;
	ELSE
		INSERT INTO comment_vote (site_id, user_id, comment_id, direction) VALUES(siteId, userId, commentId, voteDirection);
		IF commentUser != userId THEN
			IF voteDirection = 1 THEN
				CALL AdjustUserKarma(commentUser, pointsCommentUp);
			ELSE
				CALL AdjustUserKarma(commentUser, pointsCommentDown);
			END IF;
		END IF;
	END IF;
END//

DROP PROCEDURE IF EXISTS ContentEditSubmission//
CREATE PROCEDURE ContentEditSubmission(IN submissionId INT, IN submissionTitle VARCHAR(255), IN submissionSummary VARCHAR(255), IN submissionUrl VARCHAR(255))
BEGIN
	UPDATE submission SET 
		title = submissionTitle,
		summary = submissionSummary,
		url = submissionUrl 
	WHERE id = submissionId;
END//

DROP PROCEDURE IF EXISTS ContentGetCommentCount//
CREATE PROCEDURE ContentGetCommentCount(IN siteId INT)
BEGIN
	SELECT COUNT(id) AS num_comments FROM comment WHERE site_id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS ContentGetCommentDetails//
CREATE PROCEDURE ContentGetCommentDetails(IN commentId INT)
BEGIN
	SELECT 
		c.id, date_FORMAT(c.date_created, '%c/%e/%Y %h:%i %p') AS comment_date, c.body, u.id AS user_id, u.username, 
		s.id AS submission_id, s.type, s.title 
	FROM comment c 
	INNER JOIN user u ON u.id = c.user_id 
	INNER JOIN submission s ON s.id = c.submission_id 
	WHERE 
		c.id = commentId AND c.active = 1;
END//

DROP PROCEDURE IF EXISTS ContentGetComments//
CREATE PROCEDURE ContentGetComments(IN siteId INT, IN selectOffset INT, IN selectLimit INT)
BEGIN
	SET @sql = concat('
		SELECT c.id, date_FORMAT(c.date_created, \'%c/%e/%Y %h:%i %p\') AS comment_date, u.username, s.title 
		FROM comment c 
		INNER JOIN submission s ON s.id = c.submission_id  
		INNER JOIN user u ON u.id = c.user_id 
		WHERE c.site_id = ', siteId, ' AND c.active = 1 
		ORDER BY c.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);

	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS ContentGetSubmissionCount//
CREATE PROCEDURE ContentGetSubmissionCount(IN siteId INT)
BEGIN
	SELECT COUNT(id) AS num_submissions FROM submission WHERE site_id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS ContentGetSubmissionDetails//
CREATE PROCEDURE ContentGetSubmissionDetails(IN submissionId INT)
BEGIN
	SELECT 
		s.id, s.type, s.title, s.summary, s.score, date_FORMAT(s.date_created, '%c/%e/%Y %h:%i %p') AS submission_date, 
		s.url, s.popular, date_FORMAT(s.popular, '%c/%e/%Y %h:%i %p') AS popular_date, s.location, u.id AS user_id, u.username 
	FROM submission s 
	INNER JOIN user u ON u.id = s.submitted_by_user_id 
	WHERE 
		s.id = submissionId AND s.active = 1;
END//

DROP PROCEDURE IF EXISTS ContentGetSubmissions//
CREATE PROCEDURE ContentGetSubmissions(IN siteId INT, IN selectOffset INT, IN selectLimit INT)
BEGIN
	SET @sql = concat('
		SELECT s.id, s.title, date_FORMAT(s.date_created, \'%c/%e/%Y %h:%i %p\') AS submission_date, u.username 
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
		WHERE s.site_id = ', siteId, ' AND s.active = 1 
		ORDER BY s.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);

	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS ContentGetSuspendedUserCount//
CREATE PROCEDURE ContentGetSuspendedUserCount(IN siteId INT)
BEGIN
	SELECT COUNT(id) AS num_users FROM user WHERE site_id = siteId AND suspended = 1 AND active = 1;
END//

DROP PROCEDURE IF EXISTS ContentGetSuspendedUsers//
CREATE PROCEDURE ContentGetSuspendedUsers(IN siteId INT, IN selectOffset INT, IN selectLimit INT)
BEGIN
	SET @sql = concat('
		SELECT id, username, suspended, date_FORMAT(suspended_date, \'%c/%e/%Y %h:%i %p\') AS date_suspended 
		FROM user 
		WHERE site_id = ', siteId, ' AND active = 1 AND suspended = 1 
		ORDER BY username ASC LIMIT ', selectOffset , ', ', selectLimit);

	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS ContentGetUserCount//
CREATE PROCEDURE ContentGetUserCount(IN siteId INT, IN userFilter VARCHAR(255))
BEGIN
	IF userFilter = '' THEN
		SELECT COUNT(id) AS num_users FROM user WHERE site_id = siteId AND active = 1;
	ELSE
		SET @sql = concat('
			SELECT COUNT(id) AS num_users FROM user WHERE site_id = ', siteId, ' AND username LIKE \'%', userFilter, '%\' AND active = 1');

		PREPARE stmt FROM @sql;
		EXECUTE stmt;
	END IF;
END//

DROP PROCEDURE IF EXISTS ContentGetUserDetails//
CREATE PROCEDURE ContentGetUserDetails(IN userId INT)
BEGIN
	SELECT 
		id, date_FORMAT(date_created, '%c/%e/%Y %h:%i %p') AS join_date, username, email, avatar, details, 
		website, location, suspended, date_FORMAT(suspended_date, '%c/%e/%Y %h:%i %p') AS date_suspended 
	FROM user 
	WHERE id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS ContentGetUsers//
CREATE PROCEDURE ContentGetUsers(IN siteId INT, IN selectOffset INT, IN selectLimit INT, IN userFilter VARCHAR(255))
BEGIN
	IF userFilter = '' THEN
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

DROP PROCEDURE IF EXISTS DeleteAdmin//
CREATE PROCEDURE DeleteAdmin(IN adminId INT)
BEGIN
	UPDATE admin_user SET active = 0 WHERE id = adminId AND can_delete = 1;
END//

DROP PROCEDURE IF EXISTS DeleteCategory//
CREATE PROCEDURE DeleteCategory(IN siteId INT, IN categoryId INT)
BEGIN
	UPDATE category SET active = 0 WHERE site_id = siteId AND id = categoryId;
	
	UPDATE category SET active = 0 WHERE 
	site_id = siteId AND
	id IN (SELECT child_category_id FROM subcategory WHERE site_id = siteId AND parent_category_id = categoryId);
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

DROP PROCEDURE IF EXISTS DeleteCommentFavorite//
CREATE PROCEDURE DeleteCommentFavorite(IN siteId INT, IN commentId INT, IN userId INT)
BEGIN
	UPDATE comment_favorite SET active = 0 WHERE site_id = siteId AND comment_id = commentId AND user_id = userId;
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
		
	UPDATE comment SET active = 0 
		WHERE site_id = siteId 
			AND id IN (SELECT 
							comment_id 
						FROM comment_reply 
						WHERE 
							site_id = siteId 
							AND comment_replied_to_id IN (SELECT id FROM comment WHERE site_id = siteId AND submission_id = submission_id));
	
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

DROP PROCEDURE IF EXISTS DeleteFeedbackMessage//
CREATE PROCEDURE DeleteFeedbackMessage(IN feedbackId INT)
BEGIN
	UPDATE feedback SET active = 0 WHERE id = feedbackId;
END//

DROP PROCEDURE IF EXISTS DeleteSubmission//
CREATE PROCEDURE DeleteSubmission(IN siteId INT, IN submissionId INT)
BEGIN
	DECLARE userId INT;
	DECLARE pointsSubmission DECIMAL(3,2);
	
	SELECT 
		points_submission INTO pointsSubmission
	FROM site 
	WHERE id = siteId AND active = 1;
	
	SELECT submitted_by_user_id INTO userId FROM submission WHERE id = submissionId;
	
	CALL AdjustUserKarma(userId, (-1 * pointsSubmission));

	UPDATE submission_favorite SET active = 0 WHERE site_id = siteId AND submission_id = submissionId;
	UPDATE submission_category SET active = 0 WHERE site_id = siteId AND submission_id = submissionId;
	UPDATE submission_tag SET active = 0 WHERE site_id = siteId AND submission_id = submissionId;
	UPDATE subscription SET active = 0 WHERE site_id = siteId AND submission_id = submissionId;
	UPDATE submission_vote SET active = 0 WHERE site_id = siteId AND submission_id = submissionId;
	UPDATE comment SET active = 0 WHERE site_id = siteId AND submission_id = submissionId;
	UPDATE submission SET active = 0 WHERE site_id = siteId AND id = submissionId;
	UPDATE report_object SET active = 0 WHERE object_id = submissionId AND object_type = 'submission';
	UPDATE report SET active = 0 WHERE report_object_id IN (SELECT id FROM report_object WHERE object_id = submissionId AND object_type = 'submission');
	
	CALL DeleteCommentsForSubmission(siteId, submissionId);
END//

DROP PROCEDURE IF EXISTS DeleteSubmissionFavorite//
CREATE PROCEDURE DeleteSubmissionFavorite(IN siteId INT, IN submissionId INT, IN userId INT)
BEGIN
	UPDATE submission_favorite SET active = 0 WHERE site_id = siteId AND submission_id = submissionId AND user_id = userId;
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
	UPDATE alerts_favorite SET active = 0 WHERE alert_user_id = userId OR favorite_user_id = userId;
	UPDATE alerts_follower SET active = 0 WHERE alert_user_id = userId OR follower_user_id = userId;
	UPDATE alerts_share SET active = 0 WHERE alert_user_id = userId OR share_user_id = userId;
	UPDATE alerts_comment SET active = 0 WHERE alert_user_id = userId OR comment_user_id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserBlockedUsers//
CREATE PROCEDURE DeleteUserBlockedUsers(IN userId INT)
BEGIN
	UPDATE blocked_user SET active = 0 WHERE user_id = userId OR user_is_blocking_id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserCommentFavorites//
CREATE PROCEDURE DeleteUserCommentFavorites(IN userId INT)
BEGIN
	UPDATE comment_favorite SET active = 0 WHERE user_id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserComments//
CREATE PROCEDURE DeleteUserComments(IN userId INT, byUser TINYINT)
BEGIN
	UPDATE comment SET active = 0, deleted_by_user = byUser WHERE user_id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserCommentVotes//
CREATE PROCEDURE DeleteUserCommentVotes(IN userId INT)
BEGIN
	UPDATE comment_vote SET active = 0, direction = 0 WHERE user_id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserFriends//
CREATE PROCEDURE DeleteUserFriends(IN userId INT)
BEGIN
	UPDATE friend SET active = 0 WHERE user_id = userId OR user_is_following_id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserInfo//
CREATE PROCEDURE DeleteUserInfo(IN userId INT)
BEGIN
	UPDATE user_settings SET active = 0 WHERE user_id = userId;
	UPDATE user_ip_address SET active = 0 WHERE user_id = userId;
	UPDATE user SET active = 0 WHERE id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserReports//
CREATE PROCEDURE DeleteUserReports(IN userId INT)
BEGIN
	UPDATE report SET active = 0 WHERE reporting_user_id = userId;
	UPDATE report_object SET active = 0 WHERE object_type = 'user' AND object_id = userId;
	UPDATE report_object SET active = 0 WHERE object_type = 'submission' AND object_id IN (SELECT id FROM submission WHERE submitted_by_user_id = userId);
	UPDATE report_object SET active = 0 WHERE object_type = 'comment' AND object_id IN (SELECT id FROM comment WHERE user_id = userId);
END//

DROP PROCEDURE IF EXISTS DeleteUserSubmissions//
CREATE PROCEDURE DeleteUserSubmissions(IN userId INT)
BEGIN
	UPDATE submission_category SET active = 0 WHERE submission_id IN (SELECT id FROM submission WHERE submitted_by_user_id = userId);
	UPDATE submission_tag SET active = 0 WHERE submission_id IN (SELECT id FROM submission WHERE submitted_by_user_id = userId);
	UPDATE submission SET active = 0 WHERE submitted_by_user_id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserSubmsissionFavorites//
CREATE PROCEDURE DeleteUserSubmsissionFavorites(IN userId INT)
BEGIN
	UPDATE submission_favorite SET active = 0 WHERE user_id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserSubmsissionVotes//
CREATE PROCEDURE DeleteUserSubmsissionVotes(IN userId INT)
BEGIN
	UPDATE submission_vote SET active = 0, direction = 0 WHERE user_id = userId;
END//

DROP PROCEDURE IF EXISTS DeleteUserSubscriptions//
CREATE PROCEDURE DeleteUserSubscriptions(IN userId INT)
BEGIN
	UPDATE subscription SET active = 0 WHERE user_id = userId;
END//

DROP PROCEDURE IF EXISTS DoesAdminExist//
CREATE PROCEDURE DoesAdminExist(IN siteId INT, IN identifier VARCHAR(20))
BEGIN
	SELECT id FROM admin_user WHERE site_id = siteId AND (username = identifier OR email = identifier) AND active = 1;
END//

DROP PROCEDURE IF EXISTS EditCategory//
CREATE PROCEDURE EditCategory(IN siteId INT, IN categoryId INT, IN categoryName VARCHAR(35), IN categoryUrlName VARCHAR(35), IN sortOrder INT)
BEGIN
	UPDATE category SET 
		name = categoryName,
		url_name = categoryUrlName,
		sort_order = sortOrder 
	WHERE id = categoryId AND site_id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS EditComment//
CREATE PROCEDURE EditComment(IN commentId INT, IN commentBody TEXT)
BEGIN
	UPDATE comment SET body = commentBody, edited = 1 WHERE id = commentId;
END//

DROP PROCEDURE IF EXISTS EditSubmission//
CREATE PROCEDURE EditSubmission(IN siteId INT, IN submissionId INT, IN submissionTitle VARCHAR(255), IN submissionSummary VARCHAR(255))
BEGIN
	UPDATE submission SET title = submissionTitle, summary = submissionSummary WHERE site_id = siteId AND id = submissionId AND active = 1;
END//

DROP PROCEDURE IF EXISTS EnforcePasswordChange//
CREATE PROCEDURE EnforcePasswordChange(IN userId INT)
BEGIN
	SELECT force_password_reset FROM user WHERE id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS FollowUser//
CREATE PROCEDURE FollowUser(IN siteId INT, IN userId INT, IN userToFollowId INT)
BEGIN
	IF ((SELECT COUNT(user_id) FROM blocked_user WHERE site_id = siteId AND user_is_blocking_id = userId AND user_id = userToFollowId) = 0) THEN
		CALL UnblockUser(siteId, userId, userToFollowId);
		
		IF ((SELECT COUNT(user_id) FROM friend WHERE site_id = siteId AND user_id = userId AND user_is_following_id = userToFollowId) = 0) THEN
			INSERT INTO friend (site_id, user_id, user_is_following_id) VALUES (siteId, userId, userToFollowId);
		ELSE
			UPDATE friend SET active = 1 WHERE site_id = siteId AND user_id = userId AND user_is_following_id = userToFollowId;
		END IF;
		
		SELECT "OK" AS result;
	ELSE
		SELECT "BLOCKED" AS result;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetAdminInfo//
CREATE PROCEDURE GetAdminInfo(IN siteId INT, IN adminId INT)
BEGIN
	SELECT 
		au.id, au.username, au.full_name, au.email, ar.site_preferences, ar.content_management, ar.manage_admins  
	FROM admin_user au 
	INNER JOIN admin_role ar ON ar.id = au.role 
	WHERE 
		au.site_id = siteId AND au.active = 1 AND au.id = adminId;
END//

DROP PROCEDURE IF EXISTS GetAdminLoginInfo//
CREATE PROCEDURE GetAdminLoginInfo(IN siteId INT, IN adminUsername VARCHAR(255))
BEGIN
	SELECT 
		id, password, password_key, password_salt 
	FROM admin_user 
	WHERE 
		site_id = siteId AND active = 1 AND username = adminUsername;
END//

DROP PROCEDURE IF EXISTS GetAdminLoginInfoByID//
CREATE PROCEDURE GetAdminLoginInfoByID(IN siteId INT, IN adminUserId INT)
BEGIN
	SELECT 
		id, password, password_key, password_salt 
	FROM admin_user 
	WHERE 
		site_id = siteId AND active = 1 AND id = adminUserId;
END//

DROP PROCEDURE IF EXISTS GetAdminProfileSettings//
CREATE PROCEDURE GetAdminProfileSettings(IN adminUserId INT)
BEGIN
	SELECT full_name, email, email_reports, email_feedback FROM admin_user WHERE id = adminUserId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetAdmins//
CREATE PROCEDURE GetAdmins(IN siteId INT)
BEGIN
	SELECT 
		au.id, au.username, au.full_name, au.email, au.can_delete, ar.role_name, ar.site_preferences, ar.content_management, ar.manage_admins  
	FROM admin_user au 
	INNER JOIN admin_role ar ON ar.id = au.role 
	WHERE 
		au.site_id = siteId AND au.active = 1;
END//

DROP PROCEDURE IF EXISTS GetAds//
CREATE PROCEDURE GetAds(IN siteId INT)
BEGIN
	SELECT side_ad, top_ad FROM site_ads WHERE site_id = siteId AND active = 1;
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
	FROM alerts_share 
	WHERE alert_user_id = userId AND site_id = siteId AND active = 1;
	
	SELECT 
		COUNT(id) INTO numComments
	FROM alerts_comment 
	WHERE alert_user_id = userId AND site_id = siteId AND active = 1;
	
	SELECT 
		COUNT(id) INTO numFollowers
	FROM alerts_follower 
	WHERE alert_user_id = userId AND site_id = siteId AND active = 1;
	
	SELECT 
		COUNT(id) INTO numFavorites
	FROM alerts_favorite 
	WHERE alert_user_id = userId AND site_id = siteId AND active = 1;
	
	SELECT 
		numShares AS share_alert_count,
		numComments AS comment_alert_count,
		numFollowers AS follower_alert_count,
		numFavorites AS favorite_alert_count;
END//

DROP PROCEDURE IF EXISTS GetAlertCount//
CREATE PROCEDURE GetAlertCount(IN siteId INT, IN userId INT)
BEGIN
	DECLARE countComment INT;
	DECLARE countShare INT;
	DECLARE countFollower INT;
	DECLARE countFavorite INT;
	DECLARE countAlerts INT;
	
	SELECT count(id) INTO countComment FROM alerts_comment WHERE site_id = siteId AND alert_user_id = userId AND active = 1;
	SELECT count(id) INTO countShare FROM alerts_share WHERE site_id = siteId AND alert_user_id = userId AND active = 1;
	SELECT count(id) INTO countFollower FROM alerts_follower WHERE site_id = siteId AND alert_user_id = userId AND active = 1;
	SELECT count(id) INTO countFavorite FROM alerts_favorite WHERE site_id = siteId AND alert_user_id = userId AND active = 1;
	
	SET countAlerts = (countComment + countShare + countFollower + countFavorite);
	
	SELECT countAlerts AS num_alerts;
END//

DROP PROCEDURE IF EXISTS GetAlgorithmSettings//
CREATE PROCEDURE GetAlgorithmSettings(IN siteId INT)
BEGIN
	SELECT algorithm, threshold FROM site WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetAnalyticsCode//
CREATE PROCEDURE GetAnalyticsCode(IN siteId INT)
BEGIN
	SELECT google_analytics_code FROM site WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetAssociatedUsersFromIP//
CREATE PROCEDURE GetAssociatedUsersFromIP(IN siteId INT, IN ipAddress VARCHAR(25))
BEGIN
	SELECT uip.ip_address, u.username 
	FROM user_ip_address uip 
	INNER JOIN user u ON u.id = uip.user_id 
	WHERE uip.site_id = siteId AND uip.ip_address = ipAddress;
END//

DROP PROCEDURE IF EXISTS GetAverageSubmissionScore//
CREATE PROCEDURE GetAverageSubmissionScore(IN siteId INT)
BEGIN
	DECLARE startDate DATE;
	DECLARE numUpVotes INT;
	DECLARE numDownVotes INT;
	DECLARE numSubmissions INT;
	
	SET startDate = DATE_SUB(NOW(), INTERVAL 7 DAY);
	
	SELECT COUNT(id) INTO numSubmissions FROM submission WHERE date_created > startDate AND site_id = siteId AND active = 1;
	SELECT COUNT(submission_id) INTO numUpVotes FROM submission_vote WHERE direction = 1 AND active = 1;
	SELECT COUNT(submission_id) INTO numDownVotes FROM submission_vote WHERE direction = -1 AND active = 1;
	
	IF numSubmissions = 0 THEN
		SELECT 0 AS average_score;
	ELSE
		SELECT (numUpVotes - numDownVotes) / numSubmissions AS average_score;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetBannedDomains//
CREATE PROCEDURE GetBannedDomains(IN siteId INT)
BEGIN
	SELECT id, domain_name, reason FROM banned_domain WHERE site_id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetBannedIPs//
CREATE PROCEDURE GetBannedIPs(IN siteId INT)
BEGIN
	SELECT DISTINCT ip_address FROM user_ip_address WHERE site_id = siteId AND banned = 1;
END//

DROP PROCEDURE IF EXISTS GetBannedUsers//
CREATE PROCEDURE GetBannedUsers(IN siteId INT)
BEGIN
	SELECT 
		u.id, u.username, u.email, u.ban_reason AS reason, au.full_name AS banned_by
	FROM user u 
	INNER JOIN admin_user au ON u.banned_by_admin_id = au.id 
	WHERE 
		u.banned = 1 
		AND u.site_id = siteId;
END//

DROP PROCEDURE IF EXISTS GetBaseSettings//
CREATE PROCEDURE GetBaseSettings(IN siteId INT)
BEGIN
	SELECT root_url, title, blog, enable_api FROM site WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetBlockedUsers//
CREATE PROCEDURE GetBlockedUsers(IN siteId INT, IN userId INT)
BEGIN
	SELECT id, username, avatar  
	FROM user 
	WHERE 
		id IN (SELECT user_is_blocking_id FROM blocked_user WHERE user_id = userId AND active = 1) 
		AND 
		active = 1;
END//

DROP PROCEDURE IF EXISTS GetCaptchaSettings//
CREATE PROCEDURE GetCaptchaSettings(IN siteId INT)
BEGIN
	SELECT enable_recaptcha, recaptcha_private_key, recaptcha_public_key, recaptcha_theme FROM site WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetCategories//
CREATE PROCEDURE GetCategories(IN siteId INT)
BEGIN
	SELECT 
		id, name, url_name 
	FROM category 
	WHERE 
		site_id = siteId AND 
		active = 1 AND 
		id NOT IN (SELECT child_category_id FROM subcategory WHERE site_id = siteId AND active = 1) 
	ORDER BY sort_order ASC;
END//

DROP PROCEDURE IF EXISTS GetCategoryNameFromId//
CREATE PROCEDURE GetCategoryNameFromId(IN siteId INT, IN categoryId INT)
BEGIN
	SELECT name FROM category WHERE site_id = siteId AND id = categoryId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetCategoryNameFromUrlName//
CREATE PROCEDURE GetCategoryNameFromUrlName(IN siteId INT, IN urlName VARCHAR(35))
BEGIN
	SELECT name FROM category WHERE site_id = siteId AND url_name = urlName AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetCommentAlerts//
CREATE PROCEDURE GetCommentAlerts(IN siteId INT, IN userId INT)
BEGIN
	SELECT 
		a.id, u.username, u.avatar, s.id AS submission_id, a.comment_id, s.type, s.title 
	FROM alerts_comment a 
	INNER JOIN user u ON u.id = a.comment_user_id 
	INNER JOIN submission s ON s.id = a.submission_id 
	WHERE 
		a.site_id = siteId 
		AND a.alert_user_id = userId 
		AND a.active = 1 
	ORDER BY a.date_created ASC;
END//

DROP PROCEDURE IF EXISTS GetCommentCount//
CREATE PROCEDURE GetCommentCount(IN submissionId INT)
BEGIN
	SELECT COUNT(id) as num_comments FROM comment WHERE submission_id = submissionId AND active = 1;
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

DROP PROCEDURE IF EXISTS GetCommentReportDetails//
CREATE PROCEDURE GetCommentReportDetails(IN reportId INT)
BEGIN
	SELECT r.id, c.id AS comment_id, c.body, s.id AS submission_id, s.type, s.title, u.id AS user_id, u.username 
	FROM report_object r 
	INNER JOIN comment c ON c.id = r.object_id 
	INNER JOIN submission s ON s.id = c.submission_id 
	INNER JOIN user u ON u.id = c.user_id 
	WHERE 
		r.id = reportId
		AND r.object_type = 'comment' 
		AND r.active = 1;
END//

DROP PROCEDURE IF EXISTS GetCommentReports//
CREATE PROCEDURE GetCommentReports(IN siteId INT)
BEGIN
	SELECT r.id, s.title, u.username 
	FROM report_object r 
	INNER JOIN comment c ON c.id = r.object_id 
	INNER JOIN submission s ON s.id = c.submission_id 
	INNER JOIN user u ON u.id = c.user_id
	WHERE 
		r.site_id = siteId 
		AND r.object_type = 'comment' 
		AND r.active = 1;
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

DROP PROCEDURE IF EXISTS GetCommentScore//
CREATE PROCEDURE GetCommentScore(IN commentId INT)
BEGIN
	DECLARE numUpVotes INT;
	DECLARE numDownVotes INT;
	
	SELECT COUNT(comment_id) INTO numUpVotes FROM comment_vote WHERE comment_id = commentId AND direction = 1 AND active = 1;
	SELECT COUNT(comment_id) INTO numDownVotes FROM comment_vote WHERE comment_id = commentId AND direction = -1 AND active = 1;
	
	SELECT (numUpVotes - numDownVotes) AS score;
END//

DROP PROCEDURE IF EXISTS GetCommentSettings//
CREATE PROCEDURE GetCommentSettings(IN siteId INT)
BEGIN
	SELECT comment_modify_time FROM site WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetCommentsForSubmission//
CREATE PROCEDURE GetCommentsForSubmission(IN siteId INT, IN submissionId INT, IN loggedInUserId INT)
BEGIN
	IF loggedInUserId IS NULL THEN
		SELECT c.id, c.body, c.score, c.date_created, u.username, u.avatar 
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id 
		WHERE c.submission_id = submissionId AND c.active = 1 
		ORDER BY c.date_created ASC;
	ELSE
		SELECT c.id, c.body, c.score, c.date_created, u.username, u.avatar 
		FROM comment c 
		INNER JOIN user u ON u.id = c.user_id 
		WHERE c.submission_id = submissionId AND c.active = 1 AND c.user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE user_id = loggedInUserId AND active = 1) 
		ORDER BY c.date_created ASC;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetCommentVote//
CREATE PROCEDURE GetCommentVote(IN siteId INT, IN userId INT, IN commentId INT)
BEGIN 
	SELECT direction FROM comment_vote WHERE site_id = siteId AND user_id = userId AND comment_id = commentId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetCSSFiles//
CREATE PROCEDURE GetCSSFiles(IN siteId INT)
BEGIN
	SELECT filename	FROM theme WHERE part = 'css' AND site_id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetFAQs//
CREATE PROCEDURE GetFAQs(IN siteId INT)
BEGIN
	SELECT id, question, answer FROM faq WHERE site_id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetFavoriteAlerts//
CREATE PROCEDURE GetFavoriteAlerts(IN siteId INT, IN userId INT)
BEGIN
	SELECT 
		a.id, u.username, u.avatar, s.id AS submission_id, s.type, s.title 
	FROM alerts_favorite a 
	INNER JOIN user u ON u.id = a.favorite_user_id 
	INNER JOIN submission s ON s.id = a.submission_id 
	WHERE 
		a.site_id = siteId 
		AND a.alert_user_id = userId 
		AND a.active = 1 
	ORDER BY a.date_created ASC;
END//

DROP PROCEDURE IF EXISTS GetFeedback//
CREATE PROCEDURE GetFeedback(IN siteId INT)
BEGIN
	SELECT id, name, email, reason, message, date_FORMAT(date_created, '%c/%e/%Y %h:%i %p') AS message_date, unread FROM feedback WHERE site_id = siteId AND active = 1 ORDER BY date_created DESC;
END//

DROP PROCEDURE IF EXISTS GetFeedbackEmails//
CREATE PROCEDURE GetFeedbackEmails(IN siteId INT)
BEGIN
	SELECT email FROM admin_user WHERE site_id = siteId AND email_feedback = 1 AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetFeedbackMessage//
CREATE PROCEDURE GetFeedbackMessage(IN siteId INT, IN feedbackId INT)
BEGIN
	SELECT id, name, email, reason, message, date_FORMAT(date_created, '%c/%e/%Y %h:%i %p') AS message_date FROM feedback WHERE site_id = siteId AND id = feedbackId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetFollowerAlerts//
CREATE PROCEDURE GetFollowerAlerts(IN siteId INT, IN userId INT)
BEGIN
	SELECT 
		a.id, u.username, u.avatar 
	FROM alerts_follower a 
	INNER JOIN user u ON u.id = a.follower_user_id  
	WHERE 
		a.site_id = siteId 
		AND a.alert_user_id = userId 
		AND a.active = 1 
	ORDER BY a.date_created ASC;
END//

DROP PROCEDURE IF EXISTS GetFriendSubmissionCount//
CREATE PROCEDURE GetFriendSubmissionCount(IN siteId INT, IN userId INT)
BEGIN
	SELECT count(id) AS num_subs 
	FROM submission 
	WHERE 
		site_id = siteId 
		AND submitted_by_user_id IN (SELECT user_is_following_id FROM friend WHERE site_id = siteId AND user_id = userId AND active = 1) 
		AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetFriendSubmissions//
CREATE PROCEDURE GetFriendSubmissions(IN siteId INT, IN userId INT, In selectOffset INT, IN selectLimit INT)
BEGIN
	SET @sql = concat('
		SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
			s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
		WHERE 
		s.site_id = ', siteId, ' 
		AND u.site_id = ', siteId, ' 
		AND s.submitted_by_user_id IN (SELECT user_is_following_id FROM friend WHERE site_id = ', siteId, ' AND user_id = ', userId, ' AND active = 1) 
		AND s.active = 1 
		AND u.active = 1 
		ORDER BY s.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);
		
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS GetIPAddressInfo//
CREATE PROCEDURE GetIPAddressInfo(IN siteId INT, IN ipAddress VARCHAR(25))
BEGIN
	SELECT banned FROM user_ip_address WHERE ip_address = ipAddress;
END//

DROP PROCEDURE IF EXISTS GetKarmaPoints//
CREATE PROCEDURE GetKarmaPoints(IN siteId INT, IN userId INT)
BEGIN
	DECLARE countSubmissions INT;
	DECLARE countVotes INT;
	DECLARE countComments INT;
	DECLARE countPopular INT;
	
	DECLARE pointsSubmission DECIMAL(3,2);
	DECLARE pointsVote DECIMAL(3,2);
	DECLARE pointsComment DECIMAL(3,2);
	DECLARE pointsPopular DECIMAL(3,2);
	
	DECLARE karmaPoints DECIMAL(3,2);

	SELECT 
		points_submission INTO pointsSubmission
	FROM site 
	WHERE id = siteId AND active = 1;
	
	SELECT 
		points_vote INTO pointsVote
	FROM site 
	WHERE id = siteId AND active = 1;
	
	SELECT 
		points_comment INTO pointsComment
	FROM site 
	WHERE id = siteId AND active = 1;
	
	SELECT 
		points_popular INTO pointsPopular
	FROM site 
	WHERE id = siteId AND active = 1;
	
	SELECT COUNT(id) INTO countSubmissions FROM submission WHERE site_id = siteId AND submitted_by_user_id = userId AND active = 1;
	SELECT COUNT(id) INTO countComments FROM comment WHERE site_id = siteId AND user_id = userId AND active = 1;
	SELECT COUNT(id) INTO countPopular FROM submission WHERE site_id = siteId AND submitted_by_user_id = userId AND popular = 1 AND active = 1;
	
	SELECT 
		COUNT(submission_id) INTO countVotes 
	FROM submission_vote 
	WHERE 
		site_id = siteId AND 
		user_id = userId AND 
		submission_id NOT IN (SELECT id FROM submission WHERE site_id = siteId AND submitted_by_user_id = user_id AND active = 1) AND
		active = 1;
		
	SET karmaPoints = ((countSubmissions * pointsSubmission) + (countPopular * pointsPopular) + (countComments * pointsComment) + (countVotes * pointsVote));
	
	SELECT karmaPoints AS karma_points;
END//

DROP PROCEDURE IF EXISTS GetKarmaSettings//
CREATE PROCEDURE GetKarmaSettings(IN siteId INT)
BEGIN
	SELECT use_karma_system, karma_name, points_submission, points_comment, points_vote, 
		points_popular, points_comment_vote_up, points_comment_vote_down, 
		karma_penalties, karma_penalty_1_threshold, karma_penalty_1_comments, karma_penalty_1_submissions, 
		karma_penalty_2_threshold, karma_penalty_2_comments, karma_penalty_2_submissions 
	FROM site WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetParentCategory//
CREATE PROCEDURE GetParentCategory(IN siteId INT, IN category VARCHAR(35))
BEGIN
	DECLARE categoryId INT;
	
	SELECT id INTO categoryId FROM category WHERE url_name = category AND site_id = siteId AND active = 1 LIMIT 0,1;

	SELECT 
		id, name, url_name 
	FROM category 
	WHERE 
		site_id = siteId AND 
		active = 1 AND 
		id IN (SELECT parent_category_id FROM subcategory WHERE child_category_id = categoryId AND site_id = siteId AND active = 1) 
	ORDER BY sort_order ASC;
END//

DROP PROCEDURE IF EXISTS GetPolicies//
CREATE PROCEDURE GetPolicies(IN siteId INT)
BEGIN
	SELECT about_site, privacy_policy, terms_of_use, site_help FROM site WHERE id = siteId AND active = 1;
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

DROP PROCEDURE IF EXISTS GetReportEmails//
CREATE PROCEDURE GetReportEmails(IN siteId INT)
BEGIN
	SELECT email FROM admin_user WHERE site_id = siteId AND email_reports = 1 AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetReports//
CREATE PROCEDURE GetReports(IN siteId INT, IN reportId INT)
BEGIN
	SELECT r.reason, r.details, u.username 
	FROM report r 
	INNER JOIN user u ON u.id = r.reporting_user_id 
	WHERE 
		r.site_id = siteId 
		AND r.report_object_id = reportId 
		AND r.active = 1;
END//

DROP PROCEDURE IF EXISTS GetRestrictedDomains//
CREATE PROCEDURE GetRestrictedDomains(IN siteId INT)
BEGIN
	SELECT id, domain_name, reason FROM restricted_domain WHERE site_id = siteId AND active = 1;
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

DROP PROCEDURE IF EXISTS GetSecurityAnswer//
CREATE PROCEDURE GetSecurityAnswer(IN emailAddress VARCHAR(255))
BEGIN
	SELECT security_answer, security_answer_salt, security_answer_key FROM user WHERE email = emailAddress AND active = 1 AND suspended = 0;
END//

DROP PROCEDURE IF EXISTS GetSecurityQuestion//
CREATE PROCEDURE GetSecurityQuestion(IN emailAddress VARCHAR(255))
BEGIN
	SELECT security_question FROM user WHERE email = emailAddress AND active = 1 AND suspended = 0;
END//

DROP PROCEDURE IF EXISTS GetSettings//
CREATE PROCEDURE GetSettings(IN siteId INT)
BEGIN
	SELECT 
		root_url, mobile_root_url, title, theme, theme_dir, email_new_report, auto_report_keywords,
		blog, use_karma_system, karma_name, points_submission, points_comment, points_vote, points_popular, default_avatar, 
		default_photo_thumbnail, default_video_thumbnail, algorithm, threshold, comment_modify_time, pagination, show_votes, 
		enable_recaptcha, recaptcha_private_key, recaptcha_public_key, recaptcha_theme, about_site, privacy_policy, 
		terms_of_use, site_help, google_analytics_code, enable_api, version 
	FROM site 
	WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetShareAlerts//
CREATE PROCEDURE GetShareAlerts(IN siteId INT, IN userId INT)
BEGIN
	SELECT 
		a.id, a.message, u.username, u.avatar, s.id AS submission_id, s.type, s.title 
	FROM alerts_share a 
	INNER JOIN user u ON u.id = a.share_user_id 
	INNER JOIN submission s ON s.id = a.submission_id 
	WHERE 
		a.site_id = siteId 
		AND a.alert_user_id = userId 
		AND a.active = 1 
	ORDER BY a.date_created ASC;
END//

DROP PROCEDURE IF EXISTS GetSubCategories//
CREATE PROCEDURE GetSubCategories(IN siteId INT, IN parentCategory VARCHAR(35))
BEGIN
	DECLARE categoryId INT;
	
	SELECT id INTO categoryId FROM category WHERE url_name = parentCategory AND site_id = siteId AND active = 1 LIMIT 0,1;

	SELECT 
		id, name, url_name 
	FROM category 
	WHERE 
		site_id = siteId AND 
		active = 1 AND 
		id IN (SELECT child_category_id FROM subcategory WHERE parent_category_id = categoryId AND site_id = siteId AND active = 1) 
	ORDER BY sort_order ASC;
END//

DROP PROCEDURE IF EXISTS GetSubmissionCategories//
CREATE PROCEDURE GetSubmissionCategories(IN siteId INT, IN submissionId INT)
BEGIN
	SELECT name, url_name FROM category WHERE 
		id IN (SELECT category_id FROM submission_category WHERE site_id = siteId AND submission_id = submissionId AND active = 1)
		AND 
		active = 1;
END//

DROP PROCEDURE IF EXISTS GetSubmissionCountForCategory//
CREATE PROCEDURE GetSubmissionCountForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN subType VARCHAR(10), IN loggedInUserId INT)
BEGIN
	IF loggedInUserId IS NULL THEN
		IF subType = '' THEN 
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				id IN (SELECT submission_id FROM submission_category 
						WHERE 
							category_id IN (SELECT id FROM category WHERE url_name = categoryUrlName AND active = 1)
							AND site_id = siteId 
							AND active = 1)
				AND site_id = siteId AND active = 1 AND popular = 0;
		ELSE
				SELECT count(id) as num_subs FROM submission 
				WHERE 
					id IN (SELECT submission_id FROM submission_category 
							WHERE 
								category_id IN (SELECT id FROM category WHERE url_name = categoryUrlName AND active = 1)
								AND site_id = siteId 
								AND active = 1)
					AND site_id = siteId AND type = subType AND active = 1 AND popular = 0;
		END IF;
	ELSE
		IF subType = '' THEN 
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				id IN (SELECT submission_id FROM submission_category 
						WHERE 
							category_id IN (SELECT id FROM category WHERE url_name = categoryUrlName AND active = 1)
							AND site_id = siteId 
							AND active = 1)
				AND site_id = siteId 
				AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
				AND active = 1 
				AND popular = 0;
		ELSE
				SELECT count(id) as num_subs FROM submission 
				WHERE 
					id IN (SELECT submission_id FROM submission_category 
							WHERE 
								category_id IN (SELECT id FROM category WHERE url_name = categoryUrlName AND active = 1)
								AND site_id = siteId 
								AND active = 1)
					AND site_id = siteId 
					AND type = subType 
					AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
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
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				id IN (SELECT submission_id FROM submission_tag 
						WHERE 
							tag_id IN (SELECT id FROM tag WHERE url_name = tagUrlName AND active = 1)
							AND site_id = siteId 
							AND active = 1)
				AND site_id = siteId AND active = 1 AND popular = 0;
		ELSE
				SELECT count(id) as num_subs FROM submission 
				WHERE 
					id IN (SELECT submission_id FROM submission_tag 
							WHERE 
								tag_id IN (SELECT id FROM tag WHERE url_name = tagUrlName AND active = 1)
								AND site_id = siteId 
								AND active = 1)
					AND site_id = siteId AND type = subType AND active = 1 AND popular = 0;
		END IF;
	ELSE
		IF subType = '' THEN 
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				id IN (SELECT submission_id FROM submission_tag 
						WHERE 
							tag_id IN (SELECT id FROM tag WHERE url_name = tagUrlName AND active = 1)
							AND site_id = siteId 
							AND active = 1)
				AND site_id = siteId 
				AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
				AND active = 1 
				AND popular = 0;
		ELSE
				SELECT count(id) as num_subs FROM submission 
				WHERE 
					id IN (SELECT submission_id FROM submission_tag 
							WHERE 
								tag_id IN (SELECT id FROM tag WHERE url_name = tagUrlName AND active = 1)
								AND site_id = siteId 
								AND active = 1)
					AND site_id = siteId 
					AND type = subType 
					AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
					AND active = 1 
					AND popular = 0;
		END IF;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetSubmissionDataFromURL//
CREATE PROCEDURE GetSubmissionDataFromURL(IN siteId INT, IN subUrl VARCHAR(255))
BEGIN
	SELECT id, title, type FROM submission WHERE url = subUrl AND active = 1 AND site_id = siteId; 
END//

DROP PROCEDURE IF EXISTS GetSubmissionDetails//
CREATE PROCEDURE GetSubmissionDetails(IN siteId INT, IN submissionId INT)
BEGIN
	SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
		s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
	FROM submission s 
	INNER JOIN user u ON u.id = s.submitted_by_user_id 
	WHERE s.id = submissionId AND s.active = 1;
END//

DROP PROCEDURE IF EXISTS GetSubmissionPopularCountForCategory//
CREATE PROCEDURE GetSubmissionPopularCountForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN subType VARCHAR(10), IN loggedInUserId INT)
BEGIN
	IF loggedInUserId IS NULL THEN
		IF subType = '' THEN 
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				id IN (SELECT submission_id FROM submission_category 
						WHERE 
							category_id IN (SELECT id FROM category WHERE url_name = categoryUrlName AND active = 1)
							AND site_id = siteId 
							AND active = 1)
				AND site_id = siteId 
				AND active = 1 
				AND popular = 1;
		ELSE
				SELECT count(id) as num_subs FROM submission 
				WHERE 
					id IN (SELECT submission_id FROM submission_category 
							WHERE 
								category_id IN (SELECT id FROM category WHERE url_name = categoryUrlName AND active = 1)
								AND site_id = siteId 
								AND active = 1)
					AND site_id = siteId 
					AND active = 1 
					AND type = subType 
					AND popular = 1;
		END IF;
	ELSE
		IF subType = '' THEN 
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				id IN (SELECT submission_id FROM submission_category 
						WHERE 
							category_id IN (SELECT id FROM category WHERE url_name = categoryUrlName AND active = 1)
							AND site_id = siteId 
							AND active = 1)
				AND site_id = siteId 
				AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
				AND active = 1 
				AND popular = 1;
		ELSE
				SELECT count(id) as num_subs FROM submission 
				WHERE 
					id IN (SELECT submission_id FROM submission_category 
							WHERE 
								category_id IN (SELECT id FROM category WHERE url_name = categoryUrlName AND active = 1)
								AND site_id = siteId 
								AND active = 1)
					AND site_id = siteId 
					AND active = 1 
					AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
					AND type = subType 
					AND popular = 1;
		END IF;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetSubmissionPopularCountForTag//
CREATE PROCEDURE GetSubmissionPopularCountForTag(IN siteId INT, IN tagUrlName VARCHAR(255), IN subType VARCHAR(10), IN loggedInUserId INT)
BEGIN
	IF loggedInUserId IS NULL THEN
		IF subType = '' THEN 
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				id IN (SELECT submission_id FROM submission_tag 
						WHERE 
							tag_id IN (SELECT id FROM tag WHERE url_name = tagUrlName AND active = 1)
							AND site_id = siteId 
							AND active = 1)
				AND site_id = siteId 
				AND popular = 1 
				AND active = 1;
		ELSE
				SELECT count(id) as num_subs FROM submission 
				WHERE 
					id IN (SELECT submission_id FROM submission_tag 
							WHERE 
								tag_id IN (SELECT id FROM tag WHERE url_name = tagUrlName AND active = 1)
								AND site_id = siteId 
								AND active = 1)
					AND site_id = siteId AND popular = 1 AND type = subType AND active = 1;
		END IF;
	ELSE
		IF subType = '' THEN 
			SELECT count(id) as num_subs FROM submission 
			WHERE 
				id IN (SELECT submission_id FROM submission_tag 
						WHERE 
							tag_id IN (SELECT id FROM tag WHERE url_name = tagUrlName AND active = 1)
							AND site_id = siteId 
							AND active = 1)
				AND site_id = siteId 
				AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
				AND popular = 1 
				AND active = 1;
		ELSE
				SELECT count(id) as num_subs FROM submission 
				WHERE 
					id IN (SELECT submission_id FROM submission_tag 
							WHERE 
								tag_id IN (SELECT id FROM tag WHERE url_name = tagUrlName AND active = 1)
								AND site_id = siteId 
								AND active = 1)
					AND site_id = siteId 
					AND popular = 1 
					AND type = subType 
					AND submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = siteId AND user_id = loggedInUserId AND active = 1) 
					AND active = 1;
		END IF;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetSubmissionReportDetails//
CREATE PROCEDURE GetSubmissionReportDetails(IN siteId INT, IN reportId INT)
BEGIN
	SELECT r.id, s.id AS submission_id, s.type, s.title, s.score, s.summary, s.url, u.id AS user_id, u.username  
	FROM report_object r 
	INNER JOIN submission s ON s.id = r.object_id 
	INNER JOIN user u ON u.id = s.submitted_by_user_id
	WHERE 
		r.site_id = siteId 
		AND r.id = reportId 
		AND r.object_type = 'submission' 
		AND r.active = 1;
END//

DROP PROCEDURE IF EXISTS GetSubmissionReports//
CREATE PROCEDURE GetSubmissionReports(IN siteId INT)
BEGIN
	SELECT r.id, s.title 
	FROM report_object r 
	INNER JOIN submission s ON s.id = r.object_id 
	WHERE 
		r.site_id = siteId 
		AND r.object_type = 'submission' 
		AND r.active = 1;
END//

DROP PROCEDURE IF EXISTS GetSubmissionScore//
CREATE PROCEDURE GetSubmissionScore(IN submissionId INT)
BEGIN
	DECLARE numUpVotes INT;
	DECLARE numDownVotes INT;
	
	SELECT COUNT(submission_id) INTO numUpVotes FROM submission_vote WHERE submission_id = submissionId AND direction = 1 AND active = 1;
	SELECT COUNT(submission_id) INTO numDownVotes FROM submission_vote WHERE submission_id = submissionId AND direction = -1 AND active = 1;
	
	SELECT (numUpVotes - numDownVotes) AS score;
END//

DROP PROCEDURE IF EXISTS GetSubmissionSettings//
CREATE PROCEDURE GetSubmissionSettings(IN siteId INT)
BEGIN
	SELECT pagination, show_votes FROM site WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetSubmissionsForCategory//
CREATE PROCEDURE GetSubmissionsForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN subType VARCHAR(10), IN selectOffset INT, IN selectLimit INT, IN loggedInUserId INT)
BEGIN
	DECLARE typeClause VARCHAR(50);
	DECLARE blockedClause VARCHAR(255);

	IF loggedInUserId IS NULL THEN
		SET blockedClause = '';
	ELSE
		SET blockedClause = concat('AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = ', siteId, ' AND user_id = ', loggedInUserId, ' AND active = 1)');
	END IF;

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
			s.id IN (SELECT submission_id FROM submission_category 
						WHERE category_id IN (SELECT id FROM category WHERE url_name = \'', categoryUrlName, '\' AND site_id = ', siteId, ' AND active = 1) AND 
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
		SET blockedClause = concat('AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = ', siteId, ' AND user_id = ', loggedInUserId, ' AND active = 1)');
	END IF;

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
			s.id IN (SELECT submission_id FROM submission_tag 
						WHERE tag_id IN (SELECT id FROM tag WHERE url_name = \'', tagUrlName, '\' AND site_id = ', siteId, ' AND active = 1) AND 
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

DROP PROCEDURE IF EXISTS GetSubmissionsPopularForCategory//
CREATE PROCEDURE GetSubmissionsPopularForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN subType VARCHAR(10), IN selectOffset INT, IN selectLimit INT, IN loggedInUserId INT)
BEGIN
	DECLARE typeClause VARCHAR(50);
	DECLARE blockedClause VARCHAR(255);

	IF loggedInUserId IS NULL THEN
		SET blockedClause = '';
	ELSE
		SET blockedClause = concat('AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = ', siteId, ' AND user_id = ', loggedInUserId, ' AND active = 1)');
	END IF;
	
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
			s.id IN (SELECT submission_id FROM submission_category 
						WHERE category_id IN (SELECT id FROM category WHERE url_name = \'', categoryUrlName, '\' AND site_id = ', siteId, ' AND active = 1) AND 
							active = 1)
			 ', typeClause, 
			' ', 
			blockedClause, ' 
			AND 
			s.popular = 1 
			AND 
			s.active = 1 
			AND 
			s.site_id = ', siteId, 
		' ORDER BY s.popular_date DESC LIMIT ', selectOffset , ', ', selectLimit);
		
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS GetSubmissionsPopularForTag//
CREATE PROCEDURE GetSubmissionsPopularForTag(IN siteId INT, IN tagUrlName VARCHAR(35), IN subType VARCHAR(10), IN selectOffset INT, IN selectLimit INT, IN loggedInUserId INT)
BEGIN
	DECLARE typeClause VARCHAR(50);
	DECLARE blockedClause VARCHAR(255);

	IF loggedInUserId IS NULL THEN
		SET blockedClause = '';
	ELSE
		SET blockedClause = concat('AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM blocked_user WHERE site_id = ', siteId, ' AND user_id = ', loggedInUserId, ' AND active = 1)');
	END IF;

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
			s.id IN (SELECT submission_id FROM submission_tag 
						WHERE tag_id IN (SELECT id FROM tag WHERE url_name = \'', tagUrlName, '\' AND site_id = ', siteId, ' AND active = 1) AND 
							active = 1) 
			 ', typeClause, 
			' ', 
			blockedClause, ' 
			AND 
			s.popular = 1 
			AND 
			s.active = 1 
			AND 
			s.site_id = ', siteId, 
		' ORDER BY s.popular_date DESC LIMIT ', selectOffset , ', ', selectLimit);

	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS GetSubmissionTags//
CREATE PROCEDURE GetSubmissionTags(IN siteId INT, IN submissionId INT)
BEGIN
	SELECT name, url_name FROM tag WHERE 
		id IN (SELECT tag_id FROM submission_tag WHERE site_id = siteId AND submission_id = submissionId AND active = 1)
		AND 
		active = 1;
END//

DROP PROCEDURE IF EXISTS GetSubmissionThumbnail//
CREATE PROCEDURE GetSubmissionThumbnail(IN siteId INT, IN submissionId INT)
BEGIN
	 SELECT thumbnail FROM submission WHERE id = submissionId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetSubmissionVote//
CREATE PROCEDURE GetSubmissionVote(IN siteId INT, IN userId INT, IN submissionId INT)
BEGIN 
	SELECT direction FROM submission_vote WHERE user_id = userId AND submission_id = submissionId;
END//

DROP PROCEDURE IF EXISTS GetSubmissionVotes//
CREATE PROCEDURE GetSubmissionVotes(IN submissionId INT)
BEGIN
	SELECT v.direction, u.username, u.avatar 
	FROM submission_vote v 
	INNER JOIN user u ON u.id = v.user_id 
	WHERE v.submission_id = submissionId AND v.active = 1 
	ORDER BY v.date_created ASC;
END//

DROP PROCEDURE IF EXISTS GetSubscribers//
CREATE PROCEDURE GetSubscribers(IN siteId INT, IN submissionId INT)
BEGIN
	SELECT user_id FROM subscription WHERE submission_id = submissionId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetTagNameFromUrlName//
CREATE PROCEDURE GetTagNameFromUrlName(IN siteId INT, IN urlName VARCHAR(35))
BEGIN
	SELECT name FROM tag WHERE site_id = siteId AND url_name = urlName AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetThemeFiles//
CREATE PROCEDURE GetThemeFiles(IN siteId INT)
BEGIN
	SELECT part, filename FROM theme WHERE part != 'css' AND site_id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetTopPopularSubmissionsForCategory//
CREATE PROCEDURE GetTopPopularSubmissionsForCategory(IN siteId INT, IN categoryUrlName VARCHAR(35), IN subType VARCHAR(10))
BEGIN
		IF subType = '' THEN
			SELECT id, type, title, score FROM submission
			WHERE
				site_id = siteId AND 
				active = 1 AND
				popular = 1 AND 
				id IN (SELECT submission_id 
						FROM submission_category 
						WHERE site_id = siteId AND 
							active = 1 AND 
							category_id IN (SELECT id FROM category WHERE site_id = siteId AND active = 1 AND url_name = categoryUrlName)
				) AND 
				popular_date >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
				ORDER BY score DESC 
				LIMIT 0, 10;
		ELSE
			SELECT id, type, title, score FROM submission
			WHERE
				site_id = siteId AND 
				type = subType AND 
				active = 1 AND
				popular = 1 AND 
				id IN (SELECT submission_id 
						FROM submission_category 
						WHERE site_id = siteId AND 
							active = 1 AND 
							category_id IN (SELECT id FROM category WHERE site_id = siteId AND active = 1 AND url_name = categoryUrlName)
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
		SELECT id, type, title, score FROM submission
		WHERE
			site_id = siteId AND 
			active = 1 AND
			popular = 1 AND 
			id IN (SELECT submission_id 
					FROM submission_tag 
					WHERE site_id = siteId AND 
						active = 1 AND 
						tag_id IN (SELECT id FROM tag WHERE site_id = siteId AND active = 1 AND url_name = tagUrlName)
			) AND 
			popular_date >= DATE_SUB(NOW(),INTERVAL 4 DAY)  
			ORDER BY score DESC 
			LIMIT 0, 10;
	ELSE
		SELECT id, type, title, score FROM submission
		WHERE
			site_id = siteId AND 
			type = subType AND 
			active = 1 AND
			popular = 1 AND 
			id IN (SELECT submission_id 
					FROM submission_tag 
					WHERE site_id = siteId AND 
						active = 1 AND 
						tag_id IN (SELECT id FROM tag WHERE site_id = siteId AND active = 1 AND url_name = tagUrlName)
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
		SELECT id, type, title, score FROM submission
		WHERE
			site_id = siteId AND 
			active = 1 AND
			popular = 0 AND 
			id IN (SELECT submission_id 
					FROM submission_category 
					WHERE site_id = siteId AND 
						active = 1 AND 
						category_id IN (SELECT id FROM category WHERE site_id = siteId AND active = 1 AND url_name = categoryUrlName)
			) AND 
			date_created >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
			ORDER BY score DESC 
			LIMIT 0, 10;
	ELSE
		SELECT id, type, title, score FROM submission
		WHERE
			site_id = siteId AND 
			type = subType AND 
			active = 1 AND
			popular = 0 AND 
			id IN (SELECT submission_id 
					FROM submission_category 
					WHERE site_id = siteId AND 
						active = 1 AND 
						category_id IN (SELECT id FROM category WHERE site_id = siteId AND active = 1 AND url_name = categoryUrlName)
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
		SELECT id, type, title, score FROM submission
		WHERE
			site_id = siteId AND 
			active = 1 AND
			popular = 0 AND 
			id IN (SELECT submission_id 
					FROM submission_tag 
					WHERE site_id = siteId AND 
						active = 1 AND 
						tag_id IN (SELECT id FROM tag WHERE site_id = siteId AND active = 1 AND url_name = tagUrlName)
			) AND 
			date_created >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
			ORDER BY score DESC 
			LIMIT 0, 10;
	ELSE
		SELECT id, type, title, score FROM submission
		WHERE
			site_id = siteId AND 
			type = subType AND 
			active = 1 AND
			popular = 0 AND 
			id IN (SELECT submission_id 
					FROM submission_tag 
					WHERE site_id = siteId AND 
						active = 1 AND 
						tag_id IN (SELECT id FROM tag WHERE site_id = siteId AND active = 1 AND url_name = tagUrlName)
			) AND 
			date_created >= DATE_SUB(NOW(),INTERVAL 4 DAY) 
			ORDER BY score DESC 
			LIMIT 0, 10;
	END IF;
END//

DROP PROCEDURE IF EXISTS GetUnreadFeedbackCount//
CREATE PROCEDURE GetUnreadFeedbackCount(IN siteId INT)
BEGIN
	SELECT COUNT(id) AS num_messages FROM feedback WHERE site_id = siteId AND unread = 1 AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserAlertSettings//
CREATE PROCEDURE GetUserAlertSettings(IN siteId INT, IN userId INT)
BEGIN
	SELECT alert_comments, alert_shares, alert_messages, alert_followers, alert_favorites FROM user_settings WHERE user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserCommentCount//
CREATE PROCEDURE GetUserCommentCount(IN siteId INT, IN userId INT)
BEGIN
	SELECT COUNT(id) as num_comments 
	FROM comment 
	WHERE site_id = siteId AND user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserComments//
CREATE PROCEDURE GetUserComments(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
BEGIN
	SET @sql = concat('
	SELECT 
		c.id AS comment_id, s.id AS submission_id, s.type, s.title, c.body, c.score, c.date_created 
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
	ORDER BY c.date_created DESC 
	LIMIT ', selectOffset, ', ', selectLimit);
	
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
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

DROP PROCEDURE IF EXISTS GetUserDislikedSubmissionCount//
CREATE PROCEDURE GetUserDislikedSubmissionCount(IN siteId INT, IN userId INT)
BEGIN
	SELECT COUNT(id) as num_submissions 
	FROM submission 
	WHERE site_id = siteId AND id IN (SELECT submission_id FROM submission_vote WHERE user_id = userId AND direction = -1 AND active = 1) AND submitted_by_user_id != userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserDislikedSubmissions//
CREATE PROCEDURE GetUserDislikedSubmissions(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
BEGIN
	SET @sql = concat('
		SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
			s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
		WHERE 
			s.id IN (SELECT submission_id FROM submission_vote WHERE user_id = ', userId, ' AND direction = -1 AND active = 1) 
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

DROP PROCEDURE IF EXISTS GetUserFavoriteCount//
CREATE PROCEDURE GetUserFavoriteCount(IN siteId INT, IN userId INT)
BEGIN
	DECLARE countFavoriteSubmissions INT;
	DECLARE countFavoriteComments INT;
	
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

	SELECT (countFavoriteSubmissions + countFavoriteComments) AS num_favorites;
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
			f.date_created AS favorite_date, 
			u.username AS comment_username 
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

DROP PROCEDURE IF EXISTS GetUserFollowers//
CREATE PROCEDURE GetUserFollowers(IN siteId INT, IN userId INT)
BEGIN
	SELECT id, username, avatar, details 
	FROM user 
	WHERE 
		id IN (SELECT user_id FROM friend WHERE user_is_following_id = userId AND active = 1) 
		AND 
		active = 1 
	ORDER BY username ASC;
END//

DROP PROCEDURE IF EXISTS GetUserFollowing//
CREATE PROCEDURE GetUserFollowing(IN siteId INT, IN userId INT)
BEGIN
	SELECT id, username, avatar, details 
	FROM user 
	WHERE 
		id IN (SELECT user_is_following_id FROM friend WHERE user_id = userId AND active = 1) 
		AND 
		active = 1 
	ORDER BY username ASC;
END//

DROP PROCEDURE IF EXISTS GetUserInfoByID//
CREATE PROCEDURE GetUserInfoByID(IN siteId INT, IN userId INT)
BEGIN
	SELECT 
		u.id, u.username, u.password, u.email, u.avatar, u.details, u.website, u.location, u.security_question, 
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
		u.id, u.username, u.password, u.email, u.avatar, u.details, u.website, u.location, u.security_question, 
		u.security_answer, u.karma_points, u.suspended, u.banned, u.ban_reason, a.username AS banned_by_admin_username, 
		a.full_name AS banned_by_admin_full_name 
	FROM user u 
	LEFT OUTER JOIN admin_user a ON a.id = u.banned_by_admin_id 
	WHERE u.active = 1 AND (u.username = identifier OR u.email = identifier);
END//

DROP PROCEDURE IF EXISTS GetUserIPAddresses//
CREATE PROCEDURE GetUserIPAddresses(IN userId INT)
BEGIN
	SELECT ip_address FROM user_ip_address WHERE user_id = userId;
END//

DROP PROCEDURE IF EXISTS GetUserLikedSubmissionCount//
CREATE PROCEDURE GetUserLikedSubmissionCount(IN siteId INT, IN userId INT)
BEGIN
	SELECT COUNT(id) as num_submissions 
	FROM submission 
	WHERE site_id = siteId AND id IN (SELECT submission_id FROM submission_vote WHERE user_id = userId AND direction = 1 AND active = 1) AND submitted_by_user_id != userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserLikedSubmissions//
CREATE PROCEDURE GetUserLikedSubmissions(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
BEGIN
	SET @sql = concat('
		SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
			s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
		WHERE 
			s.id IN (SELECT submission_id FROM submission_vote WHERE user_id = ', userId, ' AND direction = 1 AND active = 1) 
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

DROP PROCEDURE IF EXISTS GetUserLoginInfo//
CREATE PROCEDURE GetUserLoginInfo(IN siteId INT, IN identifier VARCHAR(255))
BEGIN
	SELECT id, password, password_key, password_salt, suspended, banned FROM user WHERE active = 1 AND (username = identifier OR email = identifier);
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
	LIMIT 0, 14;
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
	LIMIT 0,14;
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

DROP PROCEDURE IF EXISTS GetUserReportDetails//
CREATE PROCEDURE GetUserReportDetails(IN reportId INT)
BEGIN
	SELECT 
		r.id, u.id AS user_id, u.username, u.email, u.details, u.website, u.avatar 
	FROM report_object r 
	INNER JOIN user u ON u.id = r.object_id 
	WHERE 
		r.id = reportId 
		AND r.object_type = 'user' 
		AND r.active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserReports//
CREATE PROCEDURE GetUserReports(IN siteId INT)
BEGIN
	SELECT r.id, u.username 
	FROM report_object r 
	INNER JOIN user u ON u.id = r.object_id 
	WHERE 
		r.site_id = siteId 
		AND r.object_type = 'user' 
		AND r.active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserSettings//
CREATE PROCEDURE GetUserSettings(IN siteId INT, IN userId INT)
BEGIN
	SELECT start_page, start_page_title, alert_comments, alert_shares, alert_messages, alert_followers, alert_favorites, open_links_in, subscribe_on_submit, 
		subscribe_on_comment, comment_threshold, prepopulate_reply  
	FROM user_settings 
	WHERE user_id = userId AND active = 1;
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
	FROM submission 
	WHERE submitted_by_user_id = userId AND site_id = siteId AND active = 1;
	
	SELECT 
		COUNT(id) INTO numComments
	FROM comment 
	WHERE user_id = userId AND site_id = siteId AND active = 1;
	
	SELECT 
		COUNT(submission_id) INTO numLikes
	FROM submission_vote 
	WHERE user_id = userId AND site_id = siteId AND direction = 1 AND active = 1;
	
	SELECT 
		COUNT(submission_id) INTO numDislikes
	FROM submission_vote 
	WHERE user_id = userId AND site_id = siteId AND direction = -1 AND active = 1;
	
	SELECT 
		COUNT(submission_id) INTO numVotes
	FROM submission_vote 
	WHERE user_id = userId AND site_id = siteId AND active = 1;
	
	SELECT 
		COUNT(submission_id) INTO numSubmissionFavorites 
	FROM submission_favorite
	WHERE user_id = userId AND site_id = siteId AND active = 1;
	
	SELECT 
		COUNT(comment_id) INTO numCommentFavorites 
	FROM comment_favorite
	WHERE user_id = userId AND site_id = siteId AND active = 1;
	
	SELECT date_FORMAT(date_created, '%M %e, %Y') INTO joinDate 
	FROM user
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

DROP PROCEDURE IF EXISTS GetUserSubmissionCount//
CREATE PROCEDURE GetUserSubmissionCount(IN siteId INT, IN userId INT)
BEGIN
	SELECT COUNT(id) as num_submissions 
	FROM submission 
	WHERE site_id = siteId AND submitted_by_user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetUserSubmissions//
CREATE PROCEDURE GetUserSubmissions(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
BEGIN
	SET @sql = concat('
		SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
			s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
		WHERE 
			s.submitted_by_user_id = ', userId, ' 
			AND 
			s.active = 1 
			AND 
			s.site_id = ', siteId, 
		' ORDER BY s.date_created DESC LIMIT ', selectOffset , ', ', selectLimit);

	PREPARE stmt FROM @sql;
	EXECUTE stmt;
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

DROP PROCEDURE IF EXISTS GetUserVoteCount//
CREATE PROCEDURE GetUserVoteCount(IN siteId INT, IN userId INT)
BEGIN
	SELECT COUNT(v.submission_id) AS num_votes 
	FROM submission_vote v 
	INNER JOIN submission s ON s.id = v.submission_id 
	WHERE
		v.site_id = siteId 
		AND 
		s.site_id = siteId 
		AND 
		v.active = 1 
		AND 
		s.active = 1 
		AND 
		v.user_id = userId 
		AND 
		s.submitted_by_user_id != userId;
END//

DROP PROCEDURE IF EXISTS GetUserVotes//
CREATE PROCEDURE GetUserVotes(IN siteId INT, IN userId INT, IN selectOffset INT, IN selectLimit INT)
BEGIN
	SET @sql = concat('
	SELECT 
		v.direction, s.id AS submission_id, s.type, s.title, v.date_created   
	FROM submission_vote v 
	INNER JOIN submission s ON s.id = v.submission_id
	WHERE 
		v.site_id = ', siteId, ' 
		AND 
		s.site_id = ', siteId, ' 
		AND 
		v.user_id = ', userId, ' 
		AND 
		s.submitted_by_user_id != ', userId, ' 
		AND 
		v.active = 1 
		AND 
		s.active = 1 
	ORDER BY v.date_created DESC 
	LIMIT ', selectOffset, ', ', selectLimit);
	
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS IgnoreReport//
CREATE PROCEDURE IgnoreReport(IN siteId INT, IN reportId INT)
BEGIN
	UPDATE report SET active = 0 WHERE site_id = siteId AND report_object_id = reportId;
	UPDATE report_object SET active = 0 WHERE site_id = siteId AND id = reportId;
END//

DROP PROCEDURE IF EXISTS IsCategoryUrlAvailable//
CREATE PROCEDURE IsCategoryUrlAvailable(IN siteId INT, IN urlName VARCHAR(35))
BEGIN
	SELECT id FROM category WHERE site_id = siteId AND url_name = urlName AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsCommentFavorite//
CREATE PROCEDURE IsCommentFavorite(IN siteId INT, IN commentId INT, IN userId INT)
BEGIN
	SELECT comment_id FROM comment_favorite WHERE site_id = siteId AND comment_id = commentId AND user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsDomainBanned//
CREATE PROCEDURE IsDomainBanned(IN siteId INT, IN domainName VARCHAR(255))
BEGIN
	SELECT id FROM banned_domain WHERE site_id = siteId AND domain_name = domainName AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsDomainRestricted//
CREATE PROCEDURE IsDomainRestricted(IN siteId INT, IN domainName VARCHAR(255))
BEGIN
	SELECT id FROM restricted_domain WHERE site_id = siteId AND domain_name = domainName AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsSubmissionFavorite//
CREATE PROCEDURE IsSubmissionFavorite(IN siteId INT, IN submissionId INT, IN userId INT)
BEGIN
	SELECT submission_id FROM submission_favorite WHERE site_id = siteId AND submission_id = submissionId AND user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsUserBlocking//
CREATE PROCEDURE IsUserBlocking(IN siteId INT, IN userId INT, IN userBlockingId INT)
BEGIN
	SELECT user_id FROM blocked_user WHERE user_id = userId AND user_is_blocking_id = userBlockingId AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsUserFollowing//
CREATE PROCEDURE IsUserFollowing(IN siteId INT, IN userId INT, IN userFollowingId INT)
BEGIN
	SELECT user_id FROM friend WHERE user_id = userId AND user_is_following_id = userFollowingId AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsUserLinkedToIP//
CREATE PROCEDURE IsUserLinkedToIP(IN userId INT, IN ipAddress VARCHAR(255))
BEGIN
	SELECT COUNT(id) AS count_ip FROM user_ip_address WHERE ip_address = ipAddress AND user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsUserSubscribed//
CREATE PROCEDURE IsUserSubscribed(IN siteId INT, IN submissionId INT, IN userId INT)
BEGIN
	SELECT submission_id FROM subscription WHERE submission_id = submissionId AND user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsUserSuspendedByID//
CREATE PROCEDURE IsUserSuspendedByID(IN siteId INT, IN userId INT)
BEGIN
	SELECT id FROM user WHERE site_id = siteId AND id = userId AND suspended = 1 AND active = 1;
END//

DROP PROCEDURE IF EXISTS IsUserSuspendedByUsername//
CREATE PROCEDURE IsUserSuspendedByUsername(IN siteId INT, IN userUsername VARCHAR(20))
BEGIN
	SELECT id FROM user WHERE site_id = siteId AND username = userUsername AND suspended = 1 AND active = 1;
END//

DROP PROCEDURE IF EXISTS LeaveFeedback//
CREATE PROCEDURE LeaveFeedback(IN siteId INT, IN personName VARCHAR(255), IN personEmail VARCHAR(255), IN personReason VARCHAR(50), IN personMessage TEXT)
BEGIN
	INSERT INTO feedback (site_id, name, email, reason, message) VALUES (siteId, personName, personEmail, personReason, personMessage);
END//

DROP PROCEDURE IF EXISTS LinkCommentReply//
CREATE PROCEDURE LinkCommentReply(IN siteId INT, IN commentId INT, IN commentRepliedToId INT)
BEGIN
	INSERT INTO comment_reply (site_id, comment_id, comment_replied_to_id) VALUES (siteId, commentId, commentRepliedToId);
END//

DROP PROCEDURE IF EXISTS MarkAllAlertsRead//
CREATE PROCEDURE MarkAllAlertsRead(IN siteId INT, IN userId INT)
BEGIN
	UPDATE alerts_comment SET active = 0 WHERE site_id = siteId AND alert_user_id = userId;
	UPDATE alerts_share SET active = 0 WHERE site_id = siteId AND alert_user_id = userId;
	UPDATE alerts_follower SET active = 0 WHERE site_id = siteId AND alert_user_id = userId;
	UPDATE alerts_favorite SET active = 0 WHERE site_id = siteId AND alert_user_id = userId;
END//

DROP PROCEDURE IF EXISTS MarkAllCommentAlertsRead//
CREATE PROCEDURE MarkAllCommentAlertsRead(IN siteId INT, IN userId INT)
BEGIN
	UPDATE alerts_comment SET active = 0 WHERE site_id = siteId AND alert_user_id = userId;
END//

DROP PROCEDURE IF EXISTS MarkAllFavoriteAlertsRead//
CREATE PROCEDURE MarkAllFavoriteAlertsRead(IN siteId INT, IN userId INT)
BEGIN
	UPDATE alerts_favorite SET active = 0 WHERE site_id = siteId AND alert_user_id = userId;
END//

DROP PROCEDURE IF EXISTS MarkAllFollowerAlertsRead//
CREATE PROCEDURE MarkAllFollowerAlertsRead(IN siteId INT, IN userId INT)
BEGIN
	UPDATE alerts_follower SET active = 0 WHERE site_id = siteId AND alert_user_id = userId;
END//

DROP PROCEDURE IF EXISTS MarkAllShareAlertsRead//
CREATE PROCEDURE MarkAllShareAlertsRead(IN siteId INT, IN userId INT)
BEGIN
	UPDATE alerts_share SET active = 0 WHERE site_id = siteId AND alert_user_id = userId;
END//

DROP PROCEDURE IF EXISTS MarkCommentAlertRead//
CREATE PROCEDURE MarkCommentAlertRead(IN siteId INT, IN alertId INT)
BEGIN
	UPDATE alerts_comment SET active = 0 WHERE site_id = siteId AND id = alertId;
END//

DROP PROCEDURE IF EXISTS MarkFavoriteAlertRead//
CREATE PROCEDURE MarkFavoriteAlertRead(IN siteId INT, IN alertId INT)
BEGIN
	UPDATE alerts_favorite SET active = 0 WHERE site_id = siteId AND id = alertId;
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

DROP PROCEDURE IF EXISTS MarkFollowerAlertRead//
CREATE PROCEDURE MarkFollowerAlertRead(IN siteId INT, IN alertId INT)
BEGIN
	UPDATE alerts_follower SET active = 0 WHERE site_id = siteId AND id = alertId;
END//

DROP PROCEDURE IF EXISTS MarkShareAlertRead//
CREATE PROCEDURE MarkShareAlertRead(IN siteId INT, IN alertId INT)
BEGIN
	UPDATE alerts_share SET active = 0 WHERE site_id = siteId AND id = alertId;
END//

DROP PROCEDURE IF EXISTS PostComment//
CREATE PROCEDURE PostComment(IN siteId INT, IN submissionId INT, IN userId INT, IN commentBody TEXT)
BEGIN
	DECLARE userActive TINYINT;
	DECLARE commentId INT;
	DECLARE karmaPoints DECIMAL(3,2);
	DECLARE currentTime TIMESTAMP;
	
	SELECT CURRENT_TIMESTAMP() INTO currentTime;
	
	SELECT active INTO userActive FROM user WHERE id = userId;
	
	IF userActive = 1 THEN
		SELECT 
			points_comment INTO karmaPoints
		FROM site 
		WHERE id = siteId AND active = 1;
	
		INSERT INTO comment (site_id, submission_id, user_id, body, date_created) VALUES (siteId, submissionId, userId, commentBody, currentTime);
	
		SELECT id INTO commentId FROM comment WHERE site_id = siteId AND submission_id = submissionId AND user_id = userId AND body = commentBody AND date_created = currentTime AND active = 1;
	
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
	
	SELECT active INTO userActive FROM user WHERE id = userId;
	
	IF userActive = 1 THEN
		SELECT 
			points_comment INTO karmaPoints
		FROM site 
		WHERE id = siteId AND active = 1;
	
		INSERT INTO comment (site_id, submission_id, user_id, body) VALUES (siteId, submissionId, userId, commentBody);
	
		SELECT id INTO commentId FROM comment WHERE site_id = siteId AND submission_id = submissionId AND user_id = userId AND body = commentBody AND date_created = currentTime AND active = 1;
	
		CALL CommentVote(siteId, userId, commentId, 1);
		CALL LinkCommentReply(siteId, commentId, commentRepliedToId);
	
		CALL AdjustUserKarma(userId, karmaPoints);
	
		SELECT commentId AS comment_id;
	ELSE
		SELECT 0 AS comment_id;
	END IF;
END//

DROP PROCEDURE IF EXISTS ResetPassword//
CREATE PROCEDURE ResetPassword(IN emailAddress VARCHAR(255), IN newPassword VARCHAR(255), IN passwordSalt VARCHAR(25), IN passwordKey VARCHAR(25))
BEGIN
	UPDATE user SET password = newPassword, password_salt = passwordSalt, password_key = passwordKey WHERE email = emailAddress AND active = 1 AND suspended = 0;
END//

DROP PROCEDURE IF EXISTS ResetUserPassword//
CREATE PROCEDURE ResetUserPassword(IN userId INT, IN password VARCHAR(255), IN passwordSalt VARCHAR(255), IN passwordKey VARCHAR(255))
BEGIN
	UPDATE user SET password = password, password_salt = passwordSalt, password_key = passwordKey, force_password_reset = 1 WHERE id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS ResetUserPasswordAndQuestion//
CREATE PROCEDURE ResetUserPasswordAndQuestion(IN userId INT, IN password VARCHAR(255), IN question VARCHAR(255), IN answer VARCHAR(255), IN passwordSalt VARCHAR(255), IN passwordKey VARCHAR(255))
BEGIN
	UPDATE user SET 
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

DROP PROCEDURE IF EXISTS ResetUserStartPage//
CREATE PROCEDURE ResetUserStartPage(IN siteId INT, IN userId INT)
BEGIN
	UPDATE user_settings SET start_page = '/', start_page_title = 'Home' WHERE user_id = userId AND active = 1;
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
			site_id = ', siteId, popularClause, typeClause, ' AND active = 1 
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
			site_id = ', siteId, categoryClause, popularClause, typeClause, ' AND active = 1 
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
			site_id = ', siteId, tagClause, popularClause, typeClause, ' AND active = 1 
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
	ORDER BY date_created DESC LIMIT 0, 10;
END//

DROP PROCEDURE IF EXISTS SaveAdminProfileSettings//
CREATE PROCEDURE SaveAdminProfileSettings(IN adminUserId INT, IN fullName VARCHAR(255), IN adminEmail VARCHAR(255), IN emailReports TINYINT, IN emailFeedback TINYINT)
BEGIN
	UPDATE admin_user SET 
		full_name = fullName, 
		email = adminEmail, 
		email_reports = emailReports, 
		email_feedback = emailFeedback 
	WHERE id = adminUserId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SaveAds//
CREATE PROCEDURE SaveAds(IN siteId INT, IN topAd TEXT, IN sideAd TEXT, IN googleAnalytics TEXT)
BEGIN
	IF ((SELECT COUNT(id) FROM site_ads WHERE site_id = siteId AND active = 1) = 0) THEN
		INSERT INTO site_ads (site_id, top_ad, side_ad) VALUES (siteId, topAd, sideAd);
	ELSE
		UPDATE site_ads SET 
			side_ad = sideAd, 
			top_ad = topAd 
		WHERE site_id = siteId AND active = 1;
	END IF;
	
	UPDATE site SET google_analytics_code = googleAnalytics WHERE id = siteId;
END//

DROP PROCEDURE IF EXISTS SaveAlgorithmSettings//
CREATE PROCEDURE SaveAlgorithmSettings(IN siteId INT, IN popularAlgorithm VARCHAR(15), IN staticThreshold INT)
BEGIN
	UPDATE site SET 
		algorithm = popularAlgorithm,
		threshold = staticThreshold
	WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SaveBaseSettings//
CREATE PROCEDURE SaveBaseSettings(IN siteId INT, IN rootUrl VARCHAR(255), IN siteTitle VARCHAR(255), IN siteBlog VARCHAR(255), IN enableAPI TINYINT)
BEGIN
	UPDATE site SET 
		root_url = rootUrl,
		title = siteTitle, 
		blog = siteBlog, 
		enable_api = enableAPI 
	WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SaveCaptchaSettings//
CREATE PROCEDURE SaveCaptchaSettings(IN siteId INT, IN enableRecaptcha TINYINT, IN privateKey VARCHAR(255), IN publicKey VARCHAR(255), IN theme VARCHAR(255))
BEGIN
	UPDATE site SET 
		enable_recaptcha = enableRecaptcha,
		recaptcha_private_key = privateKey,
		recaptcha_public_key = publicKey,
		recaptcha_theme = theme
	WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SaveCommentSettings//
CREATE PROCEDURE SaveCommentSettings(IN siteId INT, IN commentModifyTime INT)
BEGIN
	UPDATE site SET comment_modify_time = commentModifyTime WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SaveKarmaSettings//
CREATE PROCEDURE SaveKarmaSettings(IN siteId INT, IN useKarmaSystem TINYINT, IN karmaName VARCHAR(20), IN pointsSubmission DECIMAL(3,2), IN pointsComment DECIMAL(3,2), IN pointsVote DECIMAL(3,2), IN pointsPopular DECIMAL(3,2), IN pointsCommentUpVote DECIMAL(3,2), IN pointsCommentDownVote DECIMAL(3,2), IN karmaPenalties TINYINT, IN karma1Threshold INT, IN karma1Submissions INT, IN karma1Comments INT, IN karma2Threshold INT, IN karma2Submissions INT, IN karma2Comments INT)
BEGIN
	UPDATE site SET 
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

DROP PROCEDURE IF EXISTS SavePolciies//
CREATE PROCEDURE SavePolciies(IN siteId INT, IN aboutSite TEXT, IN privacyPolicy TEXT, IN termsOfUse TEXT, IN siteHelp TEXT)
BEGIN
	UPDATE site SET 
		about_site = aboutSite,
		privacy_policy = privacyPolicy,
		terms_of_use = termsOfUse,
		site_help = siteHelp
	WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SaveSubmissionSettings//
CREATE PROCEDURE SaveSubmissionSettings(IN siteId INT, IN sitePagination INT, IN showVotes TINYINT)
BEGIN
	UPDATE site SET 
		pagination = sitePagination,
		show_votes = showVotes 
	WHERE id = siteId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SearchSubmissions//
CREATE PROCEDURE SearchSubmissions(IN siteId INT, IN typeClause VARCHAR(100), IN isPopular VARCHAR(10), IN searchClause TEXT, IN orderingClause VARCHAR(100), IN loggedInUserId INT, IN selectOffset INT, IN selectLimit INT)
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
		SELECT s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
			s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
		FROM submission s 
		INNER JOIN user u ON u.id = s.submitted_by_user_id 
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
			s.site_id = ', siteId, 
		' ', orderingClause, ' LIMIT ', selectOffset , ', ', selectLimit);
		
	PREPARE stmt FROM @sql;
	EXECUTE stmt;
END//

DROP PROCEDURE IF EXISTS SetSubmissionThumbnail//
CREATE PROCEDURE SetSubmissionThumbnail(IN submissionId INT, IN submissionThumbnail VARCHAR(255))
BEGIN
	UPDATE submission SET thumbnail = submissionThumbnail WHERE id = submissionId AND active = 1;
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
	FROM site 
	WHERE id = siteId AND active = 1;
	
	SELECT 
		submitted_by_user_id INTO submissionUser 
	FROM submission 
	WHERE id = submissionId AND active = 1;
	
	SELECT COUNT(submission_id) INTO voteCount FROM submission_vote WHERE user_id = userId AND submission_id = submissionId;
	
	IF voteCount > 0 THEN
		SELECT direction INTO previousVote FROM submission_vote WHERE site_id = siteId AND user_id = userId AND submission_id = submissionId;
		UPDATE submission_vote SET direction = voteDirection, active = 1 WHERE site_id = siteId AND user_id = userId AND submission_id = submissionId;
		
		IF previousVote = -1 AND voteDirection = 0 THEN
			IF submissionUser != userId THEN
				CALL AdjustUserKarma(userId, (-1 * pointsVote));
			END IF;
			UPDATE submission_vote SET active = 0 WHERE site_id = siteId AND user_id = userId AND submission_id = submissionId;
		END IF;
		IF previousVote = 1 AND voteDirection = 0 THEN
			IF submissionUser != userId THEN
				CALL AdjustUserKarma(userId, (-1 * pointsVote));
			END IF;
			UPDATE submission_vote SET active = 0 WHERE site_id = siteId AND user_id = userId AND submission_id = submissionId;
		END IF;
		IF previousVote = 0 AND voteDirection = 1 AND userId != submissionUser THEN
			CALL AdjustUserKarma(userId, pointsVote);
		END IF;
		IF previousVote = 0 AND voteDirection = -1 AND userId != submissionUser THEN
			CALL AdjustUserKarma(userId, pointsVote);
		END IF;
	ELSE
		INSERT INTO submission_vote (site_id, user_id, submission_id, direction) VALUES(siteId, userId, submissionId, voteDirection);
		IF submissionUser != userId THEN
			CALL AdjustUserKarma(userId, pointsVote);
		END IF;
	END IF;
END//

DROP PROCEDURE IF EXISTS SubscribeOnComment//
CREATE PROCEDURE SubscribeOnComment(IN siteId INT, IN userId INT)
BEGIN
	SELECT subscribe_on_comment FROM user_settings WHERE user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SubscribeOnSubmit//
CREATE PROCEDURE SubscribeOnSubmit(IN siteId INT, IN userId INT)
BEGIN
	SELECT subscribe_on_submit FROM user_settings WHERE user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SubscribeToThread//
CREATE PROCEDURE SubscribeToThread(IN siteId INT, IN submissionId INT, IN userId INT)
BEGIN
	IF ((SELECT COUNT(user_id) FROM subscription WHERE site_id = siteId AND submission_id = submissionId AND user_id = userId) = 0) THEN
		INSERT INTO subscription (site_id, user_id, submission_id) VALUES (siteId, userId, submissionId);
	ELSE
		UPDATE subscription SET active = 1 WHERE site_id = siteId AND user_id = userId AND submission_id = submissionId;
	END IF;
END//

DROP PROCEDURE IF EXISTS SuspendUserByID//
CREATE PROCEDURE SuspendUserByID(IN siteId INT, IN userId INT)
BEGIN
	UPDATE user SET suspended = 1, suspended_date = NOW() WHERE site_id = siteId AND id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS SuspendUserByUsername//
CREATE PROCEDURE SuspendUserByUsername(IN siteId INT, IN userUsername VARCHAR(20))
BEGIN
	UPDATE user SET suspended = 1, suspended_date = NOW() WHERE site_id = siteId AND username = userUsername AND active = 1;
END//

DROP PROCEDURE IF EXISTS UnbanDomain//
CREATE PROCEDURE UnbanDomain(IN siteId INT, IN domainId INT)
BEGIN 
	UPDATE banned_domain SET active = 0 WHERE site_id = siteId AND id = domainId;
END//

DROP PROCEDURE IF EXISTS UnbanIPAddress//
CREATE PROCEDURE UnbanIPAddress(IN siteId INT, IN ipAddress VARCHAR(25))
BEGIN
	UPDATE user_ip_address SET banned = 0 WHERE site_id = siteId AND ip_address = ipAddress;
END//

DROP PROCEDURE IF EXISTS UnbanUser//
CREATE PROCEDURE UnbanUser(IN siteId INT, IN userId INT)
BEGIN
	UPDATE user SET	banned = 0 WHERE site_id = siteId AND id = userId;
END//

DROP PROCEDURE IF EXISTS UnblockUser//
CREATE PROCEDURE UnblockUser(IN siteId INT, IN userId INT, IN userToUnblockId INT)
BEGIN
	UPDATE blocked_user SET active = 0 WHERE user_id = userId AND user_is_blocking_id = userToUnblockId;
END//

DROP PROCEDURE IF EXISTS UnfollowUser//
CREATE PROCEDURE UnfollowUser(IN siteId INT, IN userId INT, IN userToUnfollowId INT)
BEGIN
	UPDATE friend SET active = 0 WHERE user_id = userId AND user_is_following_id = userToUnfollowId;
END//

DROP PROCEDURE IF EXISTS UnrestrictDomain//
CREATE PROCEDURE UnrestrictDomain(IN siteId INT, IN domainId INT)
BEGIN 
	UPDATE restricted_domain SET active = 0 WHERE site_id = siteId AND id = domainId;
END//

DROP PROCEDURE IF EXISTS UnsubscribeFromThread//
CREATE PROCEDURE UnsubscribeFromThread(IN siteId INT, IN submissionId INT, IN userId INT)
BEGIN
	UPDATE subscription SET active = 0 WHERE user_id = userId AND submission_id = submissionId;
END//

DROP PROCEDURE IF EXISTS UnsuspendUserByID//
CREATE PROCEDURE UnsuspendUserByID(IN siteId INT, IN userId INT)
BEGIN
	UPDATE user SET suspended = 0, suspended_date = 'NULL' WHERE site_id = siteId AND id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS UnsuspendUserByUsername//
CREATE PROCEDURE UnsuspendUserByUsername(IN siteId INT, IN userUsername VARCHAR(20))
BEGIN
	UPDATE user SET suspended = 0, suspended_date = 'NULL' WHERE site_id = siteId AND username = userUsername AND active = 1;
END//

DROP PROCEDURE IF EXISTS UpdateAdmin//
CREATE PROCEDURE UpdateAdmin(IN siteId INT, IN adminId INT, IN fullName VARCHAR(255), IN adminEmail VARCHAR(255), IN adminRole INT)
BEGIN
	UPDATE admin_user SET 
		full_name = fullName,
		email = adminEmail,
		role = adminRole 
	WHERE id = adminId AND site_id = siteId AND active = 1;
	
	UPDATE admin_user SET role = 1 WHERE can_delete = 0;
END//

DROP PROCEDURE IF EXISTS UpdateAdminPassword//
CREATE PROCEDURE UpdateAdminPassword(IN adminUserId INT, IN userPassword VARCHAR(255), IN passwordSalt VARCHAR(25), IN passwordKey VARCHAR(25))
BEGIN
	UPDATE admin_user SET 
		password = userPassword, 
		password_salt = passwordSalt, 
		password_key = passwordKey 
	WHERE 
		id = adminUserId AND active = 1;
END//

DROP PROCEDURE IF EXISTS UpdateAvatar//
CREATE PROCEDURE UpdateAvatar(IN siteId INT, IN userId INT, IN avatarFilename VARCHAR(255))
BEGIN
	UPDATE user SET avatar = avatarFilename WHERE id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS UpdateSubmissionMakePopular//
CREATE PROCEDURE UpdateSubmissionMakePopular(IN siteId INT, IN submissionId INT)
BEGIN
	DECLARE pointsPopular DECIMAL(3,2);
	DECLARE userId INT;
	
	SELECT 
		points_popular INTO pointsPopular
	FROM site 
	WHERE id = siteId AND active = 1;

	SELECT submitted_by_user_id INTO userId FROM submission WHERE id = submissionId;

	UPDATE submission SET popular = 1, popular_date = CURRENT_TIMESTAMP WHERE site_id = siteId AND id = submissionId AND active = 1;
	
	CALL AdjustUserKarma(userId, pointsPopular);
END//

DROP PROCEDURE IF EXISTS UpdateUserNotificationSettings//
CREATE PROCEDURE UpdateUserNotificationSettings(IN siteId INT, IN userId INT, IN alertComments VARCHAR(10), IN alertShares VARCHAR(10), IN alertMessages VARCHAR(10), IN alertFollowers VARCHAR(10), IN alertFavorites VARCHAR(10))
BEGIN
	UPDATE user_settings SET 
		alert_comments = alertComments,
		alert_shares = alertShares,
		alert_messages = alertMessages,
		alert_followers = alertFollowers,
		alert_favorites = alertFavorites 
	WHERE user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS UpdateUserPassword//
CREATE PROCEDURE UpdateUserPassword(IN siteId INT, IN userId INT, IN userPassword VARCHAR(255), IN passwordSalt VARCHAR(25), IN passwordKey VARCHAR(25))
BEGIN
	UPDATE user SET 
		password = userPassword, 
		password_salt = passwordSalt, 
		password_key = passwordKey 
	WHERE 
		id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS UpdateUserProfile//
CREATE PROCEDURE UpdateUserProfile(IN siteId INT, IN userId INT, IN userDetails TEXT, IN userEmail VARCHAR(255), IN userWebsite VARCHAR(255), IN userLocation VARCHAR(255))
BEGIN
	UPDATE user SET 
		details = userDetails, 
		email = userEmail, 
		website = userWebsite, 
		location = userLocation 
	WHERE 
		id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS UpdateUserSettings//
CREATE PROCEDURE UpdateUserSettings(IN siteId INT, IN userId INT, IN openLinksIn VARCHAR(10), IN subscribeSubmit TINYINT, IN subscribeComment TINYINT, IN commentThreshold INT, IN prepopulateReply TINYINT)
BEGIN
	UPDATE user_settings SET 
		open_links_in = openLinksIn, 
		subscribe_on_submit = subscribeSubmit, 
		subscribe_on_comment = subscribeComment, 
		comment_threshold = commentThreshold, 
		prepopulate_reply = prepopulateReply 
	WHERE user_id = userId AND active = 1;
END//

DROP PROCEDURE IF EXISTS GetCurrentThemeXML//
CREATE PROCEDURE GetCurrentThemeXML(IN siteId INT)
BEGIN
	SELECT theme FROM site WHERE id = siteId;
END//

DROP PROCEDURE IF EXISTS UpdateSiteTheme//
CREATE PROCEDURE UpdateSiteTheme(IN siteId INT, IN xmlFile VARCHAR(255), IN rootDir VARCHAR(255))
BEGIN
	UPDATE site SET theme = xmlFile, theme_dir = rootDir WHERE id = siteId;
END//