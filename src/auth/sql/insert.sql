SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE `auth.user_role_data`;
TRUNCATE TABLE `auth.role_action`;
TRUNCATE TABLE `auth.user`;
TRUNCATE TABLE `auth.action`;
TRUNCATE TABLE `auth.role`;
TRUNCATE TABLE `auth.data`;
SET FOREIGN_KEY_CHECKS=1;


INSERT INTO `auth.user` (`mail`, `password`) VALUES
       ("xavier@marcelet.com", MD5("iparks"))
       ;

INSERT INTO `auth.action` (`description`, `tag`) VALUES
       ("List users",              "auth/user/read"),
       ("Create and modify users", "auth/user/write"),
       ("Delete users",            "auth/user/delete"),
       ("List roles",              "auth/role/read"),
       ("Create and modify roles", "auth/role/write"),
       ("Delete roles",            "auth/role/delete")
       ;

INSERT INTO `auth.role` (`description`) VALUES
       ("Basic operator"),
       ("User Manager"),
       ("Role Manager"),
       ("Admin Manager");


INSERT INTO `auth.role_action` (`role_id`, `action_id`) VALUES
       (1, 1), (1, 4),
       (2, 1), (2, 4), (2, 2), (2, 3),
       (3, 1), (3, 4), (3, 5), (3, 6),
       (4, 1), (4, 2), (4, 3), (4, 4), (4, 5), (4, 6);

INSERT INTO `auth.data` (`name`) VALUES
       ("*"),
       ("Garage 1"),
       ("Garage 2");

INSERT INTO `auth.user_role_data` (`user_id`, `role_id`, `data_id`) VALUES
       (1, 1, 2),
       (1, 1, 3),
       (1, 2, 3),
       (1, 3, 3),
       (1, 4, 1);



