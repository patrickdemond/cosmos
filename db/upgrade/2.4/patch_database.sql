-- Patch to upgrade database to version 2.4

SET AUTOCOMMIT=0;

SOURCE application_type.sql
SOURCE application_type_has_role.sql
SOURCE application.sql
SOURCE application_has_site.sql

SOURCE access.sql
SOURCE service.sql
SOURCE role_has_service.sql
SOURCE setting.sql
SOURCE writelog.sql

SOURCE technician.sql
SOURCE platform.sql
SOURCE interview.sql
SOURCE stage_type.sql
SOURCE stage.sql
SOURCE comment.sql
SOURCE indicator.sql
SOURCE outlier.sql

SOURCE update_outlier.sql
SOURCE update_outliers.sql
SOURCE update_version_number.sql

COMMIT;

