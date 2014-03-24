-- Generation Time: Mar 24, 2014 at 09:27 AM

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `DATABASE`
--

-- --------------------------------------------------------

--
-- Table structure for table `emailtally`
--

CREATE TABLE IF NOT EXISTS `emailtally` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sent` smallint(5) unsigned NOT NULL,
  `runfinished` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `searcheschecked` int(10) unsigned DEFAULT NULL,
  `listingschecked` int(10) unsigned DEFAULT NULL,
  `incorrectListing` int(10) unsigned DEFAULT NULL,
  `oldlistings` int(10) unsigned DEFAULT NULL,
  `currentlistings` int(10) unsigned DEFAULT NULL,
  `tooexpensive` int(10) unsigned DEFAULT NULL,
  `outofarea` int(10) unsigned DEFAULT NULL,
  `postschecked` int(10) unsigned DEFAULT NULL,
  `brokenposts` int(10) unsigned DEFAULT NULL,
  `emptylistings` int(10) unsigned DEFAULT NULL,
  `duration` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`runfinished`),
  KEY `emptylistings` (`emptylistings`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `enginestatus`
--

CREATE TABLE IF NOT EXISTS `enginestatus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(4) NOT NULL DEFAULT 'up',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `salt` varchar(512) NOT NULL,
  `password` varchar(512) NOT NULL,
  `email` varchar(512) NOT NULL,
  `url` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'display whole url',
  `displayEmail` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'display watching email',
  `price` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'display max price',
  `location` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'display location',
  `sendfrom` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'mail from posting',
  `sortColumn` varchar(8) NOT NULL,
  `sortOrder` varchar(4) NOT NULL DEFAULT 'asc',
  `enginealerts` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'Alert user about engine status',
  `access` tinyint(2) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `code` char(36) DEFAULT NULL,
  `pro` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pro` (`pro`),
  KEY `deleted` (`deleted`),
  KEY `enginealerts` (`enginealerts`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vmail`
--

CREATE TABLE IF NOT EXISTS `vmail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(512) NOT NULL,
  `lastnotified` date NOT NULL,
  `validated` tinyint(2) unsigned NOT NULL,
  `code` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`(333))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `watches`
--

CREATE TABLE IF NOT EXISTS `watches` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) CHARACTER SET latin1 NOT NULL,
  `url` varchar(250) CHARACTER SET latin1 NOT NULL,
  `maxprice` varchar(7) CHARACTER SET latin1 NOT NULL DEFAULT 'none',
  `location` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT 'any',
  `datePosted` int(10) NOT NULL,
  `description` varchar(250) CHARACTER SET latin1 NOT NULL,
  `pid` int(10) unsigned DEFAULT NULL,
  `userid` int(11) NOT NULL DEFAULT '37',
  `deleted` tinyint(3) unsigned NOT NULL,
  `replyto` varchar(320) DEFAULT NULL,
  `added` datetime NOT NULL,
  `updated` bigint(20) unsigned DEFAULT NULL,
  `empty` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `delete` (`deleted`),
  KEY `updated` (`updated`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
