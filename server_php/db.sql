-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-08-16 10:05:53
-- 服务器版本： 5.7.43-log
-- PHP 版本： 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `jqb`
--

-- --------------------------------------------------------

--
-- 表的结构 `clients`
--

CREATE TABLE `clients` (
  `groupID` varchar(50) NOT NULL,
  `clientID` varchar(50) NOT NULL,
  `createTime` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastActive` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户端信息表';


-- --------------------------------------------------------

--
-- 表的结构 `datas`
--

CREATE TABLE `datas` (
  `md5` varchar(32) NOT NULL,
  `groupID` varchar(50) NOT NULL,
  `clientID` varchar(50) NOT NULL,
  `content` mediumtext NOT NULL,
  `contentType` varchar(20) NOT NULL,
  `createAt` datetime NOT NULL,
  `lastUse` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='剪贴板数据表';

--
-- 转存表中的数据 `datas`
--

-- --------------------------------------------------------

--
-- 表的结构 `syncdatas`
--

CREATE TABLE `syncdatas` (
  `ID` int(11) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `groupID` varchar(50) NOT NULL,
  `clientID` varchar(50) NOT NULL,
  `syncTime` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据同步状态表';

--
-- 转存表中的数据 `syncdatas`
--

--
-- 转储表的索引
--

--
-- 表的索引 `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`groupID`,`clientID`);

--
-- 表的索引 `datas`
--
ALTER TABLE `datas`
  ADD PRIMARY KEY (`md5`),
  ADD KEY `idx_group_last_use` (`groupID`,`lastUse`),
  ADD KEY `groupID` (`groupID`,`clientID`);

--
-- 表的索引 `syncdatas`
--
ALTER TABLE `syncdatas`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `buchong` (`md5`,`groupID`,`clientID`) USING BTREE,
  ADD KEY `groupID` (`groupID`,`clientID`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `syncdatas`
--
ALTER TABLE `syncdatas`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19061;

--
-- 限制导出的表
--

--
-- 限制表 `datas`
--
ALTER TABLE `datas`
  ADD CONSTRAINT `datas_ibfk_1` FOREIGN KEY (`groupID`,`clientID`) REFERENCES `clients` (`groupID`, `clientID`) ON DELETE CASCADE;

--
-- 限制表 `syncdatas`
--
ALTER TABLE `syncdatas`
  ADD CONSTRAINT `syncdatas_ibfk_1` FOREIGN KEY (`md5`) REFERENCES `datas` (`md5`) ON DELETE CASCADE,
  ADD CONSTRAINT `syncdatas_ibfk_2` FOREIGN KEY (`groupID`,`clientID`) REFERENCES `clients` (`groupID`, `clientID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
