DROP PROCEDURE IF EXISTS `sp_check_exam_examinee`;

DELIMITER $$

CREATE PROCEDURE sp_check_exam_examinee(
    IN $exam_code VARCHAR(100),
    IN $examinee_code VARCHAR(100),
    IN $cur_date_time VARCHAR(20)
)
BEGIN


    UPDATE examinee_code ec 
    JOIN exam_code e ON e.exam_code = ec.exam_code
    SET status_code = 1
    WHERE e.exam_code = $exam_code
    AND dtestart <= $cur_date_time
    AND dteend > $cur_date_time
    AND ec.examinee_code = $examinee_code
    AND ec.status_code < 1;

    SELECT id student_id
    FROM examinee_code
    WHERE exam_code = $exam_code
    and examinee_code = $examinee_code
    and status_code = 1
    AND ROW_COUNT() = 1;

END$$ 

DELIMITER ;

SELECT concat(routine_name, ' CREATED') AS '' 
FROM information_schema.routines 
WHERE routine_name = 'sp_check_exam_examinee'
AND routine_schema = schema();