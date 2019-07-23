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

ALTER TABLE user_settings ADD COLUMN prepopulate_reply TINYINT(3) NOT NULL DEFAULT 0 AFTER subscribe_on_comment;

DROP PROCEDURE IF EXISTS GetUserSettings//
CREATE PROCEDURE GetUserSettings(IN siteId INT, IN userId INT)
BEGIN
	SELECT start_page, start_page_title, alert_comments, alert_shares, alert_messages, alert_followers, alert_favorites, open_links_in, subscribe_on_submit, 
		subscribe_on_comment, comment_threshold, prepopulate_reply  
	FROM user_settings 
	WHERE user_id = userId AND active = 1;
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