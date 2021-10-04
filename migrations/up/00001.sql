-- quiz state
INSERT INTO `looper`.`quiz_state` (`label`) VALUES ('Building');
INSERT INTO `looper`.`quiz_state` (`label`) VALUES ('Answering');
INSERT INTO `looper`.`quiz_state` (`label`) VALUES ('Closed');

-- quiz
INSERT INTO `looper`.`quiz` (`title`, `is_public`, `quiz_state_id`) VALUES ('Building form 123', '0', '1');
INSERT INTO `looper`.`quiz` (`title`, `is_public`, `quiz_state_id`) VALUES ('Answering form 123', '1', '2');
INSERT INTO `looper`.`quiz` (`title`, `is_public`, `quiz_state_id`) VALUES ('Closed form 123', '1', '3');

-- Question type
INSERT INTO `looper`.`question_type` (`label`) VALUES ('Single line text');
INSERT INTO `looper`.`question_type` (`label`) VALUES ('List of single lines');
INSERT INTO `looper`.`question_type` (`label`) VALUES ('Multi-line text');

-- Question
INSERT INTO `looper`.`question` (`label`, `question_type_id`, `quiz_id`) VALUES ('Question1', '1', '1');
INSERT INTO `looper`.`question` (`label`, `question_type_id`, `quiz_id`) VALUES ('Question2', '2', '2');
INSERT INTO `looper`.`question` (`label`, `question_type_id`, `quiz_id`) VALUES ('Question3', '3', '3');
INSERT INTO `looper`.`question` (`label`, `question_type_id`, `quiz_id`) VALUES ('Question4->belongs->1', '1', '1');
INSERT INTO `looper`.`question` (`label`, `question_type_id`, `quiz_id`) VALUES ('Question5->belongs to 2', '1', '2');

-- Answer
INSERT INTO `looper`.`answer` (`value`, `question_id`, `date`) VALUES ('Answer to question1', '1', NOW());
INSERT INTO `looper`.`answer` (`value`, `question_id`, `date`) VALUES ('Answer to question2', '2', NOW());
INSERT INTO `looper`.`answer` (`value`, `question_id`, `date`) VALUES ('Answer to question3', '3', NOW());
INSERT INTO `looper`.`answer` (`value`, `question_id`, `date`) VALUES ('Answer to question2', '2', NOW());
