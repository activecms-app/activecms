/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Configs`
--

DROP TABLE IF EXISTS `Configs`;
CREATE TABLE `Configs` (
  `Path` varchar(100) NOT NULL,
  `Value` text DEFAULT NULL,
  PRIMARY KEY (`Path`)
) ENGINE=MyISAM;

--
-- Table structure for table `Filters`
--

DROP TABLE IF EXISTS `Filters`;
CREATE TABLE `Filters` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Filters_Id` int(10) unsigned DEFAULT NULL,
  `FilterKey` varchar(255) NOT NULL,
  `FilterDate` datetime DEFAULT NULL,
  `ExpirationDate` datetime DEFAULT NULL,
  `Total` int(10) unsigned NOT NULL,
  `Status` enum('outdated','processing','ready') NOT NULL DEFAULT 'processing',
  PRIMARY KEY (`Id`),
  KEY `idx_Filters_ParentKey` (`FilterKey`,`Filters_Id`),
  KEY `fk_Filters_Filters_idx` (`Filters_Id`),
  CONSTRAINT `fk_Filters_Filters` FOREIGN KEY (`Filters_Id`) REFERENCES `Filters` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `Filters_Objects`
--

DROP TABLE IF EXISTS `Filters_Objects`;
CREATE TABLE `Filters_Objects` (
  `Filters_Id` int(10) unsigned NOT NULL,
  `Objects_Id` int(10) unsigned NOT NULL,
  `Position` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`Filters_Id`,`Objects_Id`),
  KEY `idx_Filters_Objects_Filter` (`Filters_Id`),
  KEY `fk_Filters_Objets_Objects_idx` (`Objects_Id`),
  CONSTRAINT `fk_Filters_Objects_Filters` FOREIGN KEY (`Filters_Id`) REFERENCES `Filters` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Filters_Objets_Objects` FOREIGN KEY (`Objects_Id`) REFERENCES `Objects` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `LogsObjects`
--

DROP TABLE IF EXISTS `LogsObjects`;
CREATE TABLE `LogsObjects` (
  `WebKey` varchar(64) NOT NULL,
  `Objects_Id` int(10) unsigned NOT NULL,
  `Version` smallint(5) unsigned NOT NULL,
  `Types_Code` varchar(10) NOT NULL,
  `LogsDate` datetime NOT NULL,
  `IPv4` datetime NOT NULL,
  PRIMARY KEY (`WebKey`,`Objects_Id`,`LogsDate`)
) ENGINE=MyISAM;

--
-- Table structure for table `LogsObjectsSummary`
--

DROP TABLE IF EXISTS `LogsObjectsSummary`;
CREATE TABLE `LogsObjectsSummary` (
  `Id` smallint(5) NOT NULL,
  `SummaryRange` enum('day','week','mon','year') NOT NULL,
  `Types_Code` varchar(10) NOT NULL,
  `SummaryDate` datetime NOT NULL,
  PRIMARY KEY (`Id`,`SummaryRange`,`Types_Code`,`SummaryDate`)
) ENGINE=InnoDB;

--
-- Table structure for table `LogsObjectsSummaryData`
--

DROP TABLE IF EXISTS `LogsObjectsSummaryData`;
CREATE TABLE `LogsObjectsSummaryData` (
  `Summary_Id` smallint(5) NOT NULL,
  `Objects_Id` int(10) unsigned NOT NULL,
  `Total` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`Summary_Id`,`Objects_Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `LogsTypes`
--

DROP TABLE IF EXISTS `LogsTypes`;
CREATE TABLE `LogsTypes` (
  `Code` varchar(10) NOT NULL,
  `Title` text DEFAULT NULL,
  PRIMARY KEY (`Code`)
) ENGINE=MyISAM;

--
-- Table structure for table `Medios`
--

DROP TABLE IF EXISTS `Medios`;
CREATE TABLE `Medios` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `MediosFolder_Id` smallint(6) NOT NULL,
  `FileName` varchar(255) NOT NULL,
  `Title` text DEFAULT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `MediosFolder`
--

DROP TABLE IF EXISTS `MediosFolder`;
CREATE TABLE `MediosFolder` (
  `Id` smallint(6) NOT NULL AUTO_INCREMENT,
  `MediosFolder_Parent` smallint(6) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Path` text DEFAULT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_MediosFolder_MediosFolder` (`MediosFolder_Parent`),
  CONSTRAINT `fk_MediosFolder_MediosFolder` FOREIGN KEY (`MediosFolder_Parent`) REFERENCES `MediosFolder` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `MediosTags`
--

DROP TABLE IF EXISTS `MediosTags`;
CREATE TABLE `MediosTags` (
  `Id` smallint(5) unsigned NOT NULL,
  `Tag` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Medios_Tags`
--

DROP TABLE IF EXISTS `Medios_Tags`;
CREATE TABLE `Medios_Tags` (
  `Medios_Id` int(10) unsigned NOT NULL,
  `MediosTags_Id` smallint(5) unsigned NOT NULL,
  KEY `fk_Medios_Tags_Medios` (`Medios_Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Menus`
--

DROP TABLE IF EXISTS `Menus`;
CREATE TABLE `Menus` (
  `Id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Code` varchar(30) NOT NULL,
  `Title` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `MenusItems`
--

DROP TABLE IF EXISTS `MenusItems`;
CREATE TABLE `MenusItems` (
  `Menus_Id` smallint(5) unsigned NOT NULL,
  `Num` smallint(5) unsigned NOT NULL,
  `MenusItems_Num` tinyint(5) unsigned DEFAULT NULL,
  `Position` tinyint(4) NOT NULL,
  `Title` text DEFAULT NULL,
  `ItemType` enum('reference','link','none','separator') NOT NULL DEFAULT 'none',
  `Link` text DEFAULT NULL,
  `Objects_Id` int(10) unsigned DEFAULT NULL,
  `ItemStatus` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `Options` text DEFAULT NULL,
  PRIMARY KEY (`Menus_Id`,`Num`)
) ENGINE=InnoDB;

--
-- Table structure for table `Messages`
--

DROP TABLE IF EXISTS `Messages`;
CREATE TABLE `Messages` (
  `Id` int(10) unsigned NOT NULL,
  `MessageDate` datetime DEFAULT NULL,
  `Users_Id` smallint(5) unsigned DEFAULT NULL,
  `Title` text DEFAULT NULL,
  `Body` text DEFAULT NULL,
  `Objects_Id` int(10) unsigned DEFAULT NULL,
  `Objects_Version` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Messajes_Users`
--

DROP TABLE IF EXISTS `Messajes_Users`;
CREATE TABLE `Messajes_Users` (
  `Messages_Id` int(10) unsigned NOT NULL,
  `Users_Id` smallint(5) unsigned NOT NULL,
  `Dispatched` enum('no','yes') DEFAULT NULL,
  `Viewed` enum('no','yes') DEFAULT NULL,
  `DateViewed` datetime DEFAULT NULL,
  PRIMARY KEY (`Messages_Id`,`Users_Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Objects`
--

DROP TABLE IF EXISTS `Objects`;
CREATE TABLE `Objects` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Version` smallint(5) unsigned NOT NULL,
  `Types_Id` smallint(5) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL,
  `DisplayBegin` datetime DEFAULT NULL,
  `DisplayEnd` datetime DEFAULT NULL,
  `Template` text DEFAULT NULL,
  `Published` enum('no','yes') NOT NULL DEFAULT 'no',
  `LastChange` datetime DEFAULT NULL,
  `LastUser_Id` smallint(5) unsigned DEFAULT NULL,
  `Deleted` enum('no','yes') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`Id`),
  KEY `fk_Objects_Types_idx` (`Types_Id`),
  KEY `idx_Objects_Deleted` (`Deleted`),
  KEY `idx_Objects_Published` (`Published`),
  CONSTRAINT `fk_Objects_Types` FOREIGN KEY (`Types_Id`) REFERENCES `Types` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `ObjectsData`
--

DROP TABLE IF EXISTS `ObjectsData`;
CREATE TABLE `ObjectsData` (
  `Objects_Id` int(10) unsigned NOT NULL,
  `Version` smallint(5) unsigned NOT NULL,
  `TypesData_Id` smallint(5) unsigned NOT NULL,
  `Num` smallint(6) NOT NULL,
  `ValueText` mediumtext DEFAULT NULL,
  `ValueNum` double DEFAULT NULL,
  `ValueDate` datetime DEFAULT NULL,
  PRIMARY KEY (`Num`,`Version`,`Objects_Id`,`TypesData_Id`),
  KEY `idx_ObjectsData_ObjectsTypesData` (`Objects_Id`,`TypesData_Id`),
  KEY `idx_ObjectsData_Date` (`ValueDate`),
  KEY `fk_ObjectsData_TypesData_idx` (`TypesData_Id`),
  KEY `fk_ObjectsData_ObjectsVersion_idx` (`Objects_Id`,`Version`),
  CONSTRAINT `fk_ObjectsData_ObjectsVersion` FOREIGN KEY (`Objects_Id`, `Version`) REFERENCES `ObjectsVersion` (`Objects_Id`, `Version`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ObjectsData_TypesData` FOREIGN KEY (`TypesData_Id`) REFERENCES `TypesData` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `ObjectsHistory`
--

DROP TABLE IF EXISTS `ObjectsHistory`;
CREATE TABLE `ObjectsHistory` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Objects_Id` int(10) unsigned NOT NULL,
  `Version` smallint(5) unsigned DEFAULT NULL,
  `Action` VARCHAR(10) NOT NULL,
  `ActionDate` datetime NOT NULL,
  `Users_Id` smallint(5) unsigned NOT NULL,
  `Comment` text DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_ObjectsHistory_ObjectsVersion_idx` (`Objects_Id`),
  CONSTRAINT `fk_ObjectsHistory_ObjectsVersion` FOREIGN KEY (`Objects_Id`) REFERENCES `Objects` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `ObjectsLogs`
--

DROP TABLE IF EXISTS `ObjectsLogs`;
CREATE TABLE `ObjectsLogs` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Objects_Id` int(10) unsigned NOT NULL,
  `Version` smallint(5) unsigned NOT NULL,
  `Date` datetime NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM;

--
-- Table structure for table `ObjectsVersion`
--

DROP TABLE IF EXISTS `ObjectsVersion`;
CREATE TABLE `ObjectsVersion` (
  `Objects_Id` int(10) unsigned NOT NULL,
  `Version` smallint(5) unsigned NOT NULL,
  `Title` text DEFAULT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`Version`,`Objects_Id`),
  KEY `fk_ObjectsVersion_Objects_idx` (`Objects_Id`),
  CONSTRAINT `fk_ObjectsVersion_Objects` FOREIGN KEY (`Objects_Id`) REFERENCES `Objects` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `Objects_Parents`
--

DROP TABLE IF EXISTS `Objects_Parents`;
CREATE TABLE `Objects_Parents` (
  `Objects_Id` int(10) unsigned NOT NULL,
  `Objects_Parent` int(10) unsigned NOT NULL,
  `Position` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`Objects_Id`,`Objects_Parent`),
  KEY `fk_Objects_Parents_Objects` (`Objects_Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Objects_Roles`
--

DROP TABLE IF EXISTS `Objects_Roles`;
CREATE TABLE `Objects_Roles` (
  `Objects_Id` int(10) unsigned NOT NULL,
  `Roles_Id` tinyint(3) unsigned NOT NULL,
  `Permission` enum('none','read','write','publish') NOT NULL,
  PRIMARY KEY (`Objects_Id`,`Roles_Id`),
  KEY `fk_Objects_Groups_Groups` (`Roles_Id`),
  CONSTRAINT `fk_Objects_Roles_Objects` FOREIGN KEY (`Objects_Id`) REFERENCES `Objects` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Objects_Roles_Roles` FOREIGN KEY (`Roles_Id`) REFERENCES `Roles` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `Objects_Types`
--

DROP TABLE IF EXISTS `Objects_Types`;
CREATE TABLE `Objects_Types` (
  `Objects_Id` int(10) unsigned NOT NULL,
  `Types_Id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`Objects_Id`,`Types_Id`),
  KEY `fk_Objects_Types_Objects` (`Objects_Id`),
  KEY `fk_Objects_Types_Types_idx` (`Types_Id`),
  CONSTRAINT `fk_Objects_Types_Objects` FOREIGN KEY (`Objects_Id`) REFERENCES `Objects` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Objects_Types_Types` FOREIGN KEY (`Types_Id`) REFERENCES `Types` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `Objects_Users`
--

DROP TABLE IF EXISTS `Objects_Users`;
CREATE TABLE `Objects_Users` (
  `Objects_Id` int(10) unsigned NOT NULL,
  `Users_Id` smallint(5) unsigned NOT NULL,
  `Permission` enum('none','read','write','publish') NOT NULL,
  PRIMARY KEY (`Objects_Id`,`Users_Id`),
  CONSTRAINT `fk_Objects_Users_Objects` FOREIGN KEY (`Objects_Id`) REFERENCES `Objects` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `Permissions`
--

DROP TABLE IF EXISTS `Permissions`;
CREATE TABLE `Permissions` (
  `Id` tinyint(3) unsigned NOT NULL,
  `Tittle` text NOT NULL,
  `Description` text DEFAULT NULL,
  `PermissionsCategories_Id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `fk_Permissions_PermissionsCategories_idx` (`PermissionsCategories_Id`),
  CONSTRAINT `fk_Permissions_PermissionsCategories` FOREIGN KEY (`PermissionsCategories_Id`) REFERENCES `PermissionsCategories` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `PermissionsCategories`
--

DROP TABLE IF EXISTS `PermissionsCategories`;
CREATE TABLE `PermissionsCategories` (
  `Id` tinyint(3) unsigned NOT NULL,
  `Title` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Plugins`
--

DROP TABLE IF EXISTS `Plugins`;
CREATE TABLE `Plugins` (
  `Code` varchar(20) NOT NULL,
  `Title` text NOT NULL,
  `Description` text DEFAULT NULL,
  `PluginStatus` enum('disabled','enabled') NOT NULL,
  PRIMARY KEY (`Code`)
) ENGINE=InnoDB;

--
-- Table structure for table `PluginsRegisters`
--

DROP TABLE IF EXISTS `PluginsRegisters`;
CREATE TABLE `PluginsRegisters` (
  `Plugins_Code` varchar(20) NOT NULL,
  `Register` enum('header','footer','content','routing','import','export','elements','functions') NOT NULL,
  PRIMARY KEY (`Plugins_Code`,`Register`),
  KEY `idx_PluginsRegisters_Plugins` (`Plugins_Code`),
  CONSTRAINT `fk_PluginsRegisters_Plugins` FOREIGN KEY (`Plugins_Code`) REFERENCES `Plugins` (`Code`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `Roles`
--

DROP TABLE IF EXISTS `Roles`;
CREATE TABLE `Roles` (
  `Id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `Name` text NOT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Roles_Permissions`
--

DROP TABLE IF EXISTS `Roles_Permissions`;
CREATE TABLE `Roles_Permissions` (
  `Roles_Id` tinyint(3) unsigned NOT NULL,
  `Permissions_Id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`Roles_Id`,`Permissions_Id`),
  KEY `fk_Groups_Access_Groups` (`Roles_Id`),
  KEY `fk_Groups_Access_Access` (`Permissions_Id`),
  CONSTRAINT `fk_Roles_Permissions_Permissions` FOREIGN KEY (`Permissions_Id`) REFERENCES `Permissions` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Roles_Permissions_Roles` FOREIGN KEY (`Roles_Id`) REFERENCES `Roles` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `Themes`
--

DROP TABLE IF EXISTS `Themes`;
CREATE TABLE `Themes` (
  `Id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Title` text NOT NULL,
  `ThemeStatus` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Types`
--

DROP TABLE IF EXISTS `Types`;
CREATE TABLE `Types` (
  `Id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Name` text DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Class` enum('file','folder') DEFAULT NULL,
  `Template` text DEFAULT NULL,
  `TypeStatus` enum('active','disabled','deleted') DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `idx_Types_Class` (`Class`)
) ENGINE=InnoDB;

--
-- Table structure for table `TypesData`
--

DROP TABLE IF EXISTS `TypesData`;
CREATE TABLE `TypesData` (
  `Id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Types_Id` smallint(5) unsigned DEFAULT NULL,
  `TypesGroups_Id` smallint(5) unsigned DEFAULT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `TypesElements_Code` varchar(20) DEFAULT NULL,
  `Title` text DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Position` tinyint(3) unsigned DEFAULT NULL,
  `DefaultValue` text DEFAULT NULL,
  `Options` text DEFAULT NULL,
  `Deleted` enum('no','yes') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`Id`),
  KEY `fk_TypesData_Types_idx` (`Types_Id`),
  KEY `fk_TypesData_TypesElements_idx` (`TypesElements_Code`),
  KEY `fk_TypesDatat_TypesGroups_idx` (`TypesGroups_Id`),
  CONSTRAINT `fk_TypesData_Types` FOREIGN KEY (`Types_Id`) REFERENCES `Types` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_TypesData_TypesElements` FOREIGN KEY (`TypesElements_Code`) REFERENCES `TypesElements` (`Code`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_TypesDatat_TypesGroups` FOREIGN KEY (`TypesGroups_Id`) REFERENCES `TypesGroups` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `TypesElements`
--

DROP TABLE IF EXISTS `TypesElements`;
CREATE TABLE `TypesElements` (
  `Code` varchar(20) NOT NULL,
  `Name` text DEFAULT NULL,
  `ElementStatus` enum('active','inactive') DEFAULT NULL,
  PRIMARY KEY (`Code`)
) ENGINE=InnoDB;

--
-- Table structure for table `TypesGroups`
--

DROP TABLE IF EXISTS `TypesGroups`;
CREATE TABLE `TypesGroups` (
  `Id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Types_Id` smallint(5) unsigned NOT NULL,
  `Title` text DEFAULT NULL,
  `Position` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `TypesGroups_Types_idx` (`Types_Id`),
  CONSTRAINT `fk_TypesGroups_Types` FOREIGN KEY (`Types_Id`) REFERENCES `Types` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `TypesTemplates`
--

DROP TABLE IF EXISTS `TypesTemplates`;
CREATE TABLE `TypesTemplates` (
  `Types_Id` smallint(5) unsigned NOT NULL,
  `Template` varchar(255) NOT NULL,
  PRIMARY KEY (`Template`,`Types_Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `Types_Parents`
--

DROP TABLE IF EXISTS `Types_Parents`;
CREATE TABLE `Types_Parents` (
  `Types_Id` smallint(5) unsigned NOT NULL,
  `Types_Parent` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`Types_Id`,`Types_Parent`),
  KEY `fk_Types_Parents_Types` (`Types_Id`),
  KEY `fk_Types_Parents_TypesP_idx` (`Types_Parent`),
  CONSTRAINT `fk_Types_Parents_Types` FOREIGN KEY (`Types_Id`) REFERENCES `Types` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Types_Parents_TypesP` FOREIGN KEY (`Types_Parent`) REFERENCES `Types` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
CREATE TABLE `Users` (
  `Id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) NOT NULL,
  `Email` text DEFAULT NULL,
  `Pass` varchar(64) DEFAULT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `Notify` enum('always','never','noconected') NOT NULL DEFAULT 'always',
  `Observation` text DEFAULT NULL,
  `RecoverCode` varchar(8) DEFAULT NULL,
  `UserStatus` enum('active','disabled','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

--
-- Table structure for table `UsersSessions`
--

DROP TABLE IF EXISTS `UsersSessions`;
CREATE TABLE `UsersSessions` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AccessKey` varchar(64) NOT NULL,
  `Users_Id` smallint(5) unsigned NOT NULL,
  `MakeDate` datetime NOT NULL,
  `IP` varbinary(16) DEFAULT NULL,
  `ExpirationDate` datetime NOT NULL,
  `Status` enum('active','terminated') DEFAULT 'active',
  PRIMARY KEY (`Id`),
  KEY `idx_Sessions_KeySession` (`AccessKey`)
) ENGINE=InnoDB;

--
-- Table structure for table `Users_Roles`
--

DROP TABLE IF EXISTS `Users_Roles`;
CREATE TABLE `Users_Roles` (
  `Users_Id` smallint(5) unsigned NOT NULL,
  `Roles_Id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`Users_Id`,`Roles_Id`),
  KEY `Users_Groups_Users_idx` (`Users_Id`),
  KEY `Users_Groups_Groups_idx` (`Roles_Id`),
  CONSTRAINT `fk_Users_Roles_Roles` FOREIGN KEY (`Roles_Id`) REFERENCES `Roles` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `UsersObjects`
--

DROP TABLE IF EXISTS `UsersObjects`;
CREATE TABLE `UsersObjects` (
  `Users_Id` smallint(5) unsigned NOT NULL,
  `Objects_Id` int(10) unsigned NOT NULL,
  `Roles_Id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`Users_Id`,`Roles_Id`),
  KEY `Users_Groups_Users_idx` (`Users_Id`),
  KEY `Users_Groups_Groups_idx` (`Roles_Id`),
  CONSTRAINT `fk_Users_Roles_Roles` FOREIGN KEY (`Roles_Id`) REFERENCES `Roles` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

--
-- Table structure for table `Webs`
--

DROP TABLE IF EXISTS `Webs`;
CREATE TABLE `Webs` (
  `Id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Objects_Id` int(10) unsigned DEFAULT NULL,
  `Name` text DEFAULT NULL,
  `Host` varchar(255) DEFAULT NULL,
  `Url` text DEFAULT NULL,
  `Themes_Id` tinyint(3) unsigned DEFAULT NULL,
  `Published` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

