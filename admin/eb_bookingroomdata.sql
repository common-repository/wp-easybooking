-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Φιλοξενητής: localhost
-- Χρόνος δημιουργίας: 03 Φεβ 2012 στις 14:52:31
-- Έκδοση Διακομιστή: 5.5.16
-- Έκδοση PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Βάση: `wp_db_two`
--

-- --------------------------------------------------------

--
-- Δομή Πίνακα για τον Πίνακα `eb_bookingroomdata`
--

CREATE TABLE IF NOT EXISTS `eb_bookingroomdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bookingID` int(11) NOT NULL,
  `roomID` int(11) NOT NULL,
  `roomCost` varchar(10) NOT NULL,
  `roomCost_siteCur` varchar(10) NOT NULL,
  `businessID` int(11) NOT NULL,
  `noOfBabies` int(11) NOT NULL,
  `extraBedNum` int(11) NOT NULL,
  `extraBedPrice` varchar(10) NOT NULL,
  `HBoptions` varchar(150) NOT NULL,
  `guestFullName` varchar(100) NOT NULL,
  `dateRange_start` datetime NOT NULL,
  `dateRange_end` datetime NOT NULL,
  `canceled` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
