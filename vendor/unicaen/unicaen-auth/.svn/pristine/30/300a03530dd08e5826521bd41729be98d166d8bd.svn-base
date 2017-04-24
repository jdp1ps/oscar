CREATE TABLE user (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    display_name VARCHAR(64) DEFAULT NULL,
    password VARCHAR(128) NOT NULL,
    state SMALLINT default 1,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique_username` (`username` ASC)
) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_role` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_id` VARCHAR(64) NOT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `parent_id` INT(11) NULL DEFAULT NULL,
  `ldap_filter` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique_role` (`role_id` ASC),  
  INDEX `idx_parent_id` (`parent_id` ASC),
  CONSTRAINT `fk_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `user_role` (`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_role_linker` (
  `user_id` INT(11) NOT NULL,
  `role_id` INT(11) NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  INDEX `idx_role_id` (`role_id` ASC),
  INDEX `idx_user_id` (`user_id` ASC),
  CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `user_role` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;


CREATE TABLE  IF NOT EXISTS categorie_privilege (
    id            INT(11) NOT NULL AUTO_INCREMENT,
    code          VARCHAR(150) NOT NULL,
    libelle       VARCHAR(200) NOT NULL,
    ordre         INt(11),
  PRIMARY KEY (id),
  UNIQUE INDEX unique_code (code ASC)
) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS privilege (
    id            INT(11) NOT NULL AUTO_INCREMENT,
    categorie_id  INT(11) NOT NULL,
    code          VARCHAR(150) NOT NULL,
    libelle       VARCHAR(200) NOT NULL,
    ordre         INT(11),
  PRIMARY KEY (id),
  UNIQUE INDEX unique_code (code ASC),
  CONSTRAINT fk_categorie_id FOREIGN KEY (categorie_id) REFERENCES categorie_privilege (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS role_privilege (
  role_id       INT(11) NOT NULL,
  privilege_id  INT(11) NOT NULL,
  PRIMARY KEY (role_id,privilege_id),
  INDEX idx_role_id (role_id ASC),
  INDEX idx_privilege_id (privilege_id ASC),
  CONSTRAINT fk_rp_role_id FOREIGN KEY (role_id) REFERENCES user_role (id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_privilege_id FOREIGN KEY (privilege_id) REFERENCES privilege (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;


-- Données
INSERT INTO `user_role` (`id`, `role_id`, `is_default`, `parent_id`) VALUES
(1, 'Standard', 1, NULL),
(2, 'Gestionnaire', 0, 1),
(3, 'Super-gestionnaire', 0, 2),
(4, 'Administrateur', 0, 3);

INSERT INTO `categorie_privilege` (`id`, `code`, `libelle`, `ordre`) VALUES
(1, 'droit', 'Gestion des droits', 1);

INSERT INTO `privilege` (`id`, `categorie_id`, `code`, `libelle`, `ordre`) VALUES
(1, 1, 'role-visualisation', 'Rôles - Visualisation', 1),
(2, 1, 'role-edition', 'Rôles - Édition', 2),
(3, 1, 'privilege-visualisation', 'Privilèges - Visualisation', 3),
(4, 1, 'privilege-edition', 'Privilèges - Édition', 4);

INSERT INTO `role_privilege` (`role_id`, `privilege_id`) VALUES
(4, 1),
(4, 2),
(4, 3),
(4, 4);