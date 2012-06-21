-- --------------------------------------------------------

--
-- Table structure for table `deployment`
--

CREATE TABLE IF NOT EXISTS `deployment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern` varchar(10) NOT NULL,
  `platform_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pattern` (`pattern`,`platform_id`),
  UNIQUE KEY `platform_id` (`platform_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Table structure for table `platform`
--

CREATE TABLE IF NOT EXISTS `platform` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(100) NOT NULL,
  `remote_directory` varchar(100) NOT NULL,
  `deployments_limit` int(11) NOT NULL DEFAULT '10',
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `deployment_method` enum('ftp','ssh','sftp','scp','copy') NOT NULL DEFAULT 'ftp',
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE IF NOT EXISTS `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `repository_url` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `final_platform_id` int(11) DEFAULT NULL COMMENT 'ID of the record in ''platform'' table representing default target platform to deploy to',
  `is_public` tinyint(1) DEFAULT '0' COMMENT 'is project available for the public (all users using SCMFViz application) or not',
  `username` varchar(40) DEFAULT NULL COMMENT 'username for accessing project repository',
  `password` varchar(40) DEFAULT NULL COMMENT 'password for accessing project repository',
  `deployment_script` varchar(60) NOT NULL COMMENT 'filename of the deployment script to run',
  `starting_version` enum('1.x.x','0.x.x') NOT NULL COMMENT 'number to start major version numbering for the project',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='list of the project associated with specific repository url' AUTO_INCREMENT=5 ;
