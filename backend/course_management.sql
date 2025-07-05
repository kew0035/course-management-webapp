-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-07-03 15:02:42
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `course_management`
--

-- --------------------------------------------------------

--
-- 表的结构 `advisors`
--

CREATE TABLE `advisors` (
  `adv_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `adv_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `advisors`
--

INSERT INTO `advisors` (`adv_id`, `user_id`, `adv_name`, `email`) VALUES
(1, 5, 'Madam Leong', 'leong@utm.my');

-- --------------------------------------------------------

--
-- 表的结构 `advisor_notes`
--

CREATE TABLE `advisor_notes` (
  `adv_notes_id` int(11) NOT NULL,
  `adv_id` int(11) NOT NULL,
  `stud_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `is_confidential` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `advisor_notes`
--

INSERT INTO `advisor_notes` (`adv_notes_id`, `adv_id`, `stud_id`, `note`, `is_confidential`, `created_at`) VALUES
(8, 1, 3, 'Discussed poor performance in core subjects. Advised student to attend weekly tutoring sessions and follow up with course instructors.', 1, '2025-06-24 01:17:04'),
(9, 1, 3, 'Student reported feeling overwhelmed. Referred to campus counseling services and provided time management tips.', 1, '2025-06-24 01:17:13'),
(10, 1, 1, 'Student expressed interest in applying for internships. Shared resources and suggested companies aligned with major.', 1, '2025-06-24 01:17:23'),
(11, 1, 1, 'Offered guidance on graduate program options. Will follow up next semester with recommendation letter support.', 1, '2025-06-24 01:17:31'),
(12, 1, 2, 'Student did not attend scheduled advising session. Emailed reminder with link to reschedule.', 1, '2025-06-24 01:17:48'),
(13, 1, 2, 'Hello JC', 1, '2025-07-02 23:19:39');

-- --------------------------------------------------------

--
-- 表的结构 `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `lec_id` int(11) NOT NULL,
  `credit` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `courses`
--

INSERT INTO `courses` (`course_id`, `course_code`, `course_name`, `lec_id`, `credit`) VALUES
(1, 'SCSJ1013', 'Web Technology', 1, 3),
(2, 'SCSI1111', 'Database Systems', 1, 3);

-- --------------------------------------------------------

--
-- 表的结构 `departments`
--

CREATE TABLE `departments` (
  `dpt_id` int(11) NOT NULL,
  `dpt_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `departments`
--

INSERT INTO `departments` (`dpt_id`, `dpt_name`) VALUES
(4, 'Applied Computing'),
(1, 'Computer Science'),
(3, 'Emergent Computing'),
(2, 'Software Engineering');

-- --------------------------------------------------------

--
-- 表的结构 `grade_appeals`
--

CREATE TABLE `grade_appeals` (
  `ga_id` int(11) NOT NULL,
  `scm_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `resolved_by` int(11) DEFAULT NULL COMMENT 'Advisor/Lecturer ID',
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `grade_appeals`
--

INSERT INTO `grade_appeals` (`ga_id`, `scm_id`, `reason`, `status`, `resolved_by`, `resolved_at`) VALUES
(1, 2, '111', 'pending', NULL, NULL),
(2, 288, '1234', 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `grade_weights`
--

CREATE TABLE `grade_weights` (
  `gw_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `component` varchar(50) NOT NULL,
  `max_mark` decimal(5,2) NOT NULL,
  `weight` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `grade_weights`
--

INSERT INTO `grade_weights` (`gw_id`, `course_id`, `component`, `max_mark`, `weight`) VALUES
(1, 1, 'Quiz', 10.00, 10),
(2, 1, 'Assignment', 40.00, 20),
(6, 1, 'Homework', 10.00, 10),
(8, 1, 'Test', 100.00, 30);

-- --------------------------------------------------------

--
-- 表的结构 `lecturers`
--

CREATE TABLE `lecturers` (
  `lec_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lec_name` varchar(100) NOT NULL,
  `dpt_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `lecturers`
--

INSERT INTO `lecturers` (`lec_id`, `user_id`, `lec_name`, `dpt_id`) VALUES
(1, 4, 'Dr. Ahmad Ali', 1);

-- --------------------------------------------------------

--
-- 表的结构 `students`
--

CREATE TABLE `students` (
  `stud_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `matric_no` varchar(20) NOT NULL,
  `stud_name` varchar(100) NOT NULL,
  `adv_id` int(11) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `students`
--

INSERT INTO `students` (`stud_id`, `user_id`, `matric_no`, `stud_name`, `adv_id`, `gpa`) VALUES
(1, 1, 'A22EC0001', 'Zoey', 1, 4.00),
(2, 2, 'A22EC0122', 'JC', 1, 3.33),
(3, 3, 'A22EC0058', 'Kew', 1, 1.67);

-- --------------------------------------------------------

--
-- 表的结构 `student_continuous_marks`
--

CREATE TABLE `student_continuous_marks` (
  `scm_id` int(11) NOT NULL,
  `sg_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `component` varchar(50) NOT NULL,
  `score` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `student_continuous_marks`
--

INSERT INTO `student_continuous_marks` (`scm_id`, `sg_id`, `course_id`, `component`, `score`) VALUES
(1, 1, 1, 'Quiz', 10.00),
(2, 1, 1, 'Assignment', 40.00),
(3, 2, 1, 'Quiz', 0.00),
(4, 2, 1, 'Assignment', 20.00),
(5, 3, 1, 'Quiz', 0.00),
(6, 3, 1, 'Assignment', 10.00),
(288, 1, 1, 'Homework', 0.00),
(289, 2, 1, 'Homework', 0.00),
(290, 3, 1, 'Homework', 10.00),
(334, 1, 1, 'Test', 0.00),
(335, 2, 1, 'Test', 100.00),
(336, 3, 1, 'Test', 100.00);

--
-- 触发器 `student_continuous_marks`
--
DELIMITER $$
CREATE TRIGGER `trg_after_update_continuous_mark` AFTER UPDATE ON `student_continuous_marks` FOR EACH ROW BEGIN
    UPDATE student_grades
    SET continuous_total = (
        SELECT COALESCE(SUM(score), 0)
        FROM student_continuous_marks
        WHERE sg_id = NEW.sg_id
    )
    WHERE sg_id = NEW.sg_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `student_courses`
--

-- CREATE TABLE `student_courses` (
--   `id` int(11) NOT NULL,
--   `stud_id` int(11) DEFAULT NULL,
--   `course_id` int(11) DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `student_courses`
--

-- INSERT INTO `student_courses` (`id`, `stud_id`, `course_id`) VALUES
-- (1, 1, 1),
-- (2, 1, 2),
-- (3, 2, 1),
-- (4, 2, 2),
-- (5, 3, 1),
-- (6, 3, 2),

-- --------------------------------------------------------

--
-- 表的结构 `student_grades`
--

CREATE TABLE `student_grades` (
  `sg_id` int(11) NOT NULL,
  `stud_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `continuous_total` decimal(5,2) DEFAULT 0.00,
  `final_exam_score` decimal(5,2) DEFAULT 0.00,
  `total_score` float DEFAULT NULL,
  `grade` char(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `student_grades`
--

INSERT INTO `student_grades` (`sg_id`, `stud_id`, `course_id`, `continuous_total`, `final_exam_score`, `total_score`, `grade`) VALUES
(1, 1, 1, 60.00, 100.00, 90, 'A+'),
(2, 2, 1, 40.00, 100.00, 70, 'B+'),
(3, 3, 1, 45.00, 10.00, 48, 'C-'),
(4, 1, 2, 0.00, 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','lecturer','advisor','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'student1', 'qwer', 'student'),
(2, 'student2', 'qwer', 'student'),
(3, 'student3', 'qwer', 'student'),
(4, 'lecturer1', 'qwer', 'lecturer'),
(5, 'advisor1', 'qwer', 'advisor'),
(6, 'admin1', 'qwer', 'admin');

--
-- 转储表的索引
--

--
-- 表的索引 `advisors`
--
ALTER TABLE `advisors`
  ADD PRIMARY KEY (`adv_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- 表的索引 `advisor_notes`
--
ALTER TABLE `advisor_notes`
  ADD PRIMARY KEY (`adv_notes_id`),
  ADD KEY `adv_id` (`adv_id`),
  ADD KEY `stud_id` (`stud_id`);
ALTER TABLE `advisor_notes` ADD FULLTEXT KEY `idx_note_content` (`note`);

--
-- 表的索引 `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `idx_code` (`course_code`),
  ADD KEY `idx_lecturer` (`lec_id`);

--
-- 表的索引 `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dpt_id`),
  ADD UNIQUE KEY `dpt_name` (`dpt_name`),
  ADD KEY `idx_department_name` (`dpt_name`);

--
-- 表的索引 `grade_appeals`
--
ALTER TABLE `grade_appeals`
  ADD PRIMARY KEY (`ga_id`),
  ADD KEY `scm_id` (`scm_id`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `grade_weights`
--
ALTER TABLE `grade_weights`
  ADD PRIMARY KEY (`gw_id`),
  ADD UNIQUE KEY `uniq_course_component` (`course_id`,`component`),
  ADD KEY `idx_component` (`component`);

--
-- 表的索引 `lecturers`
--
ALTER TABLE `lecturers`
  ADD PRIMARY KEY (`lec_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_department` (`dpt_id`);

--
-- 表的索引 `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`stud_id`),
  ADD UNIQUE KEY `matric_no` (`matric_no`),
  ADD KEY `idx_matric_no` (`matric_no`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `fk_student_advisor` (`adv_id`);

--
-- 表的索引 `student_continuous_marks`
--
ALTER TABLE `student_continuous_marks`
  ADD PRIMARY KEY (`scm_id`),
  ADD UNIQUE KEY `uniq_sg_component` (`sg_id`,`component`),
  ADD KEY `idx_component` (`component`),
  ADD KEY `fk_grade_components` (`course_id`,`component`);

--
-- 表的索引 `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stud_id` (`stud_id`),
  ADD KEY `course_id` (`course_id`);

--
-- 表的索引 `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`sg_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_student_course` (`stud_id`,`course_id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `advisors`
--
ALTER TABLE `advisors`
  MODIFY `adv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `advisor_notes`
--
ALTER TABLE `advisor_notes`
  MODIFY `adv_notes_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 使用表AUTO_INCREMENT `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `departments`
--
ALTER TABLE `departments`
  MODIFY `dpt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `grade_appeals`
--
ALTER TABLE `grade_appeals`
  MODIFY `ga_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `grade_weights`
--
ALTER TABLE `grade_weights`
  MODIFY `gw_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `lec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `students`
--
ALTER TABLE `students`
  MODIFY `stud_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `student_continuous_marks`
--
ALTER TABLE `student_continuous_marks`
  MODIFY `scm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=369;

--
-- 使用表AUTO_INCREMENT `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `sg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 限制导出的表
--

--
-- 限制表 `advisors`
--
ALTER TABLE `advisors`
  ADD CONSTRAINT `advisors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- 限制表 `advisor_notes`
--
ALTER TABLE `advisor_notes`
  ADD CONSTRAINT `advisor_notes_ibfk_1` FOREIGN KEY (`adv_id`) REFERENCES `advisors` (`adv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advisor_notes_ibfk_2` FOREIGN KEY (`stud_id`) REFERENCES `students` (`stud_id`) ON DELETE CASCADE;

--
-- 限制表 `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`lec_id`) REFERENCES `lecturers` (`lec_id`) ON DELETE CASCADE;

--
-- 限制表 `grade_appeals`
--
ALTER TABLE `grade_appeals`
  ADD CONSTRAINT `grade_appeals_ibfk_1` FOREIGN KEY (`scm_id`) REFERENCES `student_continuous_marks` (`scm_id`) ON DELETE CASCADE;

--
-- 限制表 `grade_weights`
--
ALTER TABLE `grade_weights`
  ADD CONSTRAINT `grade_weights_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- 限制表 `lecturers`
--
ALTER TABLE `lecturers`
  ADD CONSTRAINT `lecturers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lecturers_ibfk_2` FOREIGN KEY (`dpt_id`) REFERENCES `departments` (`dpt_id`);

--
-- 限制表 `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_advisor` FOREIGN KEY (`adv_id`) REFERENCES `advisors` (`adv_id`),
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- 限制表 `student_continuous_marks`
--
ALTER TABLE `student_continuous_marks`
  ADD CONSTRAINT `fk_grade_components` FOREIGN KEY (`course_id`,`component`) REFERENCES `grade_weights` (`course_id`, `component`),
  ADD CONSTRAINT `student_continuous_marks_ibfk_1` FOREIGN KEY (`sg_id`) REFERENCES `student_grades` (`sg_id`) ON DELETE CASCADE;

--
-- 限制表 `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`stud_id`) REFERENCES `students` (`stud_id`),
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- 限制表 `student_grades`
--
ALTER TABLE `student_grades`
  ADD CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`stud_id`) REFERENCES `students` (`stud_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_grades_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- New Users (IDs 7–17)
INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(7, 'student4', 'qwer', 'student'),
(8, 'student5', 'qwer', 'student'),
(9, 'student6', 'qwer', 'student'),
(10, 'student7', 'qwer', 'student'),
(11, 'student8', 'qwer', 'student'),
(12, 'lecturer2', 'qwer', 'lecturer'),
(13, 'lecturer3', 'qwer', 'lecturer'),
(14, 'lecturer4', 'qwer', 'lecturer'),
(15, 'lecturer5', 'qwer', 'lecturer'),
(16, 'lecturer6', 'qwer', 'lecturer'),
(17, 'advisor2', 'qwer', 'advisor'),
(18, 'advisor3', 'qwer', 'advisor');

INSERT INTO `advisors` (`adv_id`, `user_id`, `adv_name`, `email`) VALUES
(2, 17, 'Dr. Rahman', 'rahman@utm.my'),
(3, 18, 'Ms. Lim', 'lim@utm.my');

INSERT INTO `lecturers` (`lec_id`, `user_id`, `lec_name`, `dpt_id`) VALUES
(2, 12, 'Prof. Chong Wei', 1),
(3, 13, 'Dr. Nurul Aina', 1),
(4, 14, 'Mr. Chong Wei', 1),
(5, 15, 'Dr. Sarah Lim', 1),
(6, 16, 'Prof. Ali', 1);

INSERT INTO `students` (`stud_id`, `user_id`, `matric_no`, `stud_name`, `adv_id`, `gpa`) VALUES
(4, 7, 'A22EC0044', 'Tan', 1, 3.00),
(5, 8, 'A22EC0045', 'Kuan', 2, 3.10),
(6, 9, 'A22EC0046', 'Kaizuan', 2, 3.20),
(7, 10, 'A22EC0047', 'Mingze', 3, 3.30),
(8, 11, 'A22EC0048', 'Steve', 3, 3.40);
