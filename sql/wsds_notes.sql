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
-- テーブルの構造 `wsds_notes`
--
DROP TABLE IF EXISTS `wsds_notes`;
CREATE TABLE `wsds_notes` (
  `id` varchar(20) NOT NULL COMMENT '登録されたノートのID。ユーザーIDを識別できる文字列に、yyyyMMddと01～04の値を合わせたもの。',
  `start_date` varchar(12) CHARACTER SET utf8 NOT NULL COMMENT 'その週の最初の月曜日。yyyy-MM-ddの形式。',
  `times` varchar(2) CHARACTER SET utf8 NOT NULL COMMENT 'その週の何番目の学習内容かを示すもの。',
  `contents` text CHARACTER SET utf8 NOT NULL COMMENT 'ユーザーの学習事項。',
  `comment` text CHARACTER SET utf8 NOT NULL COMMENT '備考文。',
  `created_at` datetime NOT NULL COMMENT 'レコードが作成された日時。',
  `created_user_id` int(8) NOT NULL COMMENT 'レコードを作成したユーザーのID。',
  `updated_at` datetime DEFAULT NULL COMMENT 'レコードが更新された日時。',
  `updated_user_id` int(8) DEFAULT NULL COMMENT 'レコードを更新したユーザーのID。'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `wsds_notes`
--
ALTER TABLE `wsds_notes`
  ADD UNIQUE KEY `show_id` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
