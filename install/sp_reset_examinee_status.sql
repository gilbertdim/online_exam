DROP PROCEDURE IF EXISTS sp_reset_examinee_status;

DELIMITER $$

CREATE PROCEDURE sp_reset_examinee_status (
    IN $exam_code VARCHAR(100),
    IN $examinee_code VARCHAR(100)
)
BEGIN
    UPDATE examinee_code SET status_code = 0
    WHERE exam_code = $exam_code 
      AND examinee_code = $examinee_code;

    SELECT ROW_COUNT() AS 'updated';

END$$

DELIMITER ;

SELECT concat(routine_name, ' CREATED') AS '' 
FROM information_schema.routines 
WHERE routine_name = 'sp_reset_examinee_status'
AND routine_schema = schema();
