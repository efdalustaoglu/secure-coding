-- MySQL dump 10.13  Distrib 5.6.17, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: bank_db
-- ------------------------------------------------------
-- Server version	5.6.17

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
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER` int(11) NOT NULL,
  `ACCOUNT_NUMBER` int(11) NOT NULL,
  `BALANCE` double NOT NULL DEFAULT '1000000',
  `DATE_CREATED` date NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ACCOUNT_NUMBER_UNIQUE` (`ACCOUNT_NUMBER`),
  KEY `FK_CLIENT_USER_idx` (`USER`),
  CONSTRAINT `FK_CLIENT_USER` FOREIGN KEY (`USER`) REFERENCES `users` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tans`
--

DROP TABLE IF EXISTS `tans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tans` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TAN_NUMBER` varchar(15) NOT NULL,
  `CLIENT_ACCOUNT` int(11) NOT NULL,
  `DATE_CREATED` date NOT NULL,
  `STATUS` varchar(1) NOT NULL DEFAULT 'V',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `TAN_NUMBER_UNIQUE` (`TAN_NUMBER`),
  KEY `FK_CLIENT_ACCOUNT_idx` (`CLIENT_ACCOUNT`),
  CONSTRAINT `FK_CLIENT_ACCOUNT` FOREIGN KEY (`CLIENT_ACCOUNT`) REFERENCES `accounts` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SENDER_ACCOUNT` int(11) NOT NULL,
  `RECIPIENT_ACCOUNT` int(11) NOT NULL,
  `AMOUNT` double NOT NULL,
  `STATUS` varchar(1) NOT NULL,
  `TAN_ID` int(11) NOT NULL,
  `APPROVED_BY` int(11) NOT NULL,
  `DATE_APPROVED` date DEFAULT NULL,
  `DATE_CREATED` date NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `TAN_ID_UNIQUE` (`TAN_ID`),
  KEY `FK_SENDER_ACCOUNT_idx` (`SENDER_ACCOUNT`),
  KEY `FK_RECIPIENT_ACCOUNT_idx` (`RECIPIENT_ACCOUNT`),
  KEY `FK_APPROVED_BY_idx` (`APPROVED_BY`),
  CONSTRAINT `FK_SENDER_ACCOUNT` FOREIGN KEY (`SENDER_ACCOUNT`) REFERENCES `accounts` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_RECIPIENT_ACCOUNT` FOREIGN KEY (`RECIPIENT_ACCOUNT`) REFERENCES `accounts` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_TAN_ID` FOREIGN KEY (`TAN_ID`) REFERENCES `tans` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_APPROVED_BY` FOREIGN KEY (`APPROVED_BY`) REFERENCES `users` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_TYPE` varchar(1) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `PASSWORD` varchar(100) CHARACTER SET utf8 NOT NULL,
  `FIRST_NAME` varchar(100) CHARACTER SET utf8 NOT NULL,
  `LAST_NAME` varchar(100) CHARACTER SET utf8 NOT NULL,
  `DATE_CREATED` date NOT NULL,
  `DATE_APPROVED` date DEFAULT NULL,
  `APPROVED_BY` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `EMAIL_UNIQUE` (`EMAIL`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `users_view`
--

DROP TABLE IF EXISTS `users_view`;
/*!50001 DROP VIEW IF EXISTS `users_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `users_view` (
  `ID` tinyint NOT NULL,
  `USER_TYPE` tinyint NOT NULL,
  `EMAIL` tinyint NOT NULL,
  `FIRST_NAME` tinyint NOT NULL,
  `LAST_NAME` tinyint NOT NULL,
  `DATE_CREATED` tinyint NOT NULL,
  `DATE_APPROVED` tinyint NOT NULL,
  `APPROVED_BY` tinyint NOT NULL,
  `ACCOUNT_ID` tinyint NOT NULL,
  `ACCOUNT_NUMBER` tinyint NOT NULL,
  `BALANCE` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Dumping events for database 'bank_db'
--

--
-- Dumping routines for database 'bank_db'
--

--
-- Final view structure for view `users_view`
--

/*!50001 DROP TABLE IF EXISTS `users_view`*/;
/*!50001 DROP VIEW IF EXISTS `users_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `users_view` AS select `u`.`ID` AS `ID`,`u`.`USER_TYPE` AS `USER_TYPE`,`u`.`EMAIL` AS `EMAIL`,`u`.`FIRST_NAME` AS `FIRST_NAME`,`u`.`LAST_NAME` AS `LAST_NAME`,`u`.`DATE_CREATED` AS `DATE_CREATED`,`u`.`DATE_APPROVED` AS `DATE_APPROVED`,(select concat(`users`.`FIRST_NAME`,' ',`users`.`LAST_NAME`) from `users` where (`users`.`ID` = `u`.`APPROVED_BY`)) AS `APPROVED_BY`,`a`.`ID` AS `ACCOUNT_ID`,`a`.`ACCOUNT_NUMBER` AS `ACCOUNT_NUMBER`,`a`.`BALANCE` AS `BALANCE` from (`users` `u` left join `accounts` `a` on((`u`.`ID` = `a`.`USER`))) where (`u`.`USER_TYPE` <> 'S') */;
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

-- Dump completed on 2015-10-30 19:01:25
