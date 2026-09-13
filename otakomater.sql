-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2024 at 03:07 PM
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
-- Database: `otakomater`
--

-- --------------------------------------------------------

--
-- Table structure for table `animes`
--

CREATE TABLE `animes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `score` decimal(3,1) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `genres` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `episodes` varchar(255) DEFAULT NULL,
  `start_airing` varchar(255) DEFAULT NULL,
  `anime_status` varchar(50) DEFAULT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `type` enum('top','series','movie') DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `animes`
--

INSERT INTO `animes` (`id`, `title`, `score`, `image_path`, `genres`, `description`, `episodes`, `start_airing`, `anime_status`, `trailer_url`, `type`, `duration`) VALUES
(2, 'Naruto: Shippuuden', 8.6, 'Photo/NarutoShippuuden.png', 'Action, Adventure, Ninja, Drama', 'Naruto Uzumaki returns to his village after a two and a half year training journey, facing new challenges and enemies.', '500', '2007', 'Completed', 'https://youtu.be/22R0j8UKRzY?si=fXnFaAcTmVEeQf3D', 'series', NULL),
(4, 'Death Note', 9.0, 'Photo/DeathNote.png', 'Mystery, Supernatural, Psychological', 'A high school student discovers a notebook that lets him kill anyone by writing their name.', '37', '2006', 'Completed', 'https://www.youtube.com/watch?v=NlJZ-YgAt-c', 'series', NULL),
(5, 'Fullmetal Alchemist: Brotherhood', 9.1, 'Photo/FullmetalAlchemistBrotherhood.png', 'Action, Adventure, Fantasy, Military, Drama', 'Two brothers search for a Philosophers Stone after an attempt to revive their deceased mother goes awry and leaves them in damaged physical forms.', '64', '2009', 'Completed', 'https://youtu.be/kx0nBaS_q50?si=X0Wnwn8v0rsbX7PI', 'series', NULL),
(6, 'Solo Leveling', 9.3, 'Photo/SoloLeveling.jpg', 'Action, Fantasy, Adventure, Supernatural', 'In a world where hunters with supernatural abilities fight deadly monsters, Sung Jinwoo, the weakest hunter ever, discovers a mysterious system that allows him to level up his abilities, transforming from the lowest-ranked hunter to an incredibly powerful protagonist.', '12', '2024', 'Completed', 'https://youtu.be/YvGSK8mIlt8?si=OomnpXsqlfpqmjMm', 'series', NULL),
(7, 'Blue Lock vs. U-20 Japan', 8.7, 'Photo/BlueLockVsU20Japan.png', 'Sports, Action, Drama, Shounen', 'The *Blue Lock* team competes against the U-20 Japan national soccer team, as they strive to prove their skills and reach new heights.', '14', '2024', 'Ongoing', 'https://youtu.be/i8RMhHZT9vU?si=lyNBp4dXOJyhuatf', 'series', NULL),
(8, 'One Piece: Gyojin Tou-hen', 8.7, 'Photo/OnePieceGyojinTouhen.png', 'Action, Adventure, Fantasy, Shounen', 'Luffy and his crew venture into the Fish-Man Island, facing new challenges and powerful enemies as they uncover hidden secrets.', '21', '2024', 'Ongoing', 'https://youtu.be/cMOCZ40-OsQ?si=117SECu8R7FfSvMT', 'series', NULL),
(9, 'Detective Conan', 8.7, 'Photo/Detective-Conan.jpg', 'Mystery, Crime, Supernatural, Shounen', 'Shinichi Kudo, a brilliant high school detective, is transformed into a child after being poisoned by a mysterious organization. Now living as Conan Edogawa, he solves complex criminal cases while searching for a way to return to his original form.', 'Unkown', '1996', 'Ongoing', 'https://youtu.be/iR2l9M0KzuQ?si=9A2W6twA3sOEs8Ue', 'series', NULL),
(10, 'Boku dake ga Inai Machi', 8.6, 'Photo/BokuDakeGaInaiMachi.png', 'Mystery, Psychological, Supernatural, Thriller', 'Satoru Fujinuma, a manga artist, is sent back in time to prevent the abduction and murder of his classmates, ultimately trying to save them from a serial killer.', '12', '2016', 'Completed', 'https://youtu.be/DwmxEAWjTQQ?si=a3HkjgAcrXNGyc99', 'series', NULL),
(11, 'Boku no Hero Academia 7th Season', 8.5, 'Photo/MyHeroAcademia7thSeason.png', 'Action, Superhero, Shounen, Drama', 'The heroes face new challenges as they continue their battle against villains and work to protect the future of society.', '21', '2024', 'Completed', 'https://youtu.be/UvEGL9KDGNQ?si=9LlzmQ2nQQf9FsH7', 'series', NULL),
(12, 'Dororo', 8.3, 'Photo/Dororo.png', 'Action, Adventure, Historical, Supernatural, Seinen', 'A young orphan named Hyakkimaru embarks on a journey to reclaim his body parts from demons that his father had sacrificed in exchange for power.', '24', '2019', 'Completed', 'https://youtu.be/YgHPBwLu0BA?si=cbLir_zCSh3iS_uA', 'series', NULL),
(13, 'Dr. Stone: New World Part 2', 8.4, 'Photo/DrStoneNewWorldPart2.png', 'Adventure, Sci-Fi, Shounen', 'Senku and his friends continue their mission to rebuild civilization using the power of science, while facing new challenges in the post-apocalyptic world.', '11', '2023', 'Completed', 'https://youtu.be/FBmcVw4R8zo?si=2hcOY7NjnxcSiXiR', 'series', NULL),
(14, 'Haikyuu!!: To the Top 2nd Season', 8.7, 'Photo/HaikyuuToTheTop2ndSeason.png', 'Sports, Comedy, Drama, Shounen', 'The Karasuno High School volleyball team continues their journey to the Nationals, facing off against strong opponents while dealing with personal growth and team dynamics.', '12', '2020', 'Completed', 'https://youtu.be/iEnDbRMbRLQ?si=3I4e1TKsH-VSK-3G', 'series', NULL),
(15, 'Jujutsu Kaisen 2nd Season', 8.9, 'Photo/JujutsuKaisen2ndSeason.png', 'Action, Supernatural, Dark Fantasy, Shounen', 'Yuji Itadori continues his journey as a Jujutsu sorcerer, facing new challenges and uncovering the mysteries of cursed energy in the ongoing battle against curses.', '23', '2023', 'Completed', 'https://youtu.be/K2I-MHhLocw?si=VwVWncK5fUuyhSyC', 'series', NULL),
(16, 'Kaijuu 8-gou', 8.3, 'Photo/Kaijuu8gou.png', 'Action, Sci-Fi, Shounen, Fantasy', 'Kafka Hibino, a man who dreams of joining the Defense Corps to fight giant kaiju, is transformed into a kaiju himself and must now fight to protect humanity.', '12', '2024', 'Completed', 'https://youtu.be/V0OZWzTAqHg?si=BVU-EKbxmU1ard_r', 'series', NULL),
(17, 'Kimetsu no Yaiba: Hashira Geiko-hen', 8.8, 'Photo/KimetsuNoYaibaHashiraGeikoHen.png', 'Action, Supernatural, Fantasy, Shounen', 'The Hashira of the Demon Slayer Corps undergo their training while facing the threat of powerful demons, showcasing their strength and dedication to the cause.', '8', '2024', 'Completed', 'https://youtu.be/PraFso1sVIc?si=NeEaNX-tIiJiIciZ', 'series', NULL),
(18, 'Kiseijuu: Sei no Kakuritsu', 8.6, 'Photo/KiseijuuSeiNoKakuritsu.png', 'Action, Horror, Sci-Fi, Supernatural, Psychological', 'Shinichi Izumi, a high school student, becomes infected by a parasite, which takes over his right hand, and together they fight other parasites threatening humanity.', '24', '2014', 'Completed', 'https://youtu.be/2ilVWE9uw88?si=ocI3dGOQH9sEaaF8', 'series', NULL),
(19, 'Your Name', 8.4, 'Photo/YourName.jpeg', 'Romance, Drama, Fantasy', 'Two teenagers share a profound, magical connection upon discovering they are swapping bodies. Things manage to become even more complicated when the boy and girl decide to meet in person.', NULL, '2016', 'Movie', 'https://www.youtube.com/watch?v=xU47nhruN-Q', 'movie', '107 minutes'),
(20, 'My Neighbor Totoro', 8.1, 'Photo/MyNeighborTotoro.jpeg', 'Family, Fantasy, Comedy', 'Two sisters relocate to rural Japan with their father to spend time with their ill mother. They face a mythical forest sprite and its woodland friends with whom they have many magical adventures.', NULL, '1988', 'Movie', 'https://www.youtube.com/watch?v=92a7Hj0ijLs', 'movie', '86 minutes'),
(21, 'Howl\'s Moving Castle', 8.2, 'Photo/HowlsMovingCastle.jpeg', 'Adventure, Fantasy, Family', 'Jealous of Sophie\'s closeness to Howl, a wizard, the Witch of Waste transforms her into an old lady. Sophie must find a way to break the spell with the help of Howl\'s friends, Calcifer and Markl.', NULL, '2004', 'Movie', 'https://www.youtube.com/watch?v=iwROgK94zcM', 'movie', '119 minutes'),
(22, 'Princess Mononoke', 8.8, 'Photo/PrincessMononoke.jpeg', 'Adventure, Fantasy, Action', 'While saving his village, Ashitaka, a heroic warrior, is befallen with a curse. To lift the curse, Ashitaka embarks on a perilous journey and gets involved in the conflict of two warring clans.', NULL, '1997', 'Movie', 'https://www.youtube.com/watch?v=4OiMOHRDs14', 'movie', '133 minutes'),
(23, 'A Silent Voice', 8.1, 'Photo/ASilentVoice.jpeg', 'Drama, Romance', 'A grade-school student with a hearing impairment is bullied and transfers to another school. Years later, the former bully is tormented by his behaviour and sets out to make amends.', NULL, '2016', 'Movie', 'https://www.youtube.com/watch?v=nfK6UgLra7g', 'movie', '130 minutes'),
(24, 'The Girl Who Leapt Through Time', 7.7, 'Photo/TheGirlWhoLeaptThroughTime.jpeg', 'Sci-Fi, Romance, Drama, Adventure, Comedy, Family, Fantasy', 'When Mokoto discovers her ability to travel through time, she decides to use it to prevent various incidents. She then discovers that there is another person close to her with the same ability.', NULL, '2006', 'Movie', 'https://www.youtube.com/watch?v=eWnTeKEsDlU', 'movie', '98 minutes'),
(25, 'Weathering With You', 7.5, 'Photo/WeatheringWithYou.jpeg', 'Romance, Fantasy, Drama', 'In Tokyo, a runaway high school student facing financial struggles ends up with a job at a small-time publisher. One day, he meets a young girl who has the ability to control the weather.', NULL, '2019', 'Movie', 'https://www.youtube.com/watch?v=Q6iK6DjV_iE', 'movie', '112 minutes'),
(26, 'Grave of the Fireflies', 8.5, 'Photo/GraveOfTheFireflies.jpeg', 'Drama, War', 'Two siblings struggle to stay together and survive during the outbreak of World War II.', NULL, '1988', 'Movie', 'https://www.youtube.com/watch?v=4vPeTSRd580', 'movie', '89 minutes'),
(27, 'Akira', 8.0, 'Photo/Akira.jpeg', 'Action, Sci-Fi, Thriller, Drama, Fantasy', 'Biker Kaneda is confronted by many anti-social elements while trying to help his friend Tetsuo who is involved in a secret government project. Tetsuo\'s supernatural persona adds the final twist.', NULL, '1988', 'Movie', 'https://www.youtube.com/watch?v=nA8KmHC2Z-g', 'movie', '124 minutes'),
(28, 'Demon Slayer: Mugen Train', 8.2, 'Photo/MugenTrain.jpeg', 'Action, Adventure, Fantasy, Thriller', 'A boy raised by boars, who wears a boar\'s head, boards the Infinity Train on a new mission with the Flame Pillar along with another boy who reveals his true power when he sleeps. Their mission is to defeat a demon who has been tormenting people and killing the demon slayers who oppose it.', NULL, '2020', 'Movie', 'https://www.youtube.com/watch?v=ATJYac_dORw', 'movie', '117 minutes'),
(29, 'Ponyo', 7.6, 'Photo/Ponyo.jpeg', 'Adventure, Fantasy, Family, Comedy', 'Sosuke rescues a goldfish trapped in a bottle. The goldfish, who is the daughter of a wizard, transforms herself into a young girl with her father\'s magic and falls in love with Sosuke.', NULL, '2008', 'Movie', 'https://www.youtube.com/watch?v=CsR3KVgBzSM', 'movie', '101 minutes'),
(30, 'Whisper of the Heart', 7.8, 'Photo/WhisperOfTheHeart.jpeg', 'Drama, Romance, Family', 'Shizuku, an aspiring writer, meets Seiji, a boy who wants to become a master luthier. The two fall in love and work simultaneously towards achieving their independent goals.', NULL, '1995', 'Movie', 'https://www.youtube.com/watch?v=0pVkiod6V0U', 'movie', '111 minutes'),
(31, 'Kiki\'s Delivery Service', 7.8, 'Photo/KikisDeliveryService.jpeg', 'Fantasy, Family', 'Thirteen-year-old Kiki tries to become an independent witch and gets a job at a delivery service. She wakes up one day to find that she can neither fly her broom nor talk to her cat.', NULL, '1989', 'Movie', 'https://www.youtube.com/watch?v=4bG17OYs-GA', 'movie', '103 minutes'),
(32, 'Spirited Away', 8.6, 'Photo/SpiritedAway.jpeg', 'Adventure, Fantasy, Family, Mystery', 'Ten-year-old Chihiro and her parents end up at an abandoned amusement park inhabited by supernatural beings. Soon, she learns that she must work to free her parents who have been turned into pigs.', NULL, '2001', 'Movie', 'https://www.youtube.com/watch?v=ByXuk9QqQkk', 'movie', '125 minutes'),
(33, 'The Wind Rises', 7.8, 'Photo/TheWindRises.jpeg', 'Drama, History, Romance, War, Biography', 'Jiro Horikoshi studies assiduously to fulfil his aim of becoming an aeronautical engineer. As WWII begins, fighter aircraft designed by him end up getting used by the Japanese Empire against its foes.', NULL, '2013', 'Movie', 'https://www.youtube.com/watch?v=TXuswYJFrDM', 'movie', '126 minutes'),
(34, 'DAN DA DAN', 9.2, 'Photo/DAN DA DAN.jpg', 'Supernatural, Romance, School, Comedy', 'High schoolers Momo and Okarun debate about ghosts vs. aliens. They investigate paranormal sites, encountering supernatural forces. Both awaken hidden powers to face the unknown. An unexpected romance blooms amid their adventures.', '12', '2024', 'Ongoing', 'https://youtu.be/NRxTXed7I7k?si=pqFhp5ch-QtX9lYs', 'top', NULL),
(35, 'Bleach: Sennen Kessen-hen - Soukoku-tan', 8.4, 'Photo/BleachSennenKessenhen.png', 'Action, Supernatural, Fantasy, Shounen', 'Ichigo Kurosaki and his friends face a new and powerful enemy in the final arc of the *Bleach* series, the Thousand-Year Blood War.', 'Unknown', '2024', 'Ongoing', 'https://youtu.be/Nq9PkH3UgEo?si=X7vk0pyPpr6F3dxN', 'top', NULL),
(36, 'Re:Zero kara Hajimeru Isekai Seikatsu Season 3', 9.5, 'Photo/re-zero-season-3.jpg', 'Isekai, Fantasy, Psychological, Drama, Supernatural', 'Natsuki Subaru continues his arduous journey in a mysterious fantasy world, facing repeated death and time loops while trying to save those he cares about. The third season follows Subaru\'s continued struggles and growth as he confronts increasingly complex challenges and emotional trials.', '16', '2024', 'Ongoing', 'https://youtu.be/5hpRO-qRy0Q?si=AHbUPE540LW2ZtX7', 'top', NULL),
(37, 'Arcane Season 2', 9.2, 'Photo/ArcaneSeason2.png', 'Action, Adventure, Drama, Fantasy, Animation', 'The story continues with the rivalry between sisters Vi and Jinx as tensions rise between the cities of Piltover and Zaun.', 'Unknown', '2024', 'Ongoing', 'https://youtu.be/ysqiEC6bLUI?si=biuM0qo50WMd8fDv', 'top', NULL),
(38, 'Natsume Yuujinchou Shichi ', 8.8, 'Photo/Natsume-Yuujinchou-Shichi.jpg', 'Supernatural, Slice of Life, Drama, Folklore', 'The seventh season continues the story of Takashi Natsume, a young man who can see yokai. He inherits his grandmother Reiko\'s Book of Friends and works to return names to supernatural beings while navigating the complex world between humans and spirits.', '12', '2024', 'Ongoing', 'https://youtu.be/yNmx5gKcHrQ?si=MIRntjEtIiB3X2Cd', 'top', NULL),
(39, 'Ao no Hako', 8.5, 'Photo/Ao-no-Hako.jpg', 'Romance, School, Drama', 'A story about a high school girl who becomes involved in a complex romantic relationship. The narrative explores themes of love, friendship, and personal growth within the context of a school setting.', '25', '2024', 'Ongoing', 'https://youtu.be/9iX6uodwBnw?si=DRdxO3pI0rZcAJ0o', 'top', NULL),
(40, 'Chi.: Chikyuu no Undou ni Tsuite', 8.4, 'Photo/Chi-Chikyuu-no-Undou.jpg', 'Educational, Science, Short Comedy', 'An educational anime that explores scientific concepts about Earth\'s movement and planetary dynamics through short, humorous episodes, making complex astronomical principles accessible and entertaining.', '24', '2024', 'Ongoing', 'https://youtu.be/u8BYhzoSQ1Q?si=CKrFSYModlGwk_jh', 'top', NULL),
(41, 'Kamonohashi Ron no Kindan Suiri 2nd Season', 8.5, 'Photo/Kamonohashi_Ron_no_Kindan_Suiri_2nd_Season.jpg', 'Mystery, Supernatural, Detective', 'In the second season, Ron Kamonohashi continues his journey as a detective, unraveling intricate mysteries involving supernatural elements. With the help of his partners, he confronts new challenges and deepens his understanding of the paranormal world.', '13', '2024', 'Ongoing', 'https://youtu.be/W6OXhilH7Xk?si=A2_ym9CYS3mnjk3L', 'top', NULL),
(42, 'Shangri-La Frontier: Kusoge Hunter, Kamige ni Idoman to su 2nd Season', 8.7, 'Photo/Shangri-La_Frontier_2nd_Season.jpg', 'Adventure, Fantasy, Action, Game', 'In the second season, the protagonist continues his quest in the virtual world, facing even more challenging games. With new allies and formidable foes, he strives to conquer the ultimate \"kamige\" titles, pushing the limits of gameplay and strategy.', '25', '2024', 'Ongoing', 'https://youtu.be/0XmM7YZjPSc?si=p4WfwYePyIHY1Tju', 'top', NULL),
(43, 'Ranma ½ (2024)', 9.0, 'Photo/Ranma_2_2024.jpg', 'Action, Comedy, Romance, Martial Arts', 'The classic series returns with new adventures as Ranma Saotome navigates the challenges of martial arts training while dealing with his unique curse. With humor, romance, and intense battles, this reboot brings fresh energy to the beloved story.', 'Unknown', '2024', 'Ongoing', 'https://youtu.be/V77a7NUof4I?si=Exg4znHOiBOJNGUx', 'top', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `anime_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `anime_id`, `user_id`, `rating`, `comment_text`, `created_at`) VALUES
(2, 2, 1, 10, 'good', '2024-11-28 19:34:06'),
(3, 35, 1, 10, 'the best', '2024-11-28 20:00:32'),
(4, 35, 1, 5, 'soo nice', '2024-11-28 20:00:44'),
(5, 34, 1, 10, 'nice', '2024-11-28 20:16:28'),
(7, 34, 2, 5, 'nice', '2024-11-28 20:53:23'),
(8, 34, 3, 9, 'woww', '2024-11-30 13:04:51'),
(9, 34, 4, 4, 'sooo overrated', '2024-11-30 13:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'afnan', 'afnan@gmail.com', '$2y$10$BYMAGVau4i/fSkuiiPnoJesawqtHCnrWgoMMlJEcitumTSHQNg5By'),
(2, 'yana', 'yana@gmail.com', '$2y$10$oK4n34R3uwJct7Q4Z78bauONF1GAvxPoHxS9eyU.f8bLKy4ConU3i'),
(3, 'aya', 'aya@gmail.com', '$2y$10$vSjMP4bgj6rZwRv8l4cype1CPtrxaQy9xPLV2OchaFSYSQ8JvydPC'),
(4, 'jana', 'jana@gmail.com', '$2y$10$U6QczsW5ZjMGwos0yw1qce9eR8oTxzE4.GPME5RN.emIFEEfaCVJ2'),
(5, 'shahd', 'shahd@gmail.com', '$2y$10$t1Rt5bNhbJxexfcPQ0eBaeNDYRN0MsT79EoTVSdjy8NeBUE4crKp2');

-- --------------------------------------------------------

--
-- Table structure for table `watchlist`
--

CREATE TABLE `watchlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `anime_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `animes`
--
ALTER TABLE `animes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anime_id` (`anime_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `watchlist`
--
ALTER TABLE `watchlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_anime` (`user_id`,`anime_id`),
  ADD KEY `anime_id` (`anime_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `animes`
--
ALTER TABLE `animes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`anime_id`) REFERENCES `animes` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `watchlist`
--
ALTER TABLE `watchlist`
  ADD CONSTRAINT `watchlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `watchlist_ibfk_2` FOREIGN KEY (`anime_id`) REFERENCES `animes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
