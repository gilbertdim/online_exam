DROP PROCEDURE IF EXISTS sp_update_student_exam_answer;

DELIMITER $$
CREATE DEFINER=`exammanager`@`%` PROCEDURE `sp_update_student_exam_answer`(
in $examid int,
in $examcode text,
in $questionid int,
in $studentid int,
in $answer text)
BEGIN

DECLARE ecodeid INT;

UPDATE student_info
SET dtefinish = (SELECT MAX(dtecreated) 
				FROM student_answer 
                WHERE studentid = $studentid AND examid = $examid)
WHERE id = $studentid AND examid = $examid;

SELECT id INTO ecodeid
FROM exam_code
WHERE exam_code = $examcode;

UPDATE student_answer sa 
JOIN exam_code ec on ec.id = sa.examcodeid AND ec.exam_id = sa.examid
SET answer = $answer, 
	sa.dtecreated = now() 
WHERE studentid = $studentid 
	AND examid = $examid 
    AND examcodeid = ecodeid
    AND questionid = $questionid
    AND now() >= ec.dtestart 
    AND now() < ec.dteend;

SELECT row_count() AS 'updated';

END$$
DELIMITER ;

SELECT concat(routine_name, ' CREATED') AS '' 
FROM information_schema.routines 
WHERE routine_name = 'sp_update_student_exam_answer'
AND routine_schema = schema();