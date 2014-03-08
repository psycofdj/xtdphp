CREATE TABLE IF NOT EXISTS `auth.user`
(
 `id`       int(11)                                                 NOT NULL AUTO_INCREMENT,
 `mail`     varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
 `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE(`mail`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `auth.action`
(
 `id`          int(11)                                                 NOT NULL AUTO_INCREMENT,
 `description` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
 `tag`         varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE(`tag`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `auth.role`
(
 `id`                 int(11)                                                 NOT NULL AUTO_INCREMENT,
 `description`        varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,

  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `auth.role_action`
(
 `role_id`        int(11) NOT NULL,
 `action_id`      int(11) NOT NULL,

  PRIMARY KEY (`role_id`, `action_id`),
  FOREIGN KEY (`role_id`)   REFERENCES `auth.role`   (`id`),
  FOREIGN KEY (`action_id`) REFERENCES `auth.action` (`id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `auth.data`
(
 `id`   int(11)                                                 NOT NULL AUTO_INCREMENT,
 `name` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,

  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `auth.user_role_data`
(
 `user_id` int(11)  NOT NULL,
 `role_id` int(11)  NOT NULL,
 `data_id` int(11)  NOT NULL,

  PRIMARY KEY (`user_id`, `role_id`, `data_id`),
  FOREIGN KEY (`user_id`)  REFERENCES `auth.user` (`id`),
  FOREIGN KEY (`role_id`)  REFERENCES `auth.role` (`id`),
  FOREIGN KEY (`data_id`)  REFERENCES `auth.data` (`id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;

CREATE OR REPLACE VIEW `auth.view_user_action` AS
   SELECT
      user.id     AS `user_id`,
      action.tag  AS `action_tag`,
      data.name   AS `data_name`
   FROM `auth.user`                AS user
   LEFT JOIN `auth.user_role_data`           ON user.id   = `auth.user_role_data`.user_id
   LEFT JOIN `auth.role`           AS role   ON role.id   = `auth.user_role_data`.role_id
   LEFT JOIN `auth.role_action`              ON role.id   = `auth.role_action`.role_id
   LEFT JOIN `auth.action`         AS action ON action.id = `auth.role_action`.action_id
   LEFT JOIN `auth.data`           AS data   ON data.id   = `auth.user_role_data`.data_id
   GROUP BY action.tag, data.name;
