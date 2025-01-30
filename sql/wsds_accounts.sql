-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: mysql1029.db.sakura.ne.jp
-- 生成日時: 2025 年 1 月 20 日 00:00
-- サーバのバージョン： 5.7.40-log
-- PHP のバージョン: 8.2.16
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: ${お使いのデータベース名}
--

-- --------------------------------------------------------

--
-- テーブルの構造 `wsds_accounts`
--
DROP TABLE IF EXISTS `wsds_accounts`;
CREATE TABLE `wsds_accounts` (
  `id` int(8) NOT NULL COMMENT 'アカウントID',
  `name` varchar(32) NOT NULL COMMENT 'アカウント名',
  `login_id` varchar(256) NOT NULL COMMENT 'ログインID',
  `password` text NOT NULL COMMENT 'パスワード',
  `comment` text COMMENT 'ユーザーの個別コメント',
  `created_at` datetime NOT NULL COMMENT '登録日',
  `created_user_id` int(8) NOT NULL COMMENT '登録者ID',
  `is_stopped` int(2) DEFAULT '0' COMMENT '利用停止フラグ',
  `stopped_at` datetime DEFAULT NULL COMMENT '利用停止日',
  `stopped_user_id` int(8) DEFAULT NULL COMMENT '停止者ID',
  `updated_at` datetime NOT NULL COMMENT '更新日',
  `updated_user_id` int(8) NOT NULL COMMENT '更新者ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- テーブルのデータのダンプ `wsds_accounts`
--
INSERT INTO `wsds_accounts` (`id`, `name`, `login_id`, `password`, `comment`, `created_at`, `created_user_id`, `is_stopped`, `stopped_at`, `stopped_user_id`, `updated_at`, `updated_user_id`) VALUES
(1, 'カンパンマン', 'kampanman.newsoul@mymail.com', '$2y$10$wY8dleaVUHo3SSPGi7RIw.7sjQSRFqkIsLLuxcYOpX/Lvzqe8QBHS', 'パスワードは、Kampan1234。', '2022-09-01 00:00:00', 1, 0, NULL, NULL, '2022-09-01 00:00:00', 1),
(2, 'ボラえもん', 'Boraemon.newsoul@mymail.com', '$2y$10$NjTudVc8jfap1qhTWO5nau5rlkTJUT68m5J7iiGh3nAfOfPm54K3m', 'パスワードは、Boraemon1234。', '2022-09-01 00:00:00', 1, 0, NULL, NULL, '2022-09-01 00:00:00', 1),
(3, '福田早苗', 'Sanae-Fukuda.newsoul@mymail.com', '$2y$10$4py4VQi4Kl794fiMEcsJquQIyTNfhxluuQM7Z/04I1XEG7sDT3NPi', 'パスワードは、Sanaesan1234。', '2022-09-01 00:00:00', 1, 0, NULL, NULL, '2022-09-01 00:00:00', 1),
(4, '荒川江南', 'Konan-Arakawa.newsoul@mymail.com', '$2y$10$w68/YymnvIA/b6IIr1uiuO7ATea.NcgkYwZRk1LqPVioTbX41XQOe', 'パスワードは、Konan1234。', '2022-09-01 00:00:00', 1, 0, NULL, NULL, '2022-09-01 00:00:00', 1),
(5, '篠原進之介', 'Shinnosuke-Shinohara.newsoul@mymail.com', '$2y$10$gURkwDzJqPwVnOPlqO0qMOvf3VhsL1qxZVo95MvIjh4bAqAaqjTIq', 'パスワードは、Shinnosuke1234。', '2022-09-01 00:00:00', 1, 0, NULL, NULL, '2022-09-01 00:00:00', 1);

--
-- テーブルのインデックス `wsds_accounts`
--
ALTER TABLE `wsds_accounts`
  ADD PRIMARY KEY (`id`);
COMMIT;

--
-- テーブルの AUTO_INCREMENT `wsds_accounts`
--
ALTER TABLE `wsds_accounts`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT COMMENT 'アカウントID', AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;