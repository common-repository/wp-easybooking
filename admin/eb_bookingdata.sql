-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Φιλοξενητής: localhost
-- Χρόνος δημιουργίας: 03 Φεβ 2012 στις 14:52:19
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
-- Δομή Πίνακα για τον Πίνακα `eb_bookingdata`
--

CREATE TABLE IF NOT EXISTS `eb_bookingdata` (
  `bookingID` int(11) NOT NULL AUTO_INCREMENT,
  `pin` int(4) NOT NULL,
  `businessID` int(11) NOT NULL,
  `customerID` int(11) NOT NULL,
  `customer_fname` varchar(50) NOT NULL,
  `customer_lname` varchar(50) NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `customer_tel` varchar(15) NOT NULL,
  `customer_country` varchar(40) NOT NULL,
  `customer_city` int(11) NOT NULL,
  `bookedNights` int(11) NOT NULL,
  `dateRange_start` datetime NOT NULL,
  `dateRange_end` datetime NOT NULL,
  `booking_date` datetime NOT NULL,
  `booking_localDate` datetime NOT NULL,
  `numberOfRooms` int(11) NOT NULL,
  `booking_currency` varchar(10) NOT NULL,
  `booking_paymentMethod` varchar(10) NOT NULL,
  `booking_paymentCharge` varchar(10) NOT NULL,
  `txn_id` varchar(40) NOT NULL,
  `booking_deposit` varchar(10) NOT NULL,
  `booking_total` varchar(10) NOT NULL,
  `booking_totalBCUR` varchar(10) NOT NULL,
  `booking_canceled_by_user` varchar(10) NOT NULL,
  `booking_cancelation_cost` varchar(10) NOT NULL,
  `booking_cancelation_date` varchar(10) NOT NULL,
  `booking_status` varchar(10) NOT NULL,  
  PRIMARY KEY (`bookingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
