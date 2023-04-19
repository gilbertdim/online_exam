DROP PROCEDURE IF EXISTS sp_exam_list;

DELIMITER $$

CREATE PROCEDURE sp_exam_list (IN $tech_id INT)

BEGIN

    select 
        eh.id, ec.exam_code, eh.subj , eh.title , eh.descrip , eh.instruction, eh.tech_id, 
        ec.dtestart , ec.dteend , mec.dtecreated, nquestions cnt, examinees
    from (
        select exam_id, max(dtecreated) dtecreated
        from exam_code ec 
        group by exam_id
    ) mec
    left join exam_code ec on ec.exam_id = mec.exam_id and ec.dtecreated = mec.dtecreated
    left join exam_head eh on eh.id = mec.exam_id
    left join ( 
        select examid, count(1) nquestions
        from exam_question eq 
        group by examid
    ) eq on eq.examid = mec.exam_id
    left join (
        select exam_code, count(id) examinees
        from examinee_code ec
        group by exam_code
    ) se on se.exam_code = ec.exam_code 
    where eh.tech_id = $tech_id;


END$$

DELIMITER ;

SELECT concat(routine_name, ' CREATED') AS '' 
FROM information_schema.routines 
WHERE routine_name = 'sp_exam_list'
AND routine_schema = schema();
