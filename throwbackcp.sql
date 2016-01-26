-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 01, 2014 at 12:02 AM
-- Server version: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `throwbackcp`
--
CREATE DATABASE IF NOT EXISTS `throwbackcp` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `throwbackcp`;

-- --------------------------------------------------------

--
-- Table structure for table `access`
--

CREATE TABLE IF NOT EXISTS `access` (
  `ipaddress` varchar(50) NOT NULL,
  `date` varchar(50) NOT NULL,
  `referrer` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `parameters`
--

CREATE TABLE IF NOT EXISTS `parameters` (
  `id` varchar(255) NOT NULL,
  `cbperiod` varchar(50) NOT NULL,
  `lastupdate` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `privileges` varchar(25) NOT NULL DEFAULT '1',
  `version` varchar(25) NOT NULL DEFAULT '0',
  `ipaddress` varchar(50) NOT NULL,
  `proxyenabled` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `targets`
--

CREATE TABLE IF NOT EXISTS `targets` (
  `id` varchar(255) NOT NULL,
  `externalip` varchar(50) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `lastupdate` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE IF NOT EXISTS `tasks` (
  `type` varchar(10) NOT NULL,
  `id` varchar(255) NOT NULL,
  `command` varchar(500) NOT NULL,
  `arguments` varchar(500) NOT NULL,
  `runas` varchar(25) NOT NULL,
  `key` varchar(50) NOT NULL,
  `status` varchar(25) NOT NULL,
  `results` mediumtext NOT NULL,
  `opentime` varchar(50) NOT NULL,
  `closetime` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `lastlogin` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
