CREATE TABLE banned_ip_address (
	id INT NOT NULL AUTO_INCREMENT,
	ip_address VARCHAR(255) NOT NULL,
	reason VARCHAR(255) NULL,
	active TINYINT(3) NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP PROCEDURE IF EXISTS GetIndependentBannedIPs//
CREATE PROCEDURE GetIndependentBannedIPs()
BEGIN
	SELECT 
		id, ip_address, reason 
	FROM banned_ip_address 
	WHERE active = 1;
END//

DROP PROCEDURE IF EXISTS UnbanIndependentIPAddress//
CREATE PROCEDURE UnbanIndependentIPAddress(IN bannedIPId INT)
BEGIN
	UPDATE banned_ip_address SET active = 0 WHERE id = bannedIPId;
END//

DROP PROCEDURE IF EXISTS BanIndependentIPAddress//
CREATE PROCEDURE BanIndependentIPAddress(IN ipAddress VARCHAR(255), IN banReason VARCHAR(255))
BEGIN
	INSERT INTO banned_ip_address (ip_address, reason) VALUES (ipAddress, banReason);
END//