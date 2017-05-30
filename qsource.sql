-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2017 at 03:29 PM
-- Server version: 5.5.49-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `qsource`
--
CREATE DATABASE IF NOT EXISTS `qsource` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `qsource`;

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE IF NOT EXISTS `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `post_text` text COLLATE utf8_unicode_ci NOT NULL,
  `reply_id` int(11) DEFAULT NULL,
  `queue_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`id`, `user_id`, `date_time`, `post_text`, `reply_id`, `queue_id`) VALUES
(26, 11, '2017-05-18 18:02:37', 'Student 1 Queue 1 Post 1', 26, 20),
(27, 12, '2017-05-18 18:09:23', 'Student 2 Queue 1 Post 1', 27, 21),
(28, 12, '2017-05-19 03:14:13', 'Student 2 Queue 1 Reply to Post 1', 26, 20),
(29, 12, '2017-05-19 03:14:47', 'Edited Post', 29, 20),
(31, 11, '2017-05-19 03:16:18', 'sdf', 31, 22),
(32, 12, '2017-05-19 03:18:52', 'asd', 32, 23);

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE IF NOT EXISTS `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `join_code` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `queue`
--

INSERT INTO `queue` (`id`, `name`, `join_code`) VALUES
(20, 'Student 1 Queue 1', '111111'),
(21, 'Student 2 Queue 1', '222222'),
(22, 'test queue', '123123'),
(23, 'test 2', 'asdasd');

-- --------------------------------------------------------

--
-- Table structure for table `queue_privilege`
--

CREATE TABLE IF NOT EXISTS `queue_privilege` (
  `name_short` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `value` int(11) NOT NULL,
  `name_full` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`name_short`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `queue_privilege`
--

INSERT INTO `queue_privilege` (`name_short`, `value`, `name_full`, `description`) VALUES
('delete_other', 16, 'Delete posts made by other users', 'This user is allowed to delete posts made by other users.\r\nThis should only be granted to trusted moderators!'),
('delete_own', 4, 'Delete posts made by the user', 'This user is allowed to delete a post they have previously made.'),
('edit_other', 8, 'Edit posts made by other users', 'This user is allowed to edit posts made by other users.\r\nThis should only be granted to trusted moderators!'),
('edit_own', 2, 'User may edit their own posts', 'This user is allowed to edit a post they have previously made.'),
('post_own', 1, 'User may post to this queue', 'This user has permission to make new posts to the queue. All users should normally have this privilege unless you only want them to be able see posts made by other users.'),
('reply_both', 32, 'Reply to posts', 'This user has permission to reply to posts in the queue. All users should normally have this privilege if you want users to be able to reply. Users will also be able to reply to their own posts.');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `screen_name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(254) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `time_zone` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'America/Los_Angeles',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `screen_name`, `username`, `password`, `time_zone`) VALUES
(11, 'Student 1', 'student1@test.com', '$2y$10$tauQJGPPugMI/5XQKZ1ONenKSwjpSpO4iUTcCe0iORZV36Goq33vG', 'America/Los_Angeles'),
(12, 'student2@test.com', 'student2@test.com', '$2y$10$AzZOM1PNv.M7dVRd7taGVuPBdlBImbqh3TGrrn/sp/kKeZ7nAweWq', 'America/Los_Angeles');

-- --------------------------------------------------------

--
-- Table structure for table `user_queue`
--

CREATE TABLE IF NOT EXISTS `user_queue` (
  `queue_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `privilege` int(11) NOT NULL,
  PRIMARY KEY (`queue_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_queue`
--

INSERT INTO `user_queue` (`queue_id`, `user_id`, `privilege`) VALUES
(20, 11, 63),
(20, 12, 39),
(21, 11, 39),
(21, 12, 63);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
