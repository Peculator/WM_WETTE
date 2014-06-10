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
-- Tabellenstruktur für Tabelle `Mannschaften`
--

CREATE TABLE IF NOT EXISTS `Mannschaften` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(50) NOT NULL,
  `IMAGE` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;

--
-- Daten für Tabelle `Mannschaften`
--

INSERT INTO `Mannschaften` (`ID`, `NAME`, `IMAGE`) VALUES
(6, 'Algerien', 'algerien.png'),
(7, 'Argentinien', 'argentinien.png'),
(8, 'Australien', 'australien.png'),
(9, 'Belgien', 'belgien.png'),
(10, 'Bosnien', 'bosnien.png'),
(11, 'Brasilien', 'brasilien.png'),
(12, 'Chile', 'chile.png'),
(13, 'Costa Rica', 'costarica.png'),
(14, 'Deutschland', 'deutschland.png'),
(15, 'Ecuador', 'ecuador.png'),
(16, 'Elfenbeinküste', 'elfenbeinkueste.png'),
(17, 'England', 'england.png'),
(18, 'Frankreich', 'frankreich.png'),
(19, 'Ghana', 'ghana.png'),
(20, 'Griechenland', 'griechenland.png'),
(21, 'Honduras', 'honduras.png'),
(22, 'Iran', 'iran.png'),
(23, 'Italien', 'italien.png'),
(24, 'Japan', 'japan.png'),
(25, 'Kamerun', 'kamerun.png'),
(26, 'Kolumbien', 'kolumbien.png'),
(27, 'Kroatien', 'kroatien.png'),
(28, 'Mexiko', 'mexico.png'),
(29, 'Niederlande', 'niederlande.png'),
(30, 'Nigeria', 'nigeria.png'),
(31, 'Portugal', 'portugal.png'),
(32, 'Russland', 'russland.png'),
(33, 'Schweiz', 'schweiz.png'),
(34, 'Spanien', 'spanien.png'),
(35, 'Südkorea', 'suedkorea.png'),
(36, 'Uruguay', 'uruguay.png'),
(37, 'USA', 'usa.png');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
