DROP TABLE IF EXISTS `civicrm_membership_periods`;

-- /*******************************************************
-- *
-- * civicrm_membership_periods
-- *
-- * Membership periods details
-- *
-- *******************************************************/
CREATE TABLE `civicrm_membership_periods` (
     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Periods ID',
     `start_date` date NOT NULL   COMMENT 'Start Date of Membership Period',
     `end_date` date NOT NULL   COMMENT 'End Date of Membership Period',
     `membership_id` int unsigned NOT NULL   COMMENT 'FK to Membership',
     `contribution_id` int unsigned    COMMENT 'FK to Contribution',
     `contact_id` int unsigned    COMMENT 'FK to Contact',
    PRIMARY KEY (`id`),
    CONSTRAINT FK_civicrm_membership_periods_membership_id FOREIGN KEY (`membership_id`) REFERENCES `civicrm_membership`(`id`) ON DELETE CASCADE,
    CONSTRAINT FK_civicrm_membership_periods_contribution_id FOREIGN KEY (`contribution_id`) REFERENCES `civicrm_contribution`(`id`) ON DELETE CASCADE,
    CONSTRAINT FK_civicrm_membership_periods_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;