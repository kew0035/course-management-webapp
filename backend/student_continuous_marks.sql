-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-07-03 15:01:06
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

--
-- 转储表的索引
--

--
-- 表的索引 `student_continuous_marks`
--
ALTER TABLE `student_continuous_marks`
  ADD PRIMARY KEY (`scm_id`),
  ADD UNIQUE KEY `uniq_sg_component` (`sg_id`,`component`),
  ADD KEY `idx_component` (`component`),
  ADD KEY `fk_grade_components` (`course_id`,`component`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `student_continuous_marks`
--
ALTER TABLE `student_continuous_marks`
  MODIFY `scm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=369;

--
-- 限制导出的表
--

--
-- 限制表 `student_continuous_marks`
--
ALTER TABLE `student_continuous_marks`
  ADD CONSTRAINT `fk_grade_components` FOREIGN KEY (`course_id`,`component`) REFERENCES `grade_weights` (`course_id`, `component`),
  ADD CONSTRAINT `student_continuous_marks_ibfk_1` FOREIGN KEY (`sg_id`) REFERENCES `student_grades` (`sg_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
