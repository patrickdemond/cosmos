DROP PROCEDURE IF EXISTS patch_writelog;
DELIMITER //
CREATE PROCEDURE patch_writelog()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = ( SELECT REPLACE( DATABASE(), "cosmos", "cenozo" ) );

    SELECT "Creating new writelog table" AS "";

    SET @sql = CONCAT(
      "CREATE TABLE IF NOT EXISTS writelog ( ",
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT, ",
        "user_id INT UNSIGNED NOT NULL, ",
        "site_id INT UNSIGNED NOT NULL, ",
        "role_id INT UNSIGNED NOT NULL, ",
        "method ENUM('DELETE', 'PATCH', 'POST', 'PUT') NOT NULL, ",
        "path VARCHAR(512) NOT NULL, ",
        "elapsed FLOAT NULL DEFAULT NULL, ",
        "status INT NULL DEFAULT NULL, ",
        "datetime DATETIME NOT NULL, ",
        "PRIMARY KEY (id), ",
        "INDEX fk_user_id (user_id ASC), ",
        "INDEX fk_site_id (site_id ASC), ",
        "INDEX fk_role_id (role_id ASC), ",
        "INDEX dk_datetime (datetime DESC), ",
        "CONSTRAINT fk_writelog_user_id ",
          "FOREIGN KEY (user_id) ",
          "REFERENCES ", @cenozo, ".user (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION, ",
        "CONSTRAINT fk_writelog_site_id ",
          "FOREIGN KEY (site_id) ",
          "REFERENCES ", @cenozo, ".site (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION, ",
        "CONSTRAINT fk_writelog_role_id ",
          "FOREIGN KEY (role_id) ",
          "REFERENCES ", @cenozo, ".role (id) ",
          "ON DELETE NO ACTION ",
          "ON UPDATE NO ACTION) ",
      "ENGINE = InnoDB" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_writelog();
DROP PROCEDURE IF EXISTS patch_writelog;