--
-- Initial database for paraphrase tagging
--


-- MySQL dump 10.16  Distrib 10.1.26-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: warp_db
-- ------------------------------------------------------
-- Server version	10.1.26-MariaDB-0+deb9u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `annotation`
--

DROP TABLE IF EXISTS `annotation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `annotation` (
  `anno_sid` bigint(20) NOT NULL AUTO_INCREMENT,
  `pair_sid` bigint(20) NOT NULL,
  `rel_sid` bigint(20) NOT NULL,
  `meta_sid` bigint(20) NOT NULL DEFAULT '0',
  `s1_scope` varchar(124) DEFAULT NULL,
  `s2_scope` varchar(124) DEFAULT NULL,
  `s1_text` varchar(512) DEFAULT NULL,
  `s2_text` varchar(512) NOT NULL,
  `key_s1` varchar(124) DEFAULT NULL,
  `key_s2` varchar(124) DEFAULT NULL,
  `k1_text` varchar(512) DEFAULT NULL,
  `k2_text` varchar(512) DEFAULT NULL,
  `user_sid` bigint(20) DEFAULT NULL,
  `layer` bigint(20) NOT NULL,
  PRIMARY KEY (`anno_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `annotation`
--

LOCK TABLES `annotation` WRITE;
/*!40000 ALTER TABLE `annotation` DISABLE KEYS */;
/*!40000 ALTER TABLE `annotation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dataset`
--

DROP TABLE IF EXISTS `dataset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dataset` (
  `pair_sid` bigint(20) NOT NULL AUTO_INCREMENT,
  `label` bigint(20) NOT NULL,
  `text1_id` varchar(256) NOT NULL,
  `text2_id` varchar(256) NOT NULL,
  `text_1` varchar(1024) NOT NULL,
  `text_2` varchar(1024) NOT NULL,
  `description_1` varchar(512) DEFAULT NULL,
  `description_2` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`pair_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dataset`
--

LOCK TABLES `dataset` WRITE;
/*!40000 ALTER TABLE `dataset` DISABLE KEYS */;
/*!40000 ALTER TABLE `dataset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `layer_types`
--

DROP TABLE IF EXISTS `layer_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `layer_types` (
  `ltype_sid` bigint(20) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(64) NOT NULL,
  `url` varchar(256) NOT NULL,
  `inp_type` bigint(20) NOT NULL,
  PRIMARY KEY (`ltype_sid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `layer_types`
--

LOCK TABLES `layer_types` WRITE;
/*!40000 ALTER TABLE `layer_types` DISABLE KEYS */;
INSERT INTO `layer_types` VALUES (1,'Textual layer','/anno/layer_1',0),(2,'Atomic layer','/anno/layer_2',1);
/*!40000 ALTER TABLE `layer_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `layers`
--

DROP TABLE IF EXISTS `layers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `layers` (
  `layer_sid` bigint(20) NOT NULL AUTO_INCREMENT,
  `layer_name` varchar(32) NOT NULL,
  `layer_type` bigint(20) DEFAULT NULL,
  `parrent` bigint(20) DEFAULT '0',
  `child` bigint(20) DEFAULT '0',
  `source_table` varchar(32) NOT NULL,
  `text_separator` varchar(32) NOT NULL,
  `display_prev` tinyint(1) DEFAULT '0',
  `lock_text` bigint(20) DEFAULT '0',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `layer_order` bigint(20) NOT NULL,
  PRIMARY KEY (`layer_sid`),
  UNIQUE KEY `layer_order` (`layer_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `layers`
--

LOCK TABLES `layers` WRITE;
/*!40000 ALTER TABLE `layers` DISABLE KEYS */;
INSERT INTO `layers` VALUES (0,'paraphrase_types',2,0,0,'dataset',' ',1,0,0,0);
/*!40000 ALTER TABLE `layers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pair_split`
--

DROP TABLE IF EXISTS `pair_split`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pair_split` (
  `split_sid` bigint(20) NOT NULL AUTO_INCREMENT,
  `pair_sid` bigint(20) NOT NULL,
  `user_sid` bigint(20) NOT NULL,
  `annotated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`split_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pair_split`
--

LOCK TABLES `pair_split` WRITE;
/*!40000 ALTER TABLE `pair_split` DISABLE KEYS */;
/*!40000 ALTER TABLE `pair_split` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relations`
--

DROP TABLE IF EXISTS `relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relations` (
  `rel_sid` bigint(20) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(64) DEFAULT NULL,
  `type_key` tinyint(1) DEFAULT NULL,
  `short_type` varchar(32) DEFAULT NULL,
  `parent` bigint(20) DEFAULT '0',
  `layer` bigint(20) DEFAULT '0',
  PRIMARY KEY (`rel_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relations`
--

LOCK TABLES `relations` WRITE;
/*!40000 ALTER TABLE `relations` DISABLE KEYS */;
INSERT INTO `relations` VALUES (1,'Morpology',1,'Morpology',0,0);
INSERT INTO `relations` VALUES (11,'Inflectional Changes',1,'mor_inflectional',1,0);
INSERT INTO `relations` VALUES (12,'Modal-Verb Changes',1,'more_modal_verb',1,0);
INSERT INTO `relations` VALUES (13,'Derivational Changes',1,'mor_derivational',1,0);

INSERT INTO `relations` VALUES (2,'Lexicon',1,'Lexicon',0,0);
INSERT INTO `relations` VALUES (21,'Spelling Changes',1,'lex_spelling',2,0);
INSERT INTO `relations` VALUES (22,'Same-Polarity Substitutions',1,'lex_same_polarity',2,0);
INSERT INTO `relations` VALUES (23,'Synthetic/Analytic Substitutions',1,'lex_synt_ana',2,0);
INSERT INTO `relations` VALUES (24,'Opposite-Polarity Substitutions',1,'lex_opposite_polarity',2,0);
INSERT INTO `relations` VALUES (25,'Converse Substitutions',1,'lex_inverse',2,0);

INSERT INTO `relations` VALUES (3,'Syntax',1,'Syntax',0,0);
INSERT INTO `relations` VALUES (31,'Diathesis Alternations',1,'sny_diathesis',3,0);
INSERT INTO `relations` VALUES (32,'Negation Switching',1,'syn_negation',3,0);
INSERT INTO `relations` VALUES (33,'Ellipsis',1,'syn_ellipsis',3,0);
INSERT INTO `relations` VALUES (34,'Coordination Changes',1,'syn_coordination',3,0);
INSERT INTO `relations` VALUES (35,'Subordination-and-Nesting Changes',1,'syn_subord_nesting',3,0);

INSERT INTO `relations` VALUES (4,'Discourse',1,'Discourse',0,0);
INSERT INTO `relations` VALUES (41,'Punctuation Changes',1,'dis_punctuation',4,0);
INSERT INTO `relations` VALUES (42,'Direct/Indirect-style Aternations',1,'dis_direct_indirect',4,0);
INSERT INTO `relations` VALUES (43,'Sentence-Modality Changes',1,'dis_sent_modality',4,0);
INSERT INTO `relations` VALUES (44,'Syntax/Discourse-structure Changes',1,'syn_dis_structure',4,0);

INSERT INTO `relations` VALUES (5,'Semantics',1,'Semantics',0,0);
INSERT INTO `relations` VALUES (51,'Semantics-based Changes',1,'semantics',5,0);

INSERT INTO `relations` VALUES (6,'Miscellaneous',1,'Miscellaneous',0,0);
INSERT INTO `relations` VALUES (61,'Change of Format',1,'format',6,0);
INSERT INTO `relations` VALUES (62,'Change of Order',1,'order',6,0);
INSERT INTO `relations` VALUES (63,'Addition/Deletion',1,'addition_deletion',6,0);

INSERT INTO `relations` VALUES (7,'Extremes',1,'Extremes',0,0);
INSERT INTO `relations` VALUES (71,'Identical',1,'identical',7,0);
INSERT INTO `relations` VALUES (72,'Entailment',1,'entailment',7,0);
INSERT INTO `relations` VALUES (73,'Non-Paraphrase',1,'non_paraphrase',7,0);
/*!40000 ALTER TABLE `relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warp_module`
--

DROP TABLE IF EXISTS `warp_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warp_module` (
  `module_sid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `module_index` varchar(128) NOT NULL,
  PRIMARY KEY (`module_sid`),
  UNIQUE KEY `module_sid` (`module_sid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warp_module`
--

LOCK TABLES `warp_module` WRITE;
/*!40000 ALTER TABLE `warp_module` DISABLE KEYS */;
INSERT INTO `warp_module` VALUES (1,'Logout','logout/index.php'),(2,'Blank','template.php'),(10,'Details','anno/details.php'),(11,'Annotation Listing','anno/list.php'),(12,'Meta annotation','anno/meta.php'),(13,'Textual Relations','anno/layer_1.php'),(14,'Atomic Relations','anno/layer_2.php'),(17,'New atomic type','anno/add_type.php'),(18,'Delete atomic type','anno/del_type.php'),(19,'Update atomic type','anno/update_type.php'),(100,'Dataset Details','data_conf/details.php'),(101,'Assign Pairs','data_conf/assign.php'),(102,'Annotation Statistics','data_conf/anno_stats.php'),(103,'Import corpus','data_conf/import.php'),(104,'Export annotations','data_conf/export.php'),(110,'User List','user_conf/list.php'),(111,'User New','user_conf/new.php'),(112,'User Edit','user_conf/edit.php'),(113,'User Delete','user_conf/delete.php'),(120,'Layer list','layer_conf/list.php'),(121,'Layer New','layer_conf/new.php'),(122,'Layer Edit','layer_conf/edit.php'),(123,'Layer Swap','layer_conf/swap_layer.php'),(124,'Layer Relations','layer_conf/configure.php'),(125,'Layer Delete','layer_conf/delete.php'),(126,'Layer Delete Relation','layer_conf/del_rel.php');
/*!40000 ALTER TABLE `warp_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warp_module_alias`
--

DROP TABLE IF EXISTS `warp_module_alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warp_module_alias` (
  `alias_sid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_sid` int(11) NOT NULL,
  `alias` varchar(128) NOT NULL,
  PRIMARY KEY (`alias_sid`),
  UNIQUE KEY `alias_sid` (`alias_sid`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warp_module_alias`
--

LOCK TABLES `warp_module_alias` WRITE;
/*!40000 ALTER TABLE `warp_module_alias` DISABLE KEYS */;
INSERT INTO `warp_module_alias` VALUES (1,1,'logout'),(10,10,'anno/details'),(11,11,'anno/list'),(12,12,'anno/meta'),(13,13,'anno/layer_1'),(14,14,'anno/layer_2'),(17,17,'anno/add_type'),(18,18,'anno/del_type'),(19,19,'anno/update_type'),(100,100,'data_conf/details'),(101,101,'data_conf/assign'),(102,102,'data_conf/anno_stats'),(103,103,'data_conf/import'),(104,104,'data_conf/export'),(110,110,'user_conf/list'),(111,111,'user_conf/new'),(112,112,'user_conf/edit'),(113,113,'user_conf/delete'),(120,120,'layer_conf/list'),(121,121,'layer_conf/new'),(122,122,'layer_conf/edit'),(123,123,'layer_conf/swap_layer'),(124,124,'layer_conf/configure'),(125,125,'layer_conf/delete'),(126,126,'layer_conf/del_rel');
/*!40000 ALTER TABLE `warp_module_alias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warp_user`
--

DROP TABLE IF EXISTS `warp_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warp_user` (
  `user_sid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(64) NOT NULL,
  `colab_id` bigint(20) DEFAULT NULL,
  `refresh` int(11) NOT NULL DEFAULT '1',
  `pagesize` int(11) NOT NULL DEFAULT '15',
  `admin` tinyint(1) DEFAULT '0',
  `disabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`user_sid`),
  UNIQUE KEY `user_sid` (`user_sid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warp_user`
--

LOCK TABLES `warp_user` WRITE;
/*!40000 ALTER TABLE `warp_user` DISABLE KEYS */;
INSERT INTO `warp_user` VALUES (1,'warp_admin','bec0a423c69f2786e5fe584a623d5be58b1863cb70808caef4062633454d22b6',1,1,15,1,0);
/*!40000 ALTER TABLE `warp_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-08-17 12:28:01
