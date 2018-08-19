#MySQL User Account Setup

GRANT ALL PRIVILEGES ON `online_exam`.* TO `exammanager`@`%`;
DROP USER `exammanager`@`%`;
CREATE USER `exammanager`@`%` IDENTIFIED BY 'examinstructor09!@#';
GRANT ALL PRIVILEGES ON `online_exam`.* TO `exammanager`@`%`;

#Schema Setup
DROP SCHEMA IF EXISTS `online_exam`;

CREATE SCHEMA `online_exam` DEFAULT CHARACTER SET utf8 ;

USE `online_exam`;

#Table Setup
DROP TABLE IF EXISTS `ausers`;
DROP TABLE IF EXISTS `exam_code`;
DROP TABLE IF EXISTS `exam_head`;
DROP TABLE IF EXISTS `exam_option`;
DROP TABLE IF EXISTS `exam_question`;
DROP TABLE IF EXISTS `student_answer`;
DROP TABLE IF EXISTS `student_info`;
DROP TABLE IF EXISTS `user_logs`;

CREATE TABLE `ausers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `addr1` varchar(100) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `pwd` varchar(100) DEFAULT NULL,
  `isactive` bit(1) DEFAULT b'1',
  `usertype` int(1) DEFAULT NULL,
  `dtecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `online_exam`.`ausers` (`fname`, `lname`, `email`, `phone`, `addr1`, `city`, `username`, `pwd`, `isactive`, `usertype`)
VALUE ('System', 'Administrator', '', '', '', '', 'admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', '1', '1');

CREATE TABLE `exam_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) DEFAULT NULL,
  `exam_code` varchar(100) DEFAULT NULL,
  `dtestart` datetime DEFAULT NULL,
  `dteend` datetime DEFAULT NULL,
  `dtecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `exam_head` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `descrip` text,
  `instruction` text,
  `tech_id` int(11) DEFAULT NULL,
  `dtecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `subj` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `exam_option` (
  `examid` int(11) DEFAULT NULL,
  `questionid` int(11) DEFAULT NULL,
  `option_letter` varchar(2) DEFAULT NULL,
  `descrip` text,
  `tech_id` int(11) DEFAULT NULL,
  `dtecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`examid`,`questionid`,`option_letter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `exam_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `examid` int(11) DEFAULT NULL,
  `question_type` varchar(50) DEFAULT NULL,
  `question` text,
  `answer` text,
  `tech_id` int(11) DEFAULT NULL,
  `dtecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `student_answer` (
  `studentid` int(11) DEFAULT NULL,
  `examid` int(11) DEFAULT NULL,
  `examcodeid` int(11) DEFAULT NULL,
  `questionid` int(11) DEFAULT NULL,
  `answer` varchar(10) DEFAULT NULL,
  `dtecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `exam` (`examid`,`examcodeid`),
  KEY `student` (`studentid`),
  KEY `question` (`examid`,`examcodeid`,`studentid`,`questionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `student_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `examid` int(11) DEFAULT NULL,
  `examcodeid` int(11) DEFAULT NULL,
  `studentno` varchar(100) DEFAULT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `sect` varchar(10) DEFAULT NULL,
  `dtefinish` datetime DEFAULT NULL,
  `dtestart` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `course` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `examid` int(11) DEFAULT NULL,
  `act` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `dtecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#Functions Setup
DROP FUNCTION IF EXISTS `fnGenerateExamCode`;
DROP FUNCTION IF EXISTS `fnGetExamCodeExaminee`;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` FUNCTION `fnGenerateExamCode`() RETURNS text CHARSET utf8
BEGIN

DECLARE valid_char VARCHAR(50);
DECLARE ecode, newcode VARCHAR(50);
DECLARE max_char INT;
DECLARE good_code BIT;
DECLARE i INT;

SET valid_char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
SET ecode = ''; SET newcode = '';
SET max_char = 25;
SET good_code = FALSE;
SET i = 1;

WHILE (NOT good_code) DO
	WHILE (i <= max_char) DO
		SET ecode = CONCAT(ecode, MID(valid_char, FLOOR(1 + RAND() * LENGTH(valid_char)), 1));
		SET i = i + 1;
	END WHILE;
    
	SET i = 1;
    WHILE (i <= max_char) DO
		IF i = 6 OR i = 11 OR i = 16 OR i = 21 OR i = 26 THEN 
			SET newcode = CONCAT(newcode, '-'); 
		END IF;
		SET newcode = CONCAT(newcode, MID(ecode, i, 1));
		SET i = i + 1;
	END WHILE;
    
    SET good_code = (SELECT COUNT(1) FROM exam_code WHERE exam_code = newcode) = 0;
END WHILE;
 
RETURN newcode;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` FUNCTION `fnGetExamCodeExaminee`($exam int, $examcodeid int) RETURNS int(11)
BEGIN

RETURN (SELECT COUNT(1) FROM (SELECT DISTINCT si.id FROM student_info si
JOIN student_answer sa ON si.id = sa.studentid AND si.examid = si.examid AND si.examcodeid = sa.examcodeid
    WHERE si.examid = $exam AND si.examcodeid = $examcodeid
GROUP BY si.id) tbl);

END$$
DELIMITER ;


#Views Setup
CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`exammanager`@`%` SQL SECURITY DEFINER VIEW `vw_exam_examinees` AS select `ec`.`exam_id` AS `exam_id`,`ec`.`exam_code` AS `exam_code`,`si`.`course` AS `course`,`si`.`sect` AS `sect`,`si`.`studentno` AS `studentno`,concat(`si`.`lname`,', ',`si`.`fname`) AS `fullname`,`ec`.`dtestart` AS `examstart`,`ec`.`dteend` AS `examend`,sum(if((`eq`.`answer` = `sa`.`answer`),1,0)) AS `score`,sum(1) AS `totalscore`,cast(((sum(if((`eq`.`answer` = `sa`.`answer`),1,0)) / sum(1)) * 100) as decimal(10,0)) AS `percent` from ((((`exam_code` `ec` left join `exam_head` `eh` on((`ec`.`exam_id` = `eh`.`id`))) left join `exam_question` `eq` on((`ec`.`exam_id` = `eq`.`examid`))) left join `student_answer` `sa` on(((`eq`.`examid` = `sa`.`examid`) and (`eq`.`id` = `sa`.`questionid`) and (`ec`.`id` = `sa`.`examcodeid`)))) join `student_info` `si` on(((`sa`.`studentid` = `si`.`id`) and (`ec`.`id` = `si`.`examcodeid`)))) group by `ec`.`exam_code`,`si`.`course`,`si`.`sect`,concat(`si`.`lname`,', ',`si`.`fname`),`si`.`studentno`;

CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`exammanager`@`%` SQL SECURITY DEFINER VIEW `vw_exams` AS select `e`.`id` AS `id`,`c`.`exam_code` AS `exam_code`,`e`.`subj` AS `subj`,`e`.`title` AS `title`,`e`.`descrip` AS `descrip`,`e`.`instruction` AS `instruction`,`e`.`tech_id` AS `tech_id`,date_format(`c`.`dtestart`,'%m/%d/%Y %h:%i %p') AS `dtestart`,date_format(`c`.`dteend`,'%m/%d/%Y %h:%i %p') AS `dteend`,date_format(`e`.`dtecreated`,'%m/%d/%Y %h:%i %p') AS `dtecreated`,(select count(1) from `exam_question` where (`exam_question`.`examid` = `e`.`id`)) AS `cnt`,`fnGetExamCodeExaminee`(`e`.`id`,`c`.`id`) AS `examinees` from (`exam_head` `e` left join `exam_code` `c` on((`c`.`id` = (select `exam_code`.`id` from `exam_code` where (`exam_code`.`exam_id` = `e`.`id`) order by `exam_code`.`dtecreated` desc limit 1))));

CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`exammanager`@`%` SQL SECURITY DEFINER VIEW `vw_exams_with_instructor` AS select `eh`.`id` AS `id`,`eh`.`title` AS `title`,`eh`.`descrip` AS `descrip`,`eh`.`instruction` AS `instruction`,`eh`.`tech_id` AS `tech_id`,`eh`.`dtecreated` AS `dtecreated`,`eh`.`subj` AS `subj`,concat(`u`.`fname`,' ',`u`.`lname`) AS `instructor` from (`exam_head` `eh` left join `ausers` `u` on((`eh`.`tech_id` = `u`.`id`)));

CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`exammanager`@`%` SQL SECURITY DEFINER VIEW `vw_question` AS select `q`.`id` AS `questionid`,`q`.`examid` AS `examid`,`q`.`question_type` AS `question_type`,`q`.`question` AS `question`,`q`.`answer` AS `answer`,`q`.`tech_id` AS `tech_id`,`o`.`option_letter` AS `option_letter`,`o`.`descrip` AS `descrip` from (`exam_question` `q` left join `exam_option` `o` on(((`q`.`examid` = `o`.`examid`) and (`q`.`id` = `o`.`questionid`) and (`q`.`tech_id` = `o`.`tech_id`)))) order by `q`.`id`,`o`.`option_letter`;

CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`exammanager`@`%` SQL SECURITY DEFINER VIEW `vw_rand_question` AS select `exam_question`.`id` AS `id`,`exam_question`.`examid` AS `examid`,`exam_question`.`question_type` AS `question_type`,`exam_question`.`question` AS `question`,`exam_question`.`answer` AS `answer`,`exam_question`.`tech_id` AS `tech_id`,`exam_question`.`dtecreated` AS `dtecreated`,rand() AS `num` from `exam_question`;

CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`exammanager`@`%` SQL SECURITY DEFINER VIEW `vw_users` AS select `ausers`.`id` AS `id`,concat(`ausers`.`fname`,' ',`ausers`.`lname`) AS `fullname`,concat(`ausers`.`addr1`,', ',`ausers`.`city`) AS `address`,`ausers`.`email` AS `email`,`ausers`.`phone` AS `phone`,`ausers`.`usertype` AS `usertype`,`ausers`.`isactive` AS `isactive` from `ausers`;

#Stored Procedure
DROP PROCEDURE IF EXISTS `sp_add_edit_delete_exam`;
DROP PROCEDURE IF EXISTS `sp_add_edit_delete_question`;
DROP PROCEDURE IF EXISTS `sp_add_edit_user`;
DROP PROCEDURE IF EXISTS `sp_create_student_exam_profile`;
DROP PROCEDURE IF EXISTS `sp_generate_exam_code`;
DROP PROCEDURE IF EXISTS `sp_select_exam_question`;
DROP PROCEDURE IF EXISTS `sp_select_student_exam_sheet`;
DROP PROCEDURE IF EXISTS `sp_select_user_account`;
DROP PROCEDURE IF EXISTS `sp_set_student_exam_finish_date`;
DROP PROCEDURE IF EXISTS `sp_update_question_option`;
DROP PROCEDURE IF EXISTS `sp_update_student_exam_answer`;
DROP PROCEDURE IF EXISTS `sp_update_user_password`;
DROP PROCEDURE IF EXISTS `sp_update_user_profile`;
DROP PROCEDURE IF EXISTS `sp_validate_login`;
DROP PROCEDURE IF EXISTS `sp_validate_username`;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_add_edit_delete_exam`(
in $etype int, 
in $id int,
in $title text,
in $descrip text,
in $instruct text,
in $userid int,
in $subj text
)
BEGIN

IF $etype = 0 THEN
	INSERT INTO user_logs (id, module, act)
	VALUE ($userid, 'Exam', 
			CONCAT('{',
				'"data_type" : "Insert",',
				'"title" : "', $title, '",',
				'"description" : "', REPLACE($descrip, '\n', '\\n'), '",',
				'"instruction" : "', REPLACE($instruct, '\n', '\\n'), '",',
				'"subj" : "', $subj, '"',
                '}'
			)
		);
	
    INSERT INTO exam_head (title, descrip, instruction, subj, tech_id)
    VALUES ($title, $descrip, $instruct, $subj, $userid);
ELSEIF $etype = 1 THEN
	INSERT INTO user_logs (id, examid, module, act)
	VALUE ($userid, $id, 'Exam', 
			(SELECT
				CONCAT('{',
					'"data_type" : "Update",',
                    '"old_value" : {',
					'"title" : "', title, '",',
					'"description" : "', REPLACE(descrip, '\n', '\\n'), '",',
					'"instruction" : "', REPLACE(instruction, '\n', '\\n'), '",',
					'"subj" : "', REPLACE(subj, '\n', '\\n'), '"',
                    '},',
                    '"new_value" : {',
					'"title" : "', $title, '",',
					'"description" : "', REPLACE($descrip, '\n', '\\n'), '",',
					'"instruction" : "', REPLACE($instruct, '\n', '\\n'), '",'
					'"subj" : "', REPLACE($subj, '\n', '\\n'), '"',
                    '}}'
				)
            FROM exam_head
            WHERE id = $id)
		);

	UPDATE exam_head
    SET
        title = $title,
        descrip = $descrip,
        instruction = $instruct,
        subj = $subj
	WHERE id = $id;
ELSEIF $etype = 2 THEN
	INSERT INTO user_logs (id, examid, module, act)
	VALUE ($userid, $id, 'Exam', 
			(SELECT
				CONCAT('{',
					'"data_type" : "Delete",',
					'"id" : "', id, '",',
					'"title" : "', title, '",',
					'"description" : "', REPLACE(descrip, '\n', '\\n'), '",',
					'"instructor" : "', tech_id, '",',
					'"instruction" : "', REPLACE(instruction, '\n', '\\n'), '",',
					'"subj" : "', REPLACE($subj, '\n', '\\n'), '"',
                    '}'
				)
            FROM exam_head
            WHERE id = $id)
		);
	
    DELETE FROM exam_head WHERE id = $id;
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_add_edit_delete_question`(
in $etype int,
in $examid int,
in $qid int,
in $qtype text,
in $question text,
in $answer text,
in $tech_id int
)
BEGIN

IF $etype = 2 THEN
	INSERT INTO user_logs (id, examid, module, act)
	VALUE ($tech_id, $examid, 'Exam Questions', 
			(SELECT CONCAT('{',
				'"data_type" : "Delete",', 
				'"id" : "', id,'",', 
				'"examid" : "', examid,'",', 
				'"question_type" : "', question_type,'",', 
				'"question" : "', REPLACE(question, '\n', '\\n'),'",', 
				'"answer" : "', answer,'",', 
				'"tech_id" : "', tech_id,'",', 
				'"dtecreated" : "', dtecreated,'"', 
				'}'
			) FROM exam_question
            WHERE examid = $examid AND id = $qid AND tech_id = $tech_id)
		);

	DELETE FROM exam_question
    WHERE examid = $examid AND id = $qid AND tech_id = $tech_id;
    
    DELETE FROM exam_option
    WHERE examid = $examid AND questionid = $qid;
ELSEIF $qid = 0 THEN
	INSERT INTO user_logs (id, examid, module, act)
	VALUE ($tech_id, $examid, 'Exam Questions', 
			(SELECT CONCAT('{',
				'"data_type" : "Insert",', 
				'"examid" : "', $examid,'",', 
				'"question_type" : "', $qtype,'",', 
				'"question" : "', REPLACE($question, '\n', '\\n'),'",', 
				'"answer" : "', $answer,'",', 
				'"tech_id" : "', $tech_id,'",', 
				'"dtecreated" : "', now(),'"', 
				'}'
			) FROM exam_question
            WHERE examid = $examid AND id = $qid AND tech_id = $tech_id)
		);

	INSERT INTO exam_question (examid, question_type, question, answer, tech_id)
    VALUES ($examid, $qtype, $question, $answer, $tech_id);
ELSE
	INSERT INTO user_logs (id, examid, module, act)
	VALUE ($tech_id, $examid, 'Exam Questions', 
			(SELECT CONCAT('{',
				'"data_type" : "Update",', 
                '"old_value" : {',
				'"question" : "', REPLACE(question, '\n', '\\n'),'",', 
				'"answer" : "', answer,'"', 
				'},',                
                '"new_value" : {',
				'"question" : "', REPLACE($question, '\n', '\\n'),'",', 
				'"answer" : "', $answer,'"', 
				'}}'
			) FROM exam_question
            WHERE examid = $examid AND id = $qid AND tech_id = $tech_id)
		);
        
	UPDATE exam_question
    SET question = $question, answer = $answer
    WHERE examid = $examid AND id = $qid AND tech_id = $tech_id;
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_add_edit_user`(
in $id int,
in $fname text,
in $lname text,
in $uname text,
in $pwd text,
in $active bit,
in $usertype int
)
BEGIN

IF $id = 0 THEN
	INSERT INTO ausers (fname, lname, username, pwd, isactive, usertype)
	VALUES ($fname, $lname, $uname, $pwd, $active, $usertype);
    
    INSERT INTO user_logs (id, module, act)
	VALUE ($userid, 'Users', 
			CONCAT('{',
				'"data_type" : "Insert",',
				'"username" : "', $uname, '",',
				'"fname" : "', $fname, '",',
				'"lname" : "', $lname, '",',
				'"usertype" : "', $usertype, '",',
				'"isactive" : "', $active, '"',
                '}'
			)
		);
ELSE
   INSERT INTO user_logs (id, module, act)
	VALUE ($userid, 'Users', 
			CONCAT('{',
				'"data_type" : "Update",',
				'"usertype" : "', $usertype, '",',
				'"isactive" : "', $active, '"',
                '}'
			)
		);

	UPDATE ausers
    SET isactive = $active,
        usertype = $usertype
	WHERE id = $id;
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_create_student_exam_profile`(
in $examid int,
in $examcode text,
in $stnumber text,
in $stfname text,
in $stlname text,
in $stsection text,
in $course text
)
BEGIN

DECLARE exist_id, exist_cnt, ecodeid INT;

SELECT id INTO ecodeid
FROM exam_code
WHERE exam_code = $examcode;

SELECT IFNULL(id, 0), count(1)
INTO exist_id, exist_cnt
FROM student_info
WHERE examid = $examid 
	AND examcodeid = ecodeid 
    AND studentno = $stnumber;

IF exist_cnt = 0 THEN
	INSERT INTO student_info (examid, examcodeid, studentno, fname, lname, sect, course)
	VALUE ($examid, ecodeid, $stnumber, $stfname, $stlname, $stsection, $course);

	SELECT MAX(id) as id FROM student_info;
ELSE
	UPDATE student_info
    SET fname = $stfname,
		lname = $stlname,
		sect = $stsection,
        course = $course
	WHERE examid = $examid and studentno = $stnumber;

	SELECT exist_id as id;
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_generate_exam_code`(
in $examid int,
in $dte datetime,
in $hr int,
in $logby int
)
BEGIN
	DECLARE ecode TEXT;
    
    SET ecode = fnGenerateExamCode();
    
	INSERT INTO exam_code (exam_id, exam_code, dtestart, dteend)
    VALUES ($examid, ecode, $dte, DATE_ADD($dte, INTERVAL $hr HOUR));

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_select_exam_question`(
in $examid int,
in $examcode text,
in $questionid int,
in $examinee int
)
BEGIN

DECLARE ecodeid INT;

SELECT id INTO ecodeid
FROM exam_code
WHERE exam_code = $examcode;

IF (SELECT COUNT(1) = 0 FROM student_answer 
	WHERE studentid = $examinee AND questionid = $questionid 
		AND examid = $examid AND examcodeid = ecodeid) THEN
    
    INSERT INTO student_answer (studentid, examid, examcodeid, questionid)
    VALUE ($examinee, $examid, ecodeid, $questionid);
END IF;

SELECT q.id, q.examid, question_type, question, sa.answer
FROM exam_question q
LEFT JOIN student_answer sa ON q.examid = sa.examid AND q.id = sa.questionid
WHERE sa.studentid = $examinee AND q.id = $questionid AND q.examid = $examid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_select_student_exam_sheet`(
in $examid int,
in $examcode text,
in $studentno text
)
BEGIN

SELECT 
	eq.id,
	eq.question_type, eq.question, eq.answer as correct_answer, sa.answer as student_answer,
    eo.option_letter, eo.descrip as option_descrip
FROM exam_code ec
LEFT JOIN exam_head eh ON ec.exam_id = eh.id
LEFT JOIN exam_question eq ON ec.exam_id = eq.examid
LEFT JOIN exam_option eo ON ec.exam_id = eo.examid AND eq.id = eo.questionid
LEFT JOIN student_answer sa ON eq.examid = sa.examid AND eq.id = sa.questionid
LEFT JOIN student_info si ON sa.studentid = si.id
LEFT JOIN ausers u ON eh.tech_id = u.id
WHERE ec.exam_id = $examid AND si.studentno = $studentno AND ec.exam_code = $examcode
ORDER BY sa.dtecreated, eo.option_letter;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_select_user_account`(in $id int)
BEGIN

SELECT * FROM ausers WHERE id = $id;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_set_student_exam_finish_date`(
in $examid int,
in $studentid int)
BEGIN

UPDATE student_info
SET dtefinish = (SELECT MAX(dtecreated) 
				FROM student_answer 
                WHERE studentid = $studentid AND examid = $examid)
WHERE id = $studentid AND examid = $examid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_update_question_option`(
in $examid int,
in $questionid int,
in $opt char(1),
in $descrip text,
in $techby int
)
BEGIN

DECLARE $cnt INT;

SELECT COUNT(1)
INTO $cnt
FROM exam_option
WHERE examid = $examid AND questionid = $questionid and option_letter = $opt;

IF $questionid = 0 THEN
	SELECT MAX(id)
    INTO $questionid
    FROM exam_question;
END IF;

IF $cnt > 0 THEN
	INSERT INTO user_logs (id, module, act)
	VALUE ($techby, 'Exam Option', 
			(SELECT
				CONCAT('{',
					'"data_type" : "Update",',
					'"old_value" : {',
						'"examid" : "', examid, '",',
						'"questionid" : "', questionid, '",',
						'"option_letter" : "', option_letter, '"',
						'"descrip" : "', REPLACE(descrip, '\n', '\\n'), '"',
						'"tech_id" : "', tech_id, '"',
					'}, ',
					'"new_value" : {',
						'"examid" : "', $examid, '",',
						'"questionid" : "', $questionid, '",',
						'"option_letter" : "', $opt, '"',
						'"descrip" : "', REPLACE($descrip, '\n', '\\n'), '"',
						'"tech_id" : "', $techby, '"',
					'} ',
					'}'
				)
            FROM exam_option
			WHERE examid = $examid AND questionid = $questionid and option_letter = $opt)
		);
ELSE
	INSERT INTO user_logs (id, module, act)
	VALUE ($techby, 'Exam Option', 
			CONCAT('{',
				'"data_type" : "Insert",',
				'"examid" : "', $examid, '",',
				'"questionid" : "', $questionid, '",',
				'"option_letter" : "', $opt, '"',
				'"descrip" : "', REPLACE($descrip, '\n', '\\n'), '"',
				'"tech_id" : "', $techby, '"',
                '}'
			)
		);
END IF;

REPLACE INTO exam_option (examid, questionid, option_letter, descrip, tech_id)
VALUE ($examid, $questionid, $opt, $descrip, $techby);

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_update_student_exam_answer`(
in $examid int,
in $examcode text,
in $questionid int,
in $studentid int,
in $answer text)
BEGIN

DECLARE ecodeid INT;

SELECT id INTO ecodeid
FROM exam_code
WHERE exam_code = $examcode;

UPDATE student_answer 
SET answer = $answer, 
	dtecreated = now() 
WHERE studentid = $studentid 
	AND examid = $examid 
    AND examcodeid = ecodeid
    AND questionid = $questionid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_update_user_password`(
in $userid int,
in $oldpass text,
in $newpass text
)
BEGIN

IF (SELECT COUNT(1) = 1 FROM ausers WHERE pwd = $oldpass AND id = $userid) THEN
	UPDATE ausers
    SET pwd = $newpass
    WHERE id = $userid;
    
	SELECT 'success' as result;
ELSE
	SELECT 'wrong pass' as result;
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_update_user_profile`(
in $id int,
in $fname text,
in $lname text,
in $addr text,
in $city text,
in $email text,
in $phone text)
BEGIN

INSERT INTO user_logs (id, module, act)
VALUE ($id, 'User Profile', 
		(SELECT
			CONCAT('{',
				'"data_type" : "Update",',
                '"old_value" : {',
					'"firstname" : "', fname, '",',
					'"lastname" : "', lname, '",',
					'"Address" : "', addr1, '",',
					'"city" : "', city, '",',
					'"phone" : "', phone, '",',
					'"email" : "', email, '"',
                '},',
					'"new_value" : {',
					'"firstname" : "', $fname, '",',
					'"lastname" : "', $lname, '",',
					'"Address" : "', $addr, '",',
					'"city" : "', $city, '",',
					'"phone" : "', $phone, '",',
					'"email" : "', $email, '"',
                '}}'
			)
        FROM ausers
        WHERE id = $id)
        );


UPDATE ausers
SET
	fname = $fname,
	lname = $lname,
    addr1 = $addr,
    city = $city,
    phone = $phone,
    email = $email
WHERE 
	id = $id;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_validate_login`(in $username text, in $pwd text, in $ip text)
BEGIN

SELECT * FROM ausers WHERE username = $username AND pwd = $pwd;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_validate_username`(in $username text)
BEGIN

IF (SELECT COUNT(1) > 0 FROM ausers WHERE username = $username) THEN
	SELECT 'exists' as result;
ELSE 
	SELECT 'proceed' as result;
END IF;

END$$
DELIMITER ;
