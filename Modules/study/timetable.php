<?php
/**
 * Created by PhpStorm.
 * User: darkm
 * Date: 06.09.2017
 * Time: 16:11
 */

final class timetable extends Base
{
    public function index()
    {
        $this->byDay(date('Y-m-d', strtotime('today')));
    }

    public function byDay($day = null)
    {
        global $_BOTH;
        if (isset($_BOTH['day'])){
            $this->response->setJson($this->getTimetable(date('Y-m-d', strtotime($_BOTH['day']))));
        } else if ($day !== null ) {
            $this->response->setJson($this->getTimetable(date('Y-m-d', strtotime($day))));
        } else
            $this->response->setJson($this->getTimetable(date('Y-m-d', strtotime('today'))));
        $this->response(RESPONSE_STUDY_TIMETABLE_BY_DAY);
    }

    public function edit()
    {
        global $_BOTH;
        $lecture_hall = $_BOTH['lecture_hall'];
        $date = date('Y-m-d', strtotime($_BOTH['day']));
        $group_id = $this->user->get('study_group_id');
        $lesson_number = $_BOTH['lesson_number'];

        if (isset($_BOTH['edit'], $date)) {
            $this->db->query("INSERT INTO timetable (lecture_hall, `changed`) 
                                      VALUES ($lecture_hall, 1) 
                                      WHERE lesson_number = '$lesson_number', 
                                      `date` = '$date', academical_group = '$group_id'");
        }
    }

    private function getTimetable(string $day) : array
    {
        $group_id = $this->user->get('study_group_id');
        return $this->db->query("
          SELECT  lesson_number, 
                  (SELECT lesson_name FROM study_lesson WHERE lesson_id = id) as lesson_name, 
                  lesson_type,
                  (SELECT CONCAT(IFNULL(last_name, ''), ' ', SUBSTR(IFNULL(first_name, ''), 1, 1), '. ', SUBSTR(IFNULL(middle_name, '') FROM 1 FOR 1), '.') FROM user_info WHERE teacher_id = id) as teacher_name, 
                  lecture_hall
          FROM study_timetable 
          WHERE `date` = '$day' AND 
                academical_group = '$group_id'
          ORDER BY lesson_number ASC
        ")->rows;
    }
}

//http://bonch.app/?file=study/timetable @TODO: сделать проверку на логинку здесь