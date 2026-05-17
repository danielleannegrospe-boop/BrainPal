-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 17, 2026 at 09:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brainpal_quiz`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `createActivityLog` (IN `p_activity` TEXT)   BEGIN
    INSERT INTO activity_logs(activity)
    VALUES (p_activity);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `createLesson` (IN `p_subjectID` INT, IN `p_lessonTitle` VARCHAR(255), IN `p_lessonDescription` TEXT, IN `p_createdBy` INT)   BEGIN

    INSERT INTO lessons(
        subjectID,
        lessonTitle,
        lessonDescription,
        createdBy
    )
    VALUES (
        p_subjectID,
        p_lessonTitle,
        p_lessonDescription,
        p_createdBy
    );

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `createQuestion` (IN `p_lessonID` INT, IN `p_questionText` TEXT, IN `p_questionType` ENUM('multiple_choice','identification','enumeration'), IN `p_correctAnswer` TEXT, IN `p_points` INT, IN `p_difficulty` ENUM('easy','medium','hard'), IN `p_choiceA` TEXT, IN `p_choiceB` TEXT, IN `p_choiceC` TEXT, IN `p_choiceD` TEXT)   BEGIN

    INSERT INTO questions(
        lessonID,
        questionText,
        questionType,
        correctAnswer,
        points,
        difficulty,
        choiceA,
        choiceB,
        choiceC,
        choiceD
    )
    VALUES (
        p_lessonID,
        p_questionText,
        p_questionType,
        p_correctAnswer,
        p_points,
        p_difficulty,
        p_choiceA,
        p_choiceB,
        p_choiceC,
        p_choiceD
    );

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `createQuizAttempt` (IN `p_studentID` INT, IN `p_lessonID` INT, IN `p_totalQuestions` INT)   BEGIN

    INSERT INTO quiz_attempts(
        studentID,
        lessonID,
        totalQuestions,
        score
    )
    VALUES (
        p_studentID,
        p_lessonID,
        p_totalQuestions,
        0
    );

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `createSubject` (IN `p_subjectName` VARCHAR(255), IN `p_description` TEXT, IN `p_createdBy` INT)   BEGIN

    INSERT INTO subjects(
        subjectName,
        description,
        createdBy
    )
    VALUES (
        p_subjectName,
        p_description,
        p_createdBy
    );

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `createUser` (IN `p_firstName` VARCHAR(100), IN `p_middleInitial` VARCHAR(10), IN `p_lastName` VARCHAR(100), IN `p_suffix` VARCHAR(50), IN `p_email` VARCHAR(150), IN `p_password` VARCHAR(255), IN `p_role` ENUM('admin','student'))   BEGIN

    INSERT INTO users(
        firstName,
        middleInitial,
        lastName,
        suffix,
        email,
        password,
        role
    )
    VALUES (
        p_firstName,
        p_middleInitial,
        p_lastName,
        p_suffix,
        p_email,
        p_password,
        p_role
    );

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteActivityLog` (IN `p_logID` INT)   BEGIN
    DELETE FROM activity_logs
    WHERE logID = p_logID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteLesson` (IN `p_lessonID` INT)   BEGIN

    UPDATE lessons
    SET date_deleted = CURRENT_TIMESTAMP
    WHERE lessonID = p_lessonID;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteQuestion` (IN `p_questionID` INT)   BEGIN

    UPDATE questions
    SET date_deleted = NOW()
    WHERE questionID = p_questionID;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteSubject` (IN `p_subjectID` INT)   BEGIN

    UPDATE subjects
    SET date_deleted = CURRENT_TIMESTAMP
    WHERE subjectID = p_subjectID;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getActivityLogs` ()   BEGIN
    SELECT * FROM activity_logs
    ORDER BY created_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getAttemptAnswers` (IN `p_attemptID` INT)   BEGIN
    SELECT *
    FROM attempt_answers
    WHERE attemptID = p_attemptID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getLessons` ()   BEGIN

    SELECT *
    FROM lessons
    WHERE date_deleted IS NULL
    ORDER BY date_created DESC;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getQuestionsByLesson` (IN `p_lessonID` INT)   BEGIN

    SELECT *
    FROM questions
    WHERE lessonID = p_lessonID
    AND date_deleted IS NULL;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getStudentAttempts` (IN `p_studentID` INT)   BEGIN

    SELECT *
    FROM quiz_attempts
    WHERE studentID = p_studentID
    ORDER BY submittedAt DESC;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getSubjects` ()   BEGIN

    SELECT *
    FROM subjects
    WHERE date_deleted IS NULL
    ORDER BY date_created DESC;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getUsers` ()   BEGIN

    SELECT *
    FROM users
    WHERE date_deleted IS NULL
    ORDER BY date_created DESC;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `saveAttemptAnswer` (IN `p_attemptID` INT, IN `p_questionID` INT, IN `p_studentAnswer` TEXT)   BEGIN
    INSERT INTO attempt_answers(
        attemptID,
        questionID,
        studentAnswer
    )
    VALUES (
        p_attemptID,
        p_questionID,
        p_studentAnswer
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `softDeleteUser` (IN `p_userID` INT)   BEGIN

    UPDATE users
    SET date_deleted = CURRENT_TIMESTAMP
    WHERE userID = p_userID;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `submitQuiz` (IN `p_attemptID` INT)   BEGIN

    DECLARE totalScore INT;

    SELECT COUNT(*)
    INTO totalScore
    FROM attempt_answers
    WHERE attemptID = p_attemptID
    AND isCorrect = 1;

    UPDATE quiz_attempts
    SET score = totalScore
    WHERE attemptID = p_attemptID;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateLesson` (IN `p_lessonID` INT, IN `p_subjectID` INT, IN `p_lessonTitle` VARCHAR(255), IN `p_lessonDescription` TEXT)   BEGIN

    UPDATE lessons
    SET
        subjectID = p_subjectID,
        lessonTitle = p_lessonTitle,
        lessonDescription = p_lessonDescription
    WHERE lessonID = p_lessonID;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateQuestion` (IN `p_questionID` INT, IN `p_questionText` TEXT, IN `p_correctAnswer` TEXT, IN `p_points` INT, IN `p_difficulty` ENUM('easy','medium','hard'))   BEGIN

    UPDATE questions
    SET
        questionText = p_questionText,
        correctAnswer = p_correctAnswer,
        points = p_points,
        difficulty = p_difficulty
    WHERE questionID = p_questionID;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateSubject` (IN `p_subjectID` INT, IN `p_subjectName` VARCHAR(255), IN `p_description` TEXT)   BEGIN

    UPDATE subjects
    SET
        subjectName = p_subjectName,
        description = p_description
    WHERE subjectID = p_subjectID;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateUser` (IN `p_userID` INT, IN `p_firstName` VARCHAR(100), IN `p_middleInitial` VARCHAR(10), IN `p_lastName` VARCHAR(100), IN `p_suffix` VARCHAR(50), IN `p_email` VARCHAR(150), IN `p_role` ENUM('admin','student'))   BEGIN

    UPDATE users
    SET
        firstName = p_firstName,
        middleInitial = p_middleInitial,
        lastName = p_lastName,
        suffix = p_suffix,
        email = p_email,
        role = p_role
    WHERE userID = p_userID;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `logID` int(11) NOT NULL,
  `activity` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`logID`, `activity`, `created_at`) VALUES
(1, 'New subject created: ITWS03', '2026-05-16 10:38:16'),
(2, 'New lesson created: Lesson 1', '2026-05-16 10:45:09'),
(3, 'Question added. ID: 1', '2026-05-16 10:48:54'),
(4, 'Question added. ID: 2', '2026-05-16 10:48:54'),
(5, 'Question added. ID: 3', '2026-05-16 10:48:54'),
(6, 'Question added. ID: 4', '2026-05-16 10:48:54'),
(7, 'Question added. ID: 5', '2026-05-16 10:48:54'),
(8, 'Question added. ID: 6', '2026-05-16 10:48:54'),
(9, 'Question added. ID: 7', '2026-05-16 10:48:54'),
(10, 'Question added. ID: 8', '2026-05-16 10:48:54'),
(11, 'Question added. ID: 9', '2026-05-16 10:48:54'),
(12, 'Question added. ID: 10', '2026-05-16 10:48:54'),
(13, 'Question added. ID: 11', '2026-05-16 10:48:54'),
(14, 'Question added. ID: 12', '2026-05-16 10:48:54'),
(15, 'Question added. ID: 13', '2026-05-16 10:48:54'),
(16, 'Question added. ID: 14', '2026-05-16 10:48:54'),
(17, 'Question added. ID: 15', '2026-05-16 10:48:54'),
(18, 'Admin created a lesson', '2026-05-17 00:44:39'),
(19, 'Admin deleted question', '2026-05-17 00:49:01'),
(20, 'New subject created: ITWS03', '2026-05-17 01:00:50'),
(21, 'Question added: df5hrtg', '2026-05-17 01:30:28'),
(22, 'New user registered: Ingrid Fortessi Gabriel (student)', '2026-05-17 03:25:27'),
(23, 'New user registered: Matteo Razler (student)', '2026-05-17 03:26:06'),
(24, 'Subject deleted: Programming', '2026-05-17 03:33:57');

--
-- Triggers `activity_logs`
--
DELIMITER $$
CREATE TRIGGER `before_insert_activity_log` BEFORE INSERT ON `activity_logs` FOR EACH ROW BEGIN

    IF NEW.activity IS NULL
       OR TRIM(NEW.activity) = '' THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Activity log cannot be empty';

    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `attempt_answers`
--

CREATE TABLE `attempt_answers` (
  `answerID` int(11) NOT NULL,
  `attemptID` int(11) NOT NULL,
  `questionID` int(11) NOT NULL,
  `studentAnswer` text DEFAULT NULL,
  `isCorrect` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attempt_answers`
--

INSERT INTO `attempt_answers` (`answerID`, `attemptID`, `questionID`, `studentAnswer`, `isCorrect`) VALUES
(18, 5, 17, '', 0),
(19, 6, 1, '', 0),
(20, 6, 2, '', 0),
(21, 6, 3, '', 0),
(22, 6, 4, '', 0),
(23, 6, 5, '', 0),
(24, 6, 6, '', 0),
(25, 6, 7, '', 0),
(26, 6, 8, '', 0),
(27, 6, 9, '', 0),
(28, 6, 10, '', 0),
(29, 6, 11, '', 0),
(30, 6, 12, '', 0),
(31, 6, 13, '', 0),
(32, 6, 14, '', 0),
(33, 6, 15, '', 0),
(34, 6, 16, '', 0),
(35, 7, 17, '', 0),
(36, 8, 17, '', 0),
(37, 9, 17, '', 0),
(38, 10, 17, '', 0);

--
-- Triggers `attempt_answers`
--
DELIMITER $$
CREATE TRIGGER `check_answer_before_insert` BEFORE INSERT ON `attempt_answers` FOR EACH ROW BEGIN
    DECLARE correctAns TEXT;

    SELECT correctAnswer
    INTO correctAns
    FROM questions
    WHERE questionID = NEW.questionID;

    IF LOWER(TRIM(NEW.studentAnswer)) =
       LOWER(TRIM(correctAns)) THEN

        SET NEW.isCorrect = 1;

    ELSE

        SET NEW.isCorrect = 0;

    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `lessonID` int(11) NOT NULL,
  `subjectID` int(11) NOT NULL,
  `lessonTitle` varchar(255) NOT NULL,
  `lessonDescription` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_deleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`lessonID`, `subjectID`, `lessonTitle`, `lessonDescription`, `createdBy`, `date_created`, `date_deleted`) VALUES
(1, 1, 'Lesson 1', 'kfdhihd', 1, '2026-05-16 10:45:09', NULL),
(2, 1, 'Introduction to HTML', 'Basic HTML lesson', 1, '2026-05-17 00:50:29', NULL);

--
-- Triggers `lessons`
--
DELIMITER $$
CREATE TRIGGER `after_insert_lesson` AFTER INSERT ON `lessons` FOR EACH ROW BEGIN

    INSERT INTO activity_logs(activity)
    VALUES (
        CONCAT(
            'New lesson created: ',
            NEW.lessonTitle
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_soft_delete_lesson` AFTER UPDATE ON `lessons` FOR EACH ROW BEGIN

    IF NEW.date_deleted IS NOT NULL
       AND OLD.date_deleted IS NULL THEN

        INSERT INTO activity_logs(activity)
        VALUES (
            CONCAT(
                'Lesson deleted: ',
                OLD.lessonTitle
            )
        );

    END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_lesson` BEFORE INSERT ON `lessons` FOR EACH ROW BEGIN

    IF NEW.lessonTitle IS NULL
       OR TRIM(NEW.lessonTitle) = '' THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Lesson title cannot be empty';

    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `questionID` int(11) NOT NULL,
  `lessonID` int(11) NOT NULL,
  `questionText` text NOT NULL,
  `questionType` enum('multiple_choice','identification','enumeration') NOT NULL DEFAULT 'multiple_choice',
  `correctAnswer` text NOT NULL,
  `points` int(11) NOT NULL DEFAULT 1,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_deleted` datetime DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') NOT NULL DEFAULT 'easy',
  `choiceA` text DEFAULT NULL,
  `choiceB` text DEFAULT NULL,
  `choiceC` text DEFAULT NULL,
  `choiceD` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`questionID`, `lessonID`, `questionText`, `questionType`, `correctAnswer`, `points`, `date_created`, `date_deleted`, `difficulty`, `choiceA`, `choiceB`, `choiceC`, `choiceD`) VALUES
(1, 1, 'adfgwe', 'identification', 'dgrfgr', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(2, 1, 'rgfdv', 'identification', 'rgre', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(3, 1, 'ggrd', 'identification', 'redfbrregrefg', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(4, 1, 'rgreg', 'identification', 'ggrg', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(5, 1, 'rgrre', 'identification', 'grgr', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(6, 1, 'gregr', 'identification', 'rreg', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(7, 1, 'ggrg', 'identification', 'rgrg', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(8, 1, 'grgrg', 'identification', 'rgreg', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(9, 1, 'grgrg', 'identification', 'regrg', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(10, 1, 'grgg', 'identification', 'rgr', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(11, 1, 'grgerg', 'identification', 'rrggr', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(12, 1, 'ggrg', 'identification', 'rggrg', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(13, 1, 'rgrg', 'identification', 'rgrg', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(14, 1, 'rggrrg', 'identification', 'rgrg', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(15, 1, 'grg', 'identification', 'gr', 1, '2026-05-16 10:48:54', NULL, 'easy', '', '', '', ''),
(16, 1, 'What does HTML stand for?', 'multiple_choice', 'HyperText Markup Language', 5, '2026-05-17 00:51:59', NULL, 'easy', 'HyperText Markup Language', 'Home Tool Markup Language', 'Hyperlinks Text Mark Language', 'Hyper Tool Multi Language'),
(17, 2, 'df5hrtg', 'identification', 'gtry', 1, '2026-05-17 01:30:28', NULL, 'medium', '', '', '', '');

--
-- Triggers `questions`
--
DELIMITER $$
CREATE TRIGGER `after_delete_question` AFTER UPDATE ON `questions` FOR EACH ROW BEGIN

    IF NEW.date_deleted IS NOT NULL
       AND OLD.date_deleted IS NULL THEN

        INSERT INTO activity_logs(activity)
        VALUES (
            CONCAT(
                'Question deleted: ',
                LEFT(OLD.questionText, 50)
            )
        );

    END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_insert_question` AFTER INSERT ON `questions` FOR EACH ROW BEGIN

    INSERT INTO activity_logs(activity)
    VALUES (
        CONCAT(
            'Question added: ',
            LEFT(NEW.questionText, 50)
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_question` BEFORE INSERT ON `questions` FOR EACH ROW BEGIN

    IF NEW.questionText IS NULL
       OR TRIM(NEW.questionText) = '' THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Question cannot be empty';

    END IF;

    IF NEW.points <= 0 THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Points must be greater than zero';

    END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validate_multiple_choice` BEFORE INSERT ON `questions` FOR EACH ROW BEGIN

    IF NEW.questionType = 'multiple_choice' THEN

        IF NEW.choiceA IS NULL
           OR NEW.choiceB IS NULL
           OR NEW.choiceC IS NULL
           OR NEW.choiceD IS NULL THEN

            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'Multiple choice questions require choices A-D';

        END IF;

    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `attemptID` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `lessonID` int(11) NOT NULL,
  `totalQuestions` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `submittedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`attemptID`, `studentID`, `lessonID`, `totalQuestions`, `score`, `submittedAt`) VALUES
(4, 1, 1, 5, 3, '2026-05-17 02:47:36'),
(5, 3, 2, 1, 0, '2026-05-17 02:55:15'),
(6, 3, 1, 16, 0, '2026-05-17 03:04:42'),
(7, 3, 2, 1, 0, '2026-05-17 03:14:07'),
(8, 3, 2, 1, 0, '2026-05-17 03:14:28'),
(9, 3, 2, 1, 0, '2026-05-17 03:14:43'),
(10, 3, 2, 1, 0, '2026-05-17 03:15:58');

--
-- Triggers `quiz_attempts`
--
DELIMITER $$
CREATE TRIGGER `after_submit_quiz` AFTER UPDATE ON `quiz_attempts` FOR EACH ROW BEGIN

    IF NEW.score <> OLD.score THEN

        INSERT INTO activity_logs(activity)
        VALUES (
            CONCAT(
                'Quiz submitted. Score: ',
                NEW.score,
                '/',
                NEW.totalQuestions
            )
        );

    END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_quiz_attempt` BEFORE INSERT ON `quiz_attempts` FOR EACH ROW BEGIN

    IF NEW.totalQuestions <= 0 THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
        'Total questions must be greater than zero';

    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subjectID` int(11) NOT NULL,
  `subjectName` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_deleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subjectID`, `subjectName`, `description`, `createdBy`, `date_created`, `date_deleted`) VALUES
(1, 'ITWS04', 'WEB VULNERABILITIES', NULL, '2026-05-16 10:38:16', NULL),
(2, 'Programming', 'Basic programming concepts', 1, '2026-05-17 00:55:15', '2026-05-17 03:33:57'),
(3, 'ITWS03', 'xvcrfdgv', NULL, '2026-05-17 01:00:50', NULL);

--
-- Triggers `subjects`
--
DELIMITER $$
CREATE TRIGGER `after_insert_subject` AFTER INSERT ON `subjects` FOR EACH ROW BEGIN

    INSERT INTO activity_logs(activity)
    VALUES (
        CONCAT(
            'New subject created: ',
            NEW.subjectName
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_soft_delete_subject` AFTER UPDATE ON `subjects` FOR EACH ROW BEGIN

    IF NEW.date_deleted IS NOT NULL
       AND OLD.date_deleted IS NULL THEN

        INSERT INTO activity_logs(activity)
        VALUES (
            CONCAT(
                'Subject deleted: ',
                OLD.subjectName
            )
        );

    END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_subject` BEFORE INSERT ON `subjects` FOR EACH ROW BEGIN

    IF NEW.subjectName IS NULL
       OR TRIM(NEW.subjectName) = '' THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Subject name cannot be empty';

    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleInitial` varchar(10) DEFAULT NULL,
  `lastName` varchar(100) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') NOT NULL DEFAULT 'student',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_deleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `firstName`, `middleInitial`, `lastName`, `suffix`, `email`, `password`, `role`, `date_created`, `date_deleted`) VALUES
(1, 'Admin', '', 'Account', '', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-05-16 08:27:11', NULL),
(2, 'Danielle Anne', 'DC.', 'Grospe', '', 'danielleannegrospe@gmail.com', '$2y$10$SHxqew9tj7bO1Mqw6bqwX.lR3NHNvGwMjNhC42P1Mo4TiaD.SsZzi', 'admin', '2026-05-16 09:00:14', NULL),
(3, 'Shiela Mae', 'M.', 'Galapon', '', 'shiela@gmail.com', '$2y$10$qkMa.wMRgJQ9GXW1Jdo4Fu5NPpYP4dtd5sj5NXnidXIIG8Qn1eBgK', 'student', '2026-05-16 09:00:58', NULL),
(4, 'Juana', 'D', 'Cruz', NULL, 'juan@email.com', '$2y$10$qkMa.wMRgJQ9GXW1Jdo4Fu5NPpYP4dtd5sj5NXnidXIIG8Qn1eBgK', 'student', '2026-05-17 00:57:03', NULL),
(5, 'Ingrid Fortessi', 'DC.', 'Gabriel', '', 'ingrid@gmail.com', '$2y$10$11D/VIWNvTuiSvgrD2W8.u0bJT8UjthUa6mtQ7I3B481bnVc2HsEu', 'student', '2026-05-17 03:25:27', NULL),
(6, 'Matteo', 'N.', 'Razler', '', 'matt@gmail.com', '$2y$10$hGE8QZNgXw8Aco.CC29ozORp4EmFTiG.xdjDbqWy5VwBwCMMyooLa', 'student', '2026-05-17 03:26:06', NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_insert_user` AFTER INSERT ON `users` FOR EACH ROW BEGIN

    INSERT INTO activity_logs(activity)
    VALUES (
        CONCAT(
            'New user registered: ',
            NEW.firstName,
            ' ',
            NEW.lastName,
            ' (',
            NEW.role,
            ')'
        )
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_soft_delete_user` AFTER UPDATE ON `users` FOR EACH ROW BEGIN

    IF NEW.date_deleted IS NOT NULL
       AND OLD.date_deleted IS NULL THEN

        INSERT INTO activity_logs(activity)
        VALUES (
            CONCAT(
                'User deleted: ',
                OLD.firstName,
                ' ',
                OLD.lastName
            )
        );

    END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_user` BEFORE INSERT ON `users` FOR EACH ROW BEGIN

    IF NEW.email IS NULL
       OR TRIM(NEW.email) = '' THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Email cannot be empty';

    END IF;

    IF NEW.password IS NULL
       OR LENGTH(NEW.password) < 6 THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Password must be at least 6 characters';

    END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_user_email` BEFORE INSERT ON `users` FOR EACH ROW BEGIN

    DECLARE emailCount INT;

    SELECT COUNT(*)
    INTO emailCount
    FROM users
    WHERE email = NEW.email;

    IF emailCount > 0 THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Email already exists';

    END IF;

END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`logID`);

--
-- Indexes for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD PRIMARY KEY (`answerID`),
  ADD KEY `attemptID` (`attemptID`),
  ADD KEY `questionID` (`questionID`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`lessonID`),
  ADD KEY `subjectID` (`subjectID`),
  ADD KEY `createdBy` (`createdBy`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`questionID`),
  ADD KEY `lessonID` (`lessonID`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`attemptID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `lessonID` (`lessonID`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subjectID`),
  ADD KEY `createdBy` (`createdBy`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `logID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  MODIFY `answerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `lessonID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `questionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `attemptID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subjectID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD CONSTRAINT `attempt_answers_ibfk_1` FOREIGN KEY (`attemptID`) REFERENCES `quiz_attempts` (`attemptID`) ON DELETE CASCADE,
  ADD CONSTRAINT `attempt_answers_ibfk_2` FOREIGN KEY (`questionID`) REFERENCES `questions` (`questionID`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`subjectID`) REFERENCES `subjects` (`subjectID`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `users` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`lessonID`) REFERENCES `lessons` (`lessonID`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `users` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`lessonID`) REFERENCES `lessons` (`lessonID`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `users` (`userID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
