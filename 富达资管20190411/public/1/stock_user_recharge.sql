/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50554
Source Host           : localhost:3306
Source Database       : phpwamp

Target Server Type    : MYSQL
Target Server Version : 50554
File Encoding         : 65001

Date: 2019-03-01 11:55:55
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `stock_user_recharge`
-- ----------------------------
DROP TABLE IF EXISTS `stock_user_recharge`;
CREATE TABLE `stock_user_recharge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID；表stock_user字段user_id字段',
  `trade_no` varchar(64) NOT NULL COMMENT '订单编号',
  `out_trade_no` varchar(128) DEFAULT NULL COMMENT '外部订单号',
  `amount` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `actual` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际到账',
  `poundage` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '充值手续费',
  `type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '充值方式：0支付宝，1微信，2-连连支付',
  `state` tinyint(4) NOT NULL DEFAULT '0' COMMENT '充值状态：0待付款，1成功，-1失败',
  `create_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '充值时间',
  `update_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `trade_no` (`trade_no`),
  KEY `user_id` (`user_id`),
  KEY `state` (`state`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='充值记录表';

-- ----------------------------
-- Records of stock_user_recharge
-- ----------------------------
