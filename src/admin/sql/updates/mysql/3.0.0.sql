-- MySQL Workbench Synchronization
-- Generated: 2018-03-23 12:41
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

ALTER TABLE `#__jinbound_campaigns`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `label` `label` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'label type' ,
  CHANGE COLUMN `greedy` `greedy` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'this campaign wants the contacts to itself' ,
  CHANGE COLUMN `published` `published` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'publication status of record - 0 is Unpublished, 1 is Published, -2 is Trashed' ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'Date and Time record was checked out' ,
  ADD INDEX `fk_assets_idx` (`asset_id` ASC);

ALTER TABLE `#__jinbound_contacts`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `published` `published` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'publication status of record - 0 is Unpublished, 1 is Published, -2 is Trashed' ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'Date and Time record was checked out' ,
  ADD INDEX `fk_users_idx` (`user_id` ASC),
  ADD INDEX `fk_assets_idx` (`asset_id` ASC),
  ADD INDEX `fk_contacts` (`core_contact_id` ASC);

ALTER TABLE `#__jinbound_contacts_campaigns`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `added` `added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when contact was added to campaign, in UTC' ,
  ADD INDEX `fk_contacts_idx` (`contact_id` ASC),
  ADD INDEX `fk_campaigns_idx` (`campaign_id` ASC);

ALTER TABLE `#__jinbound_contacts_priorities`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  ADD INDEX `fk_priorities_idx` (`priority_id` ASC),
  ADD INDEX `fk_campaigns_idx` (`campaign_id` ASC),
  ADD INDEX `fk_contacts_idx` (`contact_id` ASC);

ALTER TABLE `#__jinbound_contacts_statuses`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  ADD INDEX `fk_statuses_idx` (`status_id` ASC),
  ADD INDEX `fk_campaigns_idx` (`campaign_id` ASC),
  ADD INDEX `fk_contacts_idx` (`contact_id` ASC);

ALTER TABLE `#__jinbound_conversions`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `published` `published` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'publication status of record - 0 is Unpublished, 1 is Published, -2 is Trashed' ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'Date and Time record was checked out' ,
  ADD INDEX `fk_pages_idx` (`page_id` ASC),
  ADD INDEX `fk_contacts_idx` (`contact_id` ASC);

ALTER TABLE `#__jinbound_emails`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `published` `published` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'publication status of record - 0 is Unpublished, 1 is Published, -2 is Trashed' ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'Date and Time record was checked out' ,
  ADD INDEX `fk_assets` (`asset_id` ASC),
  ADD INDEX `fk_campaigns_idx` (`campaign_id` ASC);

ALTER TABLE `#__jinbound_emails_records`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `sent` `sent` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  ADD INDEX `fk_assets` (`asset_id` ASC),
  ADD INDEX `fk_emails_idx` (`email_id` ASC),
  ADD INDEX `fk_versions_idx` (`version_id` ASC),
  ADD INDEX `fk_contacts_idx` (`lead_id` ASC);

ALTER TABLE `#__jinbound_emails_versions`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  ADD INDEX `fk_emails_idx` (`email_id` ASC);

ALTER TABLE `#__jinbound_fields`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci , ENGINE = InnoDB ,
  CHANGE COLUMN `params` `params` TEXT NULL DEFAULT NULL COMMENT 'Various parameters for field - options, html attributes, etc' ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when field was created' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of field creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when field was last modified' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `published` `published` TINYINT(1) NULL DEFAULT NULL COMMENT 'Publication status' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'when field was checked out' ,
  ADD INDEX `fk_assets_idx` (`asset_id` ASC);

ALTER TABLE `#__jinbound_form_fields`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci , ENGINE = InnoDB ,
  ADD INDEX `fk_forms_idx` (`form_id` ASC),
  ADD INDEX `fk_fields_idx` (`field_id` ASC);

ALTER TABLE `#__jinbound_forms`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci , ENGINE = InnoDB ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when form was created' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of form creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when form was last modified' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `published` `published` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Publication status' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'Date and Time form was checked out' ,
  ADD INDEX `fk_assets_idx` (`asset_id` ASC);

ALTER TABLE `#__jinbound_landing_pages_hits`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `page_id` `page_id` INT(11) NOT NULL ,
  ADD INDEX `fk_pages_idx` (`page_id` ASC);

ALTER TABLE `#__jinbound_lead_statuses`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `default` `default` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Default status' ,
  CHANGE COLUMN `active` `active` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Active statuses count towards leads, inactive do not' ,
  CHANGE COLUMN `final` `final` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Final status' ,
  CHANGE COLUMN `published` `published` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'publication status of record - 0 is Unpublished, 1 is Published, -2 is Trashed' ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'Date and Time record was checked out' ,
  ADD INDEX `fk_assets_idx` (`asset_id` ASC);

ALTER TABLE `#__jinbound_notes`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `published` `published` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'publication status of record - 0 is Unpublished, 1 is Published, -2 is Trashed' ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'Date and Time record was checked out' ,
  ADD INDEX `fk_assets_idx` (`asset_id` ASC),
  ADD INDEX `fk_contacts_idx` (`lead_id` ASC);

ALTER TABLE `#__jinbound_pages`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `campaign` `campaign` INT(11) NULL DEFAULT NULL ,
  CHANGE COLUMN `hits` `hits` INT(11) NOT NULL DEFAULT '0' COMMENT 'number of views for this record' ,
  CHANGE COLUMN `published` `published` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'publication status of record - 0 is Unpublished, 1 is Published, -2 is Trashed' ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'Date and Time record was checked out' ,
  ADD INDEX `fk_assets_idx` (`asset_id` ASC),
  ADD INDEX `fk_forms_idx` (`formid` ASC),
  ADD INDEX `fk_campaigns_idx` (`campaign` ASC);

ALTER TABLE `#__jinbound_priorities`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `published` `published` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'publication status of record - 0 is Unpublished, 1 is Published, -2 is Trashed' ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of record creator' ,
  CHANGE COLUMN `modified` `modified` DATETIME NULL DEFAULT NULL COMMENT 'when record was last modified in UTC' ,
  CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'User id of last modifier' ,
  CHANGE COLUMN `checked_out` `checked_out` INT(11) NULL DEFAULT NULL COMMENT 'Locking column to prevent simultaneous updates' ,
  CHANGE COLUMN `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL COMMENT 'Date and Time record was checked out' ,
  ADD INDEX `fk_assets` (`asset_id` ASC);

ALTER TABLE `#__jinbound_reports_emails`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'when record was created, in UTC' ,
  ADD INDEX `fk_emails_idx` (`email_id` ASC);

ALTER TABLE `#__jinbound_subscriptions`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci ,
  ADD INDEX `fk_contacts_idx` (`contact_id` ASC);

ALTER TABLE `#__jinbound_tracks`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci , ENGINE = InnoDB ,
  CHANGE COLUMN `created` `created` DATETIME NULL DEFAULT NULL COMMENT 'Date this request was made' ;

ALTER TABLE `#__jinbound_users_tracks`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_unicode_ci , ENGINE = InnoDB ,
  ADD INDEX `fk_users` (`user_id` ASC);

DROP TABLE IF EXISTS `#__jinbound_stages` ;

DROP TABLE IF EXISTS `#__jinbound_leads` ;

DROP TABLE IF EXISTS `#__jinbound_contacts_followers` ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
