-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-07-19 07:13:36
-- 服务器版本： 8.0.25
-- PHP 版本： 8.0.6

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
  `adv_id` int NOT NULL,
  `user_id` int NOT NULL,
  `adv_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `advisors`
--

INSERT INTO `advisors` (`adv_id`, `user_id`, `adv_name`, `email`) VALUES
(1, 5, 'Madam Leong', 'leong@utm.my'),
(2, 17, 'Dr. Rahman', 'rahman@utm.my'),
(3, 18, 'Ms. Lim', 'lim@utm.my');

-- --------------------------------------------------------

--
-- 表的结构 `advisor_notes`
--

CREATE TABLE `advisor_notes` (
  `adv_notes_id` int NOT NULL,
  `adv_id` int NOT NULL,
  `stud_id` int NOT NULL,
  `note` text NOT NULL,
  `is_confidential` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `course_id` int NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `lec_id` int NOT NULL,
  `credit` int NOT NULL DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `courses`
--

INSERT INTO `courses` (`course_id`, `course_code`, `course_name`, `lec_id`, `credit`) VALUES
(1, 'SCSJ1013', 'Web Technology', 1, 3),
(2, 'SCSI1111', 'Database Systems', 2, 3),
(3, 'SECJ3553', 'Artificial Intelligence', 3, 3),
(4, 'SECJ3343', 'Software Quality Assurance', 4, 3),
(5, 'SECJ3563', 'Computational Intelligence', 5, 3),
(6, 'SECJ3623', 'Mobile Application Progrramming', 6, 3);

-- --------------------------------------------------------

--
-- 表的结构 `departments`
--

CREATE TABLE `departments` (
  `dpt_id` int NOT NULL,
  `dpt_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `ga_id` int NOT NULL,
  `scm_id` int NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `resolved_by` int DEFAULT NULL COMMENT 'Advisor/Lecturer ID',
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `grade_appeals`
--

INSERT INTO `grade_appeals` (`ga_id`, `scm_id`, `reason`, `status`, `resolved_by`, `resolved_at`) VALUES
(1, 2, '111', 'approved', NULL, NULL),
(4, 334, 'review part a', 'pending', NULL, NULL),
(5, 1, 'I think ive done great on this quiz, kindly check again', 'approved', NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `grade_weights`
--

CREATE TABLE `grade_weights` (
  `gw_id` int NOT NULL,
  `course_id` int NOT NULL,
  `component` varchar(50) NOT NULL,
  `max_mark` decimal(5,2) NOT NULL,
  `weight` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `grade_weights`
--

INSERT INTO `grade_weights` (`gw_id`, `course_id`, `component`, `max_mark`, `weight`) VALUES
(1, 1, 'Quiz', '10.00', 10),
(2, 1, 'Assignment1', '40.00', 20),
(8, 1, 'Test', '100.00', 30),
(11, 2, 'Quiz', '10.00', 10),
(12, 2, 'Lab', '7.00', 10),
(15, 2, 'Test', '20.00', 20),
(17, 2, 'Assignment', '30.00', 30),
(18, 1, 'Lab', '50.00', 10),
(19, 3, 'Test', '100.00', 30),
(20, 3, 'Assignment', '40.00', 20),
(21, 3, 'Lab', '20.00', 30),
(22, 3, 'Quiz', '10.00', 20),
(23, 4, 'Test', '100.00', 30),
(24, 4, 'Assignment', '40.00', 20),
(25, 4, 'Lab', '20.00', 30),
(26, 4, 'Quiz', '10.00', 20),
(27, 5, 'Test', '100.00', 30),
(28, 5, 'Assignment', '40.00', 20),
(29, 5, 'Lab', '20.00', 30),
(30, 5, 'Quiz', '10.00', 20),
(31, 6, 'Test', '100.00', 30),
(32, 6, 'Assignment', '40.00', 20),
(33, 6, 'Lab', '20.00', 30),
(34, 6, 'Quiz', '10.00', 20);

-- --------------------------------------------------------

--
-- 表的结构 `lecturers`
--

CREATE TABLE `lecturers` (
  `lec_id` int NOT NULL,
  `user_id` int NOT NULL,
  `lec_name` varchar(100) NOT NULL,
  `dpt_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `lecturers`
--

INSERT INTO `lecturers` (`lec_id`, `user_id`, `lec_name`, `dpt_id`) VALUES
(1, 4, 'Dr. Ahmad Ali', 1),
(2, 12, 'Prof. Chong Wei', 1),
(3, 13, 'Dr. Nurul Aina', 1),
(4, 14, 'Mr. Chong Wei', 1),
(5, 15, 'Dr. Sarah Lim', 1),
(6, 16, 'Prof. Ali', 1);

-- --------------------------------------------------------

--
-- 表的结构 `students`
--

CREATE TABLE `students` (
  `stud_id` int NOT NULL,
  `user_id` int NOT NULL,
  `matric_no` varchar(20) NOT NULL,
  `stud_name` varchar(100) NOT NULL,
  `adv_id` int DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `students`
--

INSERT INTO `students` (`stud_id`, `user_id`, `matric_no`, `stud_name`, `adv_id`, `gpa`) VALUES
(1, 1, 'A22EC0001', 'Zoey', 1, '4.00'),
(2, 2, 'A22EC0122', 'JC', 1, '3.00'),
(3, 3, 'A22EC0058', 'Kew', 1, '3.67'),
(4, 7, 'A22EC0108', 'Tan', 1, '3.33'),
(5, 8, 'A22EC0062', 'Kuan', 2, '3.10'),
(6, 9, 'A22EC0099', 'Kaizuan', 2, '3.20'),
(7, 10, 'A22EC0069', 'Mingze', 3, '3.30'),
(8, 11, 'A22EC0048', 'Steve', 3, '3.40'),
(9, 19, 'A22EC0110', 'Abu Bakar', 1, '3.00'),
(10, 20, 'A22EC0111', 'Tan Jia Cong', 1, '3.10'),
(11, 21, 'A22EC0112', 'Lim Jia Cong', 1, '3.20'),
(12, 22, 'A22EC0113', 'Kew Jia Cong', 1, '3.30'),
(13, 23, 'A22EC0114', 'Kuan Jia Cong', 1, '3.40'),
(14, 24, 'A22EC0115', 'Leow Jia Cong', 1, '3.50'),
(15, 25, 'A22EC0116', 'Wong Jia Cong', 2, '3.00'),
(16, 26, 'A22EC0117', 'Teo Jia Cong', 2, '3.10'),
(17, 27, 'A22EC0118', 'Tam Jia Cong', 2, '3.20'),
(18, 28, 'A22EC0119', 'Toh Jia Cong', 2, '3.30'),
(19, 29, 'A22EC0120', 'Pua Jia Cong', 2, '3.40'),
(20, 30, 'A22EC0121', 'Lee Jia Cong', 2, '3.50');

-- --------------------------------------------------------

--
-- 表的结构 `student_continuous_marks`
--

CREATE TABLE `student_continuous_marks` (
  `scm_id` int NOT NULL,
  `sg_id` int NOT NULL,
  `course_id` int NOT NULL,
  `component` varchar(50) NOT NULL,
  `score` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `student_continuous_marks`
--

INSERT INTO `student_continuous_marks` (`scm_id`, `sg_id`, `course_id`, `component`, `score`) VALUES
(1, 1, 1, 'Quiz', '10.00'),
(2, 1, 1, 'Assignment1', '40.00'),
(3, 2, 1, 'Quiz', '5.00'),
(4, 2, 1, 'Assignment1', '20.00'),
(5, 3, 1, 'Quiz', '5.00'),
(6, 3, 1, 'Assignment1', '20.00'),
(334, 1, 1, 'Test', '100.00'),
(335, 2, 1, 'Test', '100.00'),
(336, 3, 1, 'Test', '100.00'),
(411, 5, 1, 'Quiz', '7.50'),
(412, 5, 1, 'Assignment1', '30.00'),
(413, 5, 1, 'Test', '70.00'),
(423, 6, 1, 'Quiz', '0.00'),
(424, 6, 1, 'Assignment1', '0.00'),
(425, 6, 1, 'Test', '0.00'),
(426, 7, 1, 'Quiz', '0.00'),
(427, 7, 1, 'Assignment1', '0.00'),
(428, 7, 1, 'Test', '0.00'),
(429, 8, 1, 'Quiz', '0.00'),
(430, 8, 1, 'Assignment1', '0.00'),
(431, 8, 1, 'Test', '0.00'),
(432, 9, 1, 'Quiz', '0.00'),
(433, 9, 1, 'Assignment1', '0.00'),
(434, 9, 1, 'Test', '0.00'),
(452, 10, 1, 'Quiz', '0.00'),
(453, 10, 1, 'Assignment1', '0.00'),
(454, 10, 1, 'Test', '0.00'),
(456, 11, 1, 'Quiz', '0.00'),
(457, 11, 1, 'Assignment1', '0.00'),
(458, 11, 1, 'Test', '0.00'),
(460, 12, 1, 'Quiz', '0.00'),
(461, 12, 1, 'Assignment1', '0.00'),
(462, 12, 1, 'Test', '0.00'),
(464, 13, 1, 'Quiz', '0.00'),
(465, 13, 1, 'Assignment1', '0.00'),
(466, 13, 1, 'Test', '0.00'),
(468, 14, 1, 'Quiz', '0.00'),
(469, 14, 1, 'Assignment1', '0.00'),
(470, 14, 1, 'Test', '0.00'),
(472, 15, 1, 'Quiz', '0.00'),
(473, 15, 1, 'Assignment1', '0.00'),
(474, 15, 1, 'Test', '0.00'),
(476, 16, 1, 'Quiz', '0.00'),
(477, 16, 1, 'Assignment1', '0.00'),
(478, 16, 1, 'Test', '0.00'),
(480, 4, 2, 'Quiz', '10.00'),
(481, 17, 2, 'Quiz', '0.00'),
(482, 18, 2, 'Quiz', '0.00'),
(483, 19, 2, 'Quiz', '0.00'),
(484, 20, 2, 'Quiz', '0.00'),
(485, 21, 2, 'Quiz', '0.00'),
(486, 22, 2, 'Quiz', '0.00'),
(487, 23, 2, 'Quiz', '0.00'),
(488, 24, 2, 'Quiz', '0.00'),
(489, 25, 2, 'Quiz', '0.00'),
(490, 26, 2, 'Quiz', '0.00'),
(491, 27, 2, 'Quiz', '0.00'),
(492, 28, 2, 'Quiz', '0.00'),
(499, 4, 2, 'Lab', '7.00'),
(500, 17, 2, 'Lab', '0.00'),
(501, 18, 2, 'Lab', '0.00'),
(502, 19, 2, 'Lab', '0.00'),
(503, 20, 2, 'Lab', '0.00'),
(504, 21, 2, 'Lab', '0.00'),
(505, 22, 2, 'Lab', '0.00'),
(506, 23, 2, 'Lab', '0.00'),
(507, 24, 2, 'Lab', '0.00'),
(508, 25, 2, 'Lab', '0.00'),
(509, 26, 2, 'Lab', '0.00'),
(510, 27, 2, 'Lab', '0.00'),
(511, 28, 2, 'Lab', '0.00'),
(516, 4, 2, 'Test', '20.00'),
(517, 17, 2, 'Test', '0.00'),
(518, 18, 2, 'Test', '0.00'),
(519, 19, 2, 'Test', '0.00'),
(520, 20, 2, 'Test', '0.00'),
(521, 21, 2, 'Test', '0.00'),
(522, 22, 2, 'Test', '0.00'),
(523, 23, 2, 'Test', '0.00'),
(524, 24, 2, 'Test', '0.00'),
(525, 25, 2, 'Test', '0.00'),
(526, 26, 2, 'Test', '0.00'),
(527, 27, 2, 'Test', '0.00'),
(528, 28, 2, 'Test', '0.00'),
(529, 4, 2, 'Assignment', '30.00'),
(530, 17, 2, 'Assignment', '0.00'),
(531, 18, 2, 'Assignment', '0.00'),
(532, 19, 2, 'Assignment', '0.00'),
(533, 20, 2, 'Assignment', '0.00'),
(534, 21, 2, 'Assignment', '0.00'),
(535, 22, 2, 'Assignment', '0.00'),
(536, 23, 2, 'Assignment', '0.00'),
(537, 24, 2, 'Assignment', '0.00'),
(538, 25, 2, 'Assignment', '0.00'),
(539, 26, 2, 'Assignment', '0.00'),
(540, 27, 2, 'Assignment', '0.00'),
(541, 28, 2, 'Assignment', '0.00'),
(550, 1, 1, 'Lab', '0.00'),
(551, 2, 1, 'Lab', '0.00'),
(552, 3, 1, 'Lab', '0.00'),
(553, 5, 1, 'Lab', '0.00'),
(554, 6, 1, 'Lab', '0.00'),
(555, 7, 1, 'Lab', '0.00'),
(556, 8, 1, 'Lab', '0.00'),
(557, 9, 1, 'Lab', '0.00'),
(558, 10, 1, 'Lab', '0.00'),
(559, 11, 1, 'Lab', '0.00'),
(560, 12, 1, 'Lab', '0.00'),
(561, 13, 1, 'Lab', '0.00'),
(562, 14, 1, 'Lab', '0.00'),
(563, 15, 1, 'Lab', '0.00'),
(564, 16, 1, 'Lab', '0.00');

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
-- 表的结构 `student_grades`
--

CREATE TABLE `student_grades` (
  `sg_id` int NOT NULL,
  `stud_id` int NOT NULL,
  `course_id` int NOT NULL,
  `continuous_total` decimal(5,2) DEFAULT '0.00',
  `final_exam_score` decimal(5,2) DEFAULT '0.00',
  `total_score` float DEFAULT NULL,
  `grade` char(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `student_grades`
--

INSERT INTO `student_grades` (`sg_id`, `stud_id`, `course_id`, `continuous_total`, `final_exam_score`, `total_score`, `grade`) VALUES
(1, 1, 1, '62.00', '100.00', 92, 'A+'),
(2, 2, 1, '47.00', '60.00', 65, 'B'),
(3, 3, 1, '46.00', '100.00', 76, 'A-'),
(4, 1, 2, '67.00', '100.00', 100, 'A+'),
(5, 4, 1, '44.50', '90.00', 71.5, 'B+'),
(6, 5, 1, '42.00', '75.00', 66.6, 'B'),
(7, 6, 1, '50.00', '70.00', 71, 'B+'),
(8, 7, 1, '58.00', '85.00', 84.4, 'A'),
(9, 8, 1, '47.00', '60.00', 64.1, 'B-'),
(10, 14, 1, '40.00', '60.00', 58.0, 'C+'),
(11, 15, 1, '45.00', '50.00', 60, 'B-'),
(12, 16, 1, '55.00', '55.00', 71.5, 'B+'),
(13, 17, 1, '35.00', '30.00', 44, 'D+'),
(14, 18, 1, '57.00', '78.00', 80.4, 'A'),
(15, 19, 1, '60.00', '98.00', 89.4, 'A'),
(16, 20, 1, '65.00', '88.00', 91.4, 'A+'),
(17, 9, 2, '0.00', '0.00', NULL, NULL),
(18, 10, 2, '0.00', '0.00', NULL, NULL),
(19, 11, 2, '0.00', '0.00', NULL, NULL),
(20, 12, 2, '0.00', '0.00', NULL, NULL),
(21, 13, 2, '0.00', '0.00', NULL, NULL),
(22, 14, 2, '0.00', '0.00', NULL, NULL),
(23, 15, 2, '0.00', '0.00', NULL, NULL),
(24, 16, 2, '0.00', '0.00', NULL, NULL),
(25, 17, 2, '0.00', '0.00', NULL, NULL),
(26, 18, 2, '0.00', '0.00', NULL, NULL),
(27, 19, 2, '0.00', '0.00', NULL, NULL),
(28, 20, 2, '0.00', '0.00', NULL, NULL),
(29, 1, 3, '60.00', '100.00', 90, 'A+'),
(30, 1, 4, '60.00', '100.00', 90, 'A+'),
(31, 1, 5, '47.00', '60.00', 65, 'B'),
(32, 1, 6, '36.00', '100.00', 76, 'A-'),
(33, 2, 3, '62.00', '100.00', 90, 'A+'),
(34, 2, 4, '42.00', '75.00', 66.6, 'B'),
(35, 2, 5, '58.00', '85.00', 784.4, 'A'),
(36, 2, 6, '47.00', '60.00', 64.1, 'B-');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','lecturer','advisor','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'student1', 'qwer', 'student'),
(2, 'student2', 'qwer', 'student'),
(3, 'student3', 'qwer', 'student'),
(4, 'lecturer1', 'qwer', 'lecturer'),
(5, 'advisor1', 'qwer', 'advisor'),
(6, 'admin1', 'qwer', 'admin'),
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
(18, 'advisor3', 'qwer', 'advisor'),
(19, 'student9', 'qwer', 'student'),
(20, 'student10', 'qwer', 'student'),
(21, 'student11', 'qwer', 'student'),
(22, 'student12', 'qwer', 'student'),
(23, 'student13', 'qwer', 'student'),
(24, 'student14', 'qwer', 'student'),
(25, 'student15', 'qwer', 'student'),
(26, 'student16', 'qwer', 'student'),
(27, 'student17', 'qwer', 'student'),
(28, 'student18', 'qwer', 'student'),
(29, 'student19', 'qwer', 'student'),
(30, 'student20', 'qwer', 'student');

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
  MODIFY `adv_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `advisor_notes`
--
ALTER TABLE `advisor_notes`
  MODIFY `adv_notes_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 使用表AUTO_INCREMENT `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `departments`
--
ALTER TABLE `departments`
  MODIFY `dpt_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `grade_appeals`
--
ALTER TABLE `grade_appeals`
  MODIFY `ga_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `grade_weights`
--
ALTER TABLE `grade_weights`
  MODIFY `gw_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- 使用表AUTO_INCREMENT `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `lec_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `students`
--
ALTER TABLE `students`
  MODIFY `stud_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用表AUTO_INCREMENT `student_continuous_marks`
--
ALTER TABLE `student_continuous_marks`
  MODIFY `scm_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=565;

--
-- 使用表AUTO_INCREMENT `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `sg_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
  ADD CONSTRAINT `fk_grade_components` FOREIGN KEY (`course_id`,`component`) REFERENCES `grade_weights` (`course_id`, `component`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_continuous_marks_ibfk_1` FOREIGN KEY (`sg_id`) REFERENCES `student_grades` (`sg_id`) ON DELETE CASCADE;

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
