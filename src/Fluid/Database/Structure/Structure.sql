CREATE TABLE `fluid_api_consumers` (
  `name` char(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `key` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `secret` char(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`key`),
  UNIQUE KEY `secret` (`secret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `fluid_api_nonce` (
  `nonce` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`nonce`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `fluid_api_tokens` (
  `consumer_key` char(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `token` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `token_secret` char(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `type` varchar(7) NOT NULL DEFAULT '',
  `expiration` datetime DEFAULT NULL,
  PRIMARY KEY (`token`),
  UNIQUE KEY `access_secret` (`token_secret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `fluid_page_tokens` (
  `token` binary(64) NOT NULL DEFAULT '\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `expiration` datetime DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;