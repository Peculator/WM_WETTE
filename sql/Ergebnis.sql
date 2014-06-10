-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 10. Jun 2014 um 17:26
-- Server Version: 5.5.37-0ubuntu0.14.04.1
-- PHP-Version: 5.5.9-1ubuntu4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `WMWETTE`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Ergebnis`
--

CREATE TABLE IF NOT EXISTS `Ergebnis` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ToreA` int(11) NOT NULL,
  `ToreB` int(11) NOT NULL,
  `Extra` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `Ergebnis`
--

INSERT INTO `Ergebnis` (`ID`, `ToreA`, `ToreB`, `Extra`) VALUES
(0, 0, 0, '0');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
