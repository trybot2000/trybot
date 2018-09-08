-- MySQL dump 10.13  Distrib 5.7.19, for Linux (x86_64)
--
-- Host: localhost    Database: trybot2000
-- ------------------------------------------------------
-- Server version	5.7.19

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aprilfools`
--

DROP TABLE IF EXISTS `aprilfools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aprilfools` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `botId` varchar(254) NOT NULL,
  `botAvatar` varchar(254) NOT NULL,
  `botName` varchar(254) NOT NULL,
  `userName` varchar(254) NOT NULL,
  `groupId` varchar(254) NOT NULL,
  PRIMARY KEY (`primary`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth`
--

DROP TABLE IF EXISTS `auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `authToken` varchar(254) NOT NULL,
  `userToken` varchar(254) NOT NULL COMMENT 'internal use',
  `service` varchar(254) NOT NULL,
  `userId` varchar(150) DEFAULT NULL,
  `expiresAt` int(11) NOT NULL,
  PRIMARY KEY (`primary`),
  UNIQUE KEY `authToken` (`authToken`(25),`userToken`(25),`service`(20))
) ENGINE=MyISAM AUTO_INCREMENT=1280 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boots`
--

DROP TABLE IF EXISTS `boots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boots` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Hour` varchar(50) DEFAULT NULL,
  `GroupId` varchar(100) DEFAULT NULL,
  `UserId` varchar(100) DEFAULT NULL,
  `MessageId` varchar(100) DEFAULT NULL,
  `BootMinutes` int(10) unsigned DEFAULT NULL,
  `BootReason` varchar(50) DEFAULT NULL,
  `DateBooted` timestamp NULL DEFAULT NULL,
  `DateToReadd` timestamp NULL DEFAULT NULL,
  `DateReadded` timestamp NULL DEFAULT NULL,
  `AddResult` text,
  `BootResult` text,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bots`
--

DROP TABLE IF EXISTS `bots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bots` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `groupName` varchar(254) NOT NULL,
  `groupId` varchar(254) NOT NULL,
  `botName` varchar(254) NOT NULL,
  `botId` varchar(254) NOT NULL,
  `botAvatarUrl` varchar(254) DEFAULT NULL,
  `botCallbackUrl` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`primary`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chatKems`
--

DROP TABLE IF EXISTS `chatKems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatKems` (
  `primary` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `timestampMessage` timestamp NULL DEFAULT NULL,
  `messageId` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `createdAt` int(10) unsigned DEFAULT NULL,
  `groupId` int(10) unsigned DEFAULT NULL,
  `sentToGroup` int(10) unsigned DEFAULT '0',
  `timestampSent` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`primary`),
  UNIQUE KEY `messageId` (`messageId`)
) ENGINE=InnoDB AUTO_INCREMENT=711912 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `key` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversations` (
  `varDateStart` varchar(254) NOT NULL,
  `varDateEnd` varchar(254) NOT NULL,
  `varActive` varchar(12) NOT NULL,
  `varName` varchar(12) NOT NULL,
  `varInitialMessage` text NOT NULL,
  `varUserId` varchar(254) NOT NULL,
  `groupId` varchar(254) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `count`
--

DROP TABLE IF EXISTS `count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `count` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `groupId` varchar(254) NOT NULL,
  `subject` varchar(254) NOT NULL,
  `total` int(11) NOT NULL,
  PRIMARY KEY (`primary`)
) ENGINE=MyISAM AUTO_INCREMENT=491 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(254) NOT NULL,
  `eventId` varchar(254) NOT NULL,
  `attending` varchar(254) NOT NULL,
  `createdAt` varchar(254) NOT NULL,
  PRIMARY KEY (`primary`)
) ENGINE=MyISAM AUTO_INCREMENT=1121 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `guessWhoGames`
--

DROP TABLE IF EXISTS `guessWhoGames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guessWhoGames` (
  `Id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `GroupId` varchar(50) DEFAULT NULL,
  `UserIdInitiator` varchar(50) DEFAULT NULL,
  `MysteryUserId` varchar(50) DEFAULT NULL,
  `IsActive` int(11) unsigned DEFAULT '1',
  `OmitFromStatistics` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `guessWhoMessages`
--

DROP TABLE IF EXISTS `guessWhoMessages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guessWhoMessages` (
  `Id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MessageId` varchar(100) NOT NULL,
  `Text` text NOT NULL,
  `GameId` int(10) unsigned NOT NULL,
  `Used` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=848 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `guessWhoPoints`
--

DROP TABLE IF EXISTS `guessWhoPoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guessWhoPoints` (
  `Id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `GameId` int(10) unsigned NOT NULL,
  `UserId` varchar(50) DEFAULT NULL,
  `Guess` varchar(50) DEFAULT NULL,
  `Points` int(11) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=1706 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `guessWhoPool`
--

DROP TABLE IF EXISTS `guessWhoPool`;
/*!50001 DROP VIEW IF EXISTS `guessWhoPool`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `guessWhoPool` AS SELECT 
 1 AS `COUNT(*)`,
 1 AS `primary`,
 1 AS `id`,
 1 AS `timestamp`,
 1 AS `source_guid`,
 1 AS `created_at`,
 1 AS `user_id`,
 1 AS `group_id`,
 1 AS `name`,
 1 AS `avatar_url`,
 1 AS `text`,
 1 AS `system`,
 1 AS `attachments_type`,
 1 AS `attachments_url`,
 1 AS `attachments_name`,
 1 AS `attachments_lat`,
 1 AS `attachments_lng`,
 1 AS `textLength`,
 1 AS `numFavorites`,
 1 AS `favoritedBy`,
 1 AS `isTryBot`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `kicklog`
--

DROP TABLE IF EXISTS `kicklog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kicklog` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `userId` varchar(254) NOT NULL,
  `name` varchar(254) NOT NULL,
  `groupId` varchar(254) NOT NULL,
  `bootDuration` varchar(254) NOT NULL COMMENT 'seconds',
  PRIMARY KEY (`primary`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `latestUserNames`
--

DROP TABLE IF EXISTS `latestUserNames`;
/*!50001 DROP VIEW IF EXISTS `latestUserNames`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latestUserNames` AS SELECT 
 1 AS `name`,
 1 AS `user_id`,
 1 AS `created_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `markov`
--

DROP TABLE IF EXISTS `markov`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `markov` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Type` char(50) DEFAULT NULL,
  `Text` text,
  `UserId` varchar(50) DEFAULT NULL,
  `GroupId` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=380 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mentions`
--

DROP TABLE IF EXISTS `mentions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentions` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `varId` varchar(254) NOT NULL,
  `varUserId` varchar(256) NOT NULL,
  `varUserName` varchar(254) NOT NULL,
  `varDateTime` varchar(254) NOT NULL,
  `varText` text NOT NULL,
  `varGroupId` varchar(254) NOT NULL,
  `mentionType` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`primary`),
  UNIQUE KEY `varId` (`varId`),
  FULLTEXT KEY `varText` (`varText`)
) ENGINE=MyISAM AUTO_INCREMENT=21657 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `primary` int(11) DEFAULT NULL,
  `id` varchar(100) CHARACTER SET utf8 NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source_guid` varchar(100) CHARACTER SET utf8 NOT NULL,
  `created_at` varchar(100) CHARACTER SET utf8 NOT NULL,
  `user_id` varchar(100) CHARACTER SET utf8 NOT NULL,
  `group_id` varchar(100) CHARACTER SET utf8 NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `avatar_url` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `text` text CHARACTER SET utf8,
  `system` varchar(100) CHARACTER SET utf8 NOT NULL,
  `mentions` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `numMentions` int(10) unsigned DEFAULT NULL,
  `attachments_type` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `attachments_url` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `attachments_name` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `attachments_lat` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `attachments_lng` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
  `textLength` int(10) unsigned DEFAULT NULL,
  `numFavorites` int(10) unsigned DEFAULT NULL,
  `favoritedBy` text COLLATE utf8_unicode_ci,
  `isTryBot` int(2) unsigned NOT NULL DEFAULT '0' COMMENT '0 or 1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `created_at` (`created_at`),
  KEY `user_id` (`user_id`),
  KEY `numFavorites` (`numFavorites`),
  KEY `group_id` (`group_id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM AUTO_INCREMENT=902725 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `palindromes`
--

DROP TABLE IF EXISTS `palindromes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `palindromes` (
  `primary` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` varchar(100) DEFAULT NULL,
  `text` text,
  `length` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`primary`)
) ENGINE=InnoDB AUTO_INCREMENT=321 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `gamertag` varchar(254) NOT NULL,
  `displayName` varchar(254) DEFAULT NULL,
  `groupMeId` varchar(254) DEFAULT NULL,
  `codUserId` varchar(254) DEFAULT NULL,
  `emailAddress` varchar(254) DEFAULT NULL,
  `redditUserName` varchar(254) DEFAULT NULL,
  `notes` varchar(254) DEFAULT NULL,
  `dateJoined` timestamp NULL DEFAULT NULL,
  `introPost` varchar(255) DEFAULT NULL,
  `altFor` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`primary`),
  UNIQUE KEY `gamertag` (`gamertag`)
) ENGINE=MyISAM AUTO_INCREMENT=540 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `poll`
--

DROP TABLE IF EXISTS `poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` varchar(254) NOT NULL,
  `strPollName` varchar(254) NOT NULL,
  `IP` varchar(254) NOT NULL,
  `useragent` varchar(254) NOT NULL,
  `answer1` varchar(254) DEFAULT '0',
  `answer2` varchar(254) DEFAULT '0',
  `answer3` varchar(254) DEFAULT '0',
  `answer4` varchar(254) DEFAULT '0',
  `answer5` varchar(254) DEFAULT '0',
  `answer6` varchar(254) DEFAULT '0',
  `answer7` varchar(254) DEFAULT '0',
  `answer8` varchar(254) DEFAULT '0',
  UNIQUE KEY `strPollName` (`strPollName`(20),`IP`(20)),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=409 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `progress`
--

DROP TABLE IF EXISTS `progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progress` (
  `progress` varchar(254) NOT NULL,
  `scriptName` varchar(254) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `current` varchar(100) NOT NULL,
  `target` varchar(100) NOT NULL,
  `memory` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14896013 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reddit`
--

DROP TABLE IF EXISTS `reddit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reddit` (
  `subreddit` varchar(254) NOT NULL,
  `name` varchar(254) NOT NULL,
  `id` varchar(50) NOT NULL,
  `stickied` varchar(254) DEFAULT NULL,
  `author` varchar(254) NOT NULL,
  `url` varchar(254) NOT NULL,
  `tryBotPostedToGroupMe` varchar(254) DEFAULT NULL,
  `created_utc` varchar(254) NOT NULL,
  `title` varchar(254) NOT NULL,
  `selftext_html` text,
  `selftext` text,
  `isIntroPost` varchar(10) DEFAULT NULL,
  `tryBotRespondToIntroPost` varchar(10) DEFAULT NULL,
  `gamertag` varchar(254) DEFAULT NULL,
  `game` varchar(254) DEFAULT NULL,
  `botPostReturn` text,
  `accountAge` varchar(254) DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reminders`
--

DROP TABLE IF EXISTS `reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reminders` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `remindAt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userId` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `service` enum('groupme','reddit') NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`primary`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduled`
--

DROP TABLE IF EXISTS `scheduled`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scheduled` (
  `varTimeToSend` varchar(254) NOT NULL,
  `varTimeToSendPretty` varchar(254) NOT NULL COMMENT 'DATE_ISO8601 Format',
  `varText` text NOT NULL,
  `varSent` varchar(12) NOT NULL,
  `varSentAt` varchar(254) NOT NULL,
  `varSentAtDiff` varchar(254) NOT NULL,
  `varMessageName` varchar(254) NOT NULL,
  `varGroups` varchar(254) NOT NULL DEFAULT 'all',
  PRIMARY KEY (`varTimeToSend`(50),`varGroups`(50))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `key` varchar(50) NOT NULL,
  `value` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `siteStatus`
--

DROP TABLE IF EXISTS `siteStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siteStatus` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Domain` varchar(100) DEFAULT NULL,
  `Status` enum('down','degraded','partially up','having issues','up','unknown') DEFAULT NULL,
  `StatusInfo` text,
  `StatusDetail` text,
  `StatusTypes` text,
  `StartDate` timestamp NULL DEFAULT NULL,
  `EndDate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statlog`
--

DROP TABLE IF EXISTS `statlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statlog` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `strDateTimeLogged` varchar(254) NOT NULL,
  `intCp` varchar(254) NOT NULL DEFAULT '0',
  `intCpTotal` varchar(254) NOT NULL DEFAULT '0',
  `intCaps` varchar(254) NOT NULL DEFAULT '0',
  `strPlace` varchar(254) NOT NULL DEFAULT '0',
  `dtLastUpdatedWarSheet` varchar(254) DEFAULT NULL,
  `dtLastUpdatedPlayerSheet` varchar(254) NOT NULL,
  `arrScoreBoardScore1` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore2` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore3` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore4` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore5` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore6` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore7` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore8` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore9` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore10` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore11` varchar(254) DEFAULT NULL,
  `arrScoreBoardScore12` varchar(254) DEFAULT NULL,
  `arrScoreBoardName1` varchar(254) DEFAULT NULL,
  `arrScoreBoardName2` varchar(254) DEFAULT NULL,
  `arrScoreBoardName3` varchar(254) DEFAULT NULL,
  `arrScoreBoardName4` varchar(254) DEFAULT NULL,
  `arrScoreBoardName5` varchar(254) DEFAULT NULL,
  `arrScoreBoardName6` varchar(254) DEFAULT NULL,
  `arrScoreBoardName7` varchar(254) DEFAULT NULL,
  `arrScoreBoardName8` varchar(254) DEFAULT NULL,
  `arrScoreBoardName9` varchar(254) DEFAULT NULL,
  `arrScoreBoardName10` varchar(254) DEFAULT NULL,
  `arrScoreBoardName11` varchar(254) DEFAULT NULL,
  `arrScoreBoardName12` varchar(254) DEFAULT NULL,
  `numClans` varchar(254) DEFAULT NULL,
  `intTotalHours` varchar(254) NOT NULL DEFAULT '0',
  PRIMARY KEY (`primary`)
) ENGINE=MyISAM AUTO_INCREMENT=1767 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tourney`
--

DROP TABLE IF EXISTS `tourney`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tourney` (
  `primary` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `groupId` varchar(254) NOT NULL,
  `userId` varchar(254) NOT NULL,
  `groupName` varchar(254) NOT NULL,
  PRIMARY KEY (`primary`)
) ENGINE=MyISAM AUTO_INCREMENT=213 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `twitter`
--

DROP TABLE IF EXISTS `twitter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twitter` (
  `varTimeTweetCreatedStamp` varchar(254) NOT NULL,
  `varTimeTweetCreated` varchar(254) NOT NULL COMMENT 'DATE_ISO8601 Format',
  `varTweetId` varchar(254) NOT NULL,
  `varText` text NOT NULL,
  `varSentAt` varchar(254) NOT NULL,
  UNIQUE KEY `varTweetId` (`varTweetId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `words`
--

DROP TABLE IF EXISTS `words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `words` (
  `Primary` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Word` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Primary`),
  UNIQUE KEY `Word` (`Word`)
) ENGINE=InnoDB AUTO_INCREMENT=7032 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `guessWhoPool`
--

/*!50001 DROP VIEW IF EXISTS `guessWhoPool`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`jakebathman`@`%.%.%.%` SQL SECURITY DEFINER */
/*!50001 VIEW `guessWhoPool` AS select count(0) AS `COUNT(*)`,`M`.`primary` AS `primary`,`M`.`id` AS `id`,`M`.`timestamp` AS `timestamp`,`M`.`source_guid` AS `source_guid`,`M`.`created_at` AS `created_at`,`M`.`user_id` AS `user_id`,`M`.`group_id` AS `group_id`,`M`.`name` AS `name`,`M`.`avatar_url` AS `avatar_url`,`M`.`text` AS `text`,`M`.`system` AS `system`,`M`.`attachments_type` AS `attachments_type`,`M`.`attachments_url` AS `attachments_url`,`M`.`attachments_name` AS `attachments_name`,`M`.`attachments_lat` AS `attachments_lat`,`M`.`attachments_lng` AS `attachments_lng`,`M`.`textLength` AS `textLength`,`M`.`numFavorites` AS `numFavorites`,`M`.`favoritedBy` AS `favoritedBy`,`M`.`isTryBot` AS `isTryBot` from `messages` `M` where ((`M`.`numFavorites` >= 4) and (`M`.`user_id` > 999999)) group by `M`.`user_id` having (count(0) > 250) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latestUserNames`
--

/*!50001 DROP VIEW IF EXISTS `latestUserNames`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`jakebathman`@`%.%.%.%` SQL SECURITY DEFINER */
/*!50001 VIEW `latestUserNames` AS select `M`.`name` AS `name`,`M`.`user_id` AS `user_id`,`M`.`created_at` AS `created_at` from `messages` `M` group by `M`.`user_id` order by `M`.`created_at` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-29  3:21:36
