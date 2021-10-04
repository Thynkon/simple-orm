-- -----------------------------------------------------
-- Table `looper`.`quiz_state`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `looper`.`quiz_state` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(45) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `label_UNIQUE` (`label` ASC) VISIBLE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `looper`.`quiz`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `looper`.`quiz` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(45) NOT NULL,
    `is_public` TINYINT NOT NULL DEFAULT 0,
    `quiz_state_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_quiz_quiz_state_idx` (`quiz_state_id` ASC) VISIBLE,
    UNIQUE INDEX `title_UNIQUE` (`title` ASC) VISIBLE,
    CONSTRAINT `fk_quiz_quiz_state`
        FOREIGN KEY (`quiz_state_id`)
        REFERENCES `looper`.`quiz_state` (`id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `looper`.`question_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `looper`.`question_type` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `label_UNIQUE` (`label` ASC) VISIBLE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `looper`.`question`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `looper`.`question` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(45) NOT NULL,
    `question_type_id` INT NOT NULL,
    `quiz_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_question_question_type_idx` (`question_type_id` ASC) VISIBLE,
    INDEX `fk_question_quiz_idx` (`quiz_id` ASC) VISIBLE,
    CONSTRAINT `fk_question_question_type`
        FOREIGN KEY (`question_type_id`)
        REFERENCES `looper`.`question_type` (`id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    CONSTRAINT `fk_question_quiz`
        FOREIGN KEY (`quiz_id`)
        REFERENCES `looper`.`quiz` (`id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `looper`.`answer`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `looper`.`answer` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `value` VARCHAR(45) NULL,
    `question_id` INT NOT NULL,
    `date` DATETIME NOT NULL DEFAULT NOW(),
    PRIMARY KEY (`id`),
    INDEX `fk_answer_question_idx` (`question_id` ASC) VISIBLE,
    CONSTRAINT `fk_answer_question`
        FOREIGN KEY (`question_id`)
        REFERENCES `looper`.`question` (`id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION)
ENGINE = InnoDB;