-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 02 juin 2026 à 22:06
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `monuquest`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`) VALUES
(1, 'Historique'),
(2, 'Culturel'),
(3, 'Moderne');

-- --------------------------------------------------------

--
-- Structure de la table `liste_envies`
--

CREATE TABLE `liste_envies` (
  `utilisateur_id` int(11) NOT NULL,
  `monument_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `monuments`
--

CREATE TABLE `monuments` (
  `id` int(11) NOT NULL,
  `nom` varchar(120) NOT NULL,
  `pays` varchar(80) NOT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `emoji` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `categorie_id` int(11) DEFAULT NULL,
  `est_custom` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `monuments`
--

INSERT INTO `monuments` (`id`, `nom`, `pays`, `ville`, `latitude`, `longitude`, `emoji`, `description`, `categorie_id`, `est_custom`, `created_by`) VALUES
(1, 'Tour Eiffel', 'France', 'Paris', 48.858370, 2.294481, '📍', 'Symbole de Paris et de la France', 3, 0, NULL),
(2, 'Statue de la Liberté', 'États-Unis', 'New York', 40.689247, -74.044502, '📍', 'Symbole de liberté', 1, 0, NULL),
(3, 'Taj Mahal', 'Inde', 'Agra', 27.175144, 78.042142, '📍', 'Mausolée en marbre blanc', 1, 0, NULL),
(4, 'Colisée', 'Italie', 'Rome', 41.890210, 12.492231, '📍', 'Amphithéâtre romain antique', 1, 0, NULL),
(5, 'Big Ben', 'Royaume-Uni', 'Londres', 51.500729, -0.124625, '📍', 'Horloge emblématique de Londres', 1, 0, NULL),
(6, 'Pyramides de Gizeh', 'Égypte', 'Gizeh', 29.979235, 31.134202, '📍', 'Merveille du monde antique', 1, 0, NULL),
(7, 'Machu Picchu', 'Pérou', 'Cusco', -13.163141, -72.544963, '📍', 'Cité inca dans les Andes', 1, 0, NULL),
(8, 'Christ Rédempteur', 'Brésil', 'Rio de Janeiro', -22.951916, -43.210487, '📍', 'Statue dominant Rio', 2, 0, NULL),
(9, 'Opéra de Sydney', 'Australie', 'Sydney', -33.856784, 151.215297, '📍', 'Chef-d’œuvre architectural moderne', 2, 0, NULL),
(10, 'Muraille de Chine', 'Chine', 'Pékin', 40.431908, 116.570374, '📍', 'Fortification historique gigantesque', 1, 0, NULL),
(11, 'Burj Khalifa', 'Émirats arabes unis', 'Dubaï', 25.197197, 55.274376, '📍', 'Plus haute tour du monde', 3, 0, NULL),
(12, 'Sagrada Família', 'Espagne', 'Barcelone', 41.403629, 2.174356, '📍', 'Basilique de Gaudí', 2, 0, NULL),
(13, 'Arc de Triomphe', 'France', 'Paris', 48.873792, 2.295028, '📍', 'Monument napoléonien', 1, 0, NULL),
(14, 'Mont-Saint-Michel', 'France', 'Normandie', 48.636063, -1.511457, '📍', 'Îlot et abbaye médiévale', 1, 0, NULL),
(15, 'Stonehenge', 'Royaume-Uni', 'Wiltshire', 51.178882, -1.826215, '📍', 'Cercle mégalithique mystérieux', 1, 0, NULL),
(16, 'Acropole d’Athènes', 'Grèce', 'Athènes', 37.971532, 23.725749, '📍', 'Site antique majeur', 1, 0, NULL),
(17, 'Empire State Building', 'États-Unis', 'New York', 40.748817, -73.985428, '📍', 'Gratte-ciel emblématique', 3, 0, NULL),
(18, 'Golden Gate Bridge', 'États-Unis', 'San Francisco', 37.819929, -122.478255, '📍', 'Pont mondialement connu', 3, 0, NULL),
(19, 'Chichén Itzá', 'Mexique', 'Yucatán', 20.684284, -88.567783, '📍', 'Cité maya antique', 1, 0, NULL),
(20, 'Petra', 'Jordanie', 'Ma’an', 30.328454, 35.444362, '📍', 'Ville taillée dans la roche', 1, 0, NULL),
(21, 'Angkor Wat', 'Cambodge', 'Siem Reap', 13.412469, 103.866986, '📍', 'Temple bouddhiste gigantesque', 2, 0, NULL),
(22, 'Louvre', 'France', 'Paris', 48.860611, 2.337644, '📍', 'Plus grand musée du monde', 2, 0, NULL),
(23, 'Notre-Dame de Paris', 'France', 'Paris', 48.852968, 2.349902, '📍', 'Cathédrale gothique', 1, 0, NULL),
(24, 'Tour de Pise', 'Italie', 'Pise', 43.722952, 10.396597, '📍', 'Tour penchée célèbre', 1, 0, NULL),
(25, 'Basilique Saint-Pierre', 'Vatican', 'Vatican', 41.902168, 12.453937, '📍', 'Centre du catholicisme', 2, 0, NULL),
(26, 'Alhambra', 'Espagne', 'Grenade', 37.176078, -3.588141, '📍', 'Palais islamique historique', 1, 0, NULL),
(27, 'Palais de Buckingham', 'Royaume-Uni', 'Londres', 51.501364, -0.141890, '📍', 'Résidence royale britannique', 1, 0, NULL),
(28, 'Kinkaku-ji', 'Japon', 'Kyoto', 35.039370, 135.729243, '📍', 'Temple du Pavillon d’Or', 2, 0, NULL),
(29, 'Mont Fuji', 'Japon', 'Honshu', 35.360638, 138.727363, '📍', 'Volcan sacré japonais', 2, 0, NULL),
(30, 'Pont Charles', 'Tchéquie', 'Prague', 50.086509, 14.411436, '📍', 'Pont médiéval historique', 1, 0, NULL),
(31, 'Mosquée Bleue', 'Turquie', 'Istanbul', 41.005410, 28.976813, '📍', 'Mosquée ottomane célèbre', 2, 0, NULL),
(32, 'Sainte-Sophie', 'Turquie', 'Istanbul', 41.008584, 28.980175, '📍', 'Basilique historique', 1, 0, NULL),
(33, 'Temple du Lotus', 'Inde', 'New Delhi', 28.553492, 77.258826, '📍', 'Temple moderne en forme de fleur', 3, 0, NULL),
(34, 'Parthénon', 'Grèce', 'Athènes', 37.971532, 23.726726, '📍', 'Temple grec antique', 1, 0, NULL),
(35, 'Canaux de Venise', 'Italie', 'Venise', 45.440847, 12.315515, '📍', 'Ville sur l’eau', 2, 0, NULL),
(36, 'Times Square', 'États-Unis', 'New York', 40.758896, -73.985130, '📍', 'Place lumineuse de Manhattan', 3, 0, NULL),
(37, 'Central Park', 'États-Unis', 'New York', 40.782865, -73.965355, '📍', 'Grand parc urbain', 2, 0, NULL),
(38, 'Borobudur', 'Indonésie', 'Magelang', -7.607874, 110.203751, '📍', 'Temple bouddhiste monumental', 2, 0, NULL),
(39, 'Marina Bay Sands', 'Singapour', 'Singapour', 1.283394, 103.860724, '📍', 'Hôtel iconique moderne', 3, 0, NULL),
(40, 'Pont du Rialto', 'Italie', 'Venise', 45.438042, 12.335952, '📍', 'Pont historique de Venise', 1, 0, NULL),
(41, 'Neuschwanstein', 'Allemagne', 'Bavière', 47.557574, 10.749800, '📍', 'Château de conte de fées', 1, 0, NULL),
(42, 'Cité interdite', 'Chine', 'Pékin', 39.916345, 116.397155, '📍', 'Palais impérial chinois', 1, 0, NULL),
(43, 'Abou Simbel', 'Égypte', 'Assouan', 22.337231, 31.625799, '📍', 'Temple de Ramsès II', 1, 0, NULL),
(44, 'CN Tower', 'Canada', 'Toronto', 43.642566, -79.387057, '📍', 'Tour emblématique canadienne', 3, 0, NULL),
(45, 'Palais de Versailles', 'France', 'Versailles', 48.804865, 2.120355, '📍', 'Ancienne résidence royale', 1, 0, NULL),
(46, 'Hollywood Walk of Fame', 'États-Unis', 'Los Angeles', 34.101558, -118.326843, '📍', 'Allée des stars', 2, 0, NULL),
(47, 'Palais du Potala', 'Tibet', 'Lhassa', 29.657778, 91.117222, '📍', 'Ancien palais du Dalaï-Lama', 1, 0, NULL),
(48, 'Uluru', 'Australie', 'Territoire du Nord', -25.344428, 131.036882, '📍', 'Monolithe sacré australien', 2, 0, NULL),
(49, 'Temple du Ciel', 'Chine', 'Pékin', 39.882181, 116.406605, '📍', 'Temple impérial chinois', 1, 0, NULL),
(50, 'Grand Canyon', 'États-Unis', 'Arizona', 36.106965, -112.112997, '📍', 'Canyon naturel spectaculaire', 2, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `pseudo` varchar(60) NOT NULL,
  `email` varchar(180) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `objectif` int(11) NOT NULL DEFAULT 50,
  `badge` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `pseudo`, `email`, `mot_de_passe`, `objectif`, `badge`, `created_at`, `is_admin`) VALUES
(1, 'admin', 'admin@test.fr', '$2y$10$FNKjZJRUdY.NAfvtuxuH5O40DL/z/zIERWxkmWF1P32NDJv1ZzhGS', 50, NULL, '2026-06-02 21:48:45', 1);

-- --------------------------------------------------------

--
-- Structure de la table `visites`
--

CREATE TABLE `visites` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `monument_id` int(11) NOT NULL,
  `note` int(11) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `date_visite` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `liste_envies`
--
ALTER TABLE `liste_envies`
  ADD PRIMARY KEY (`utilisateur_id`,`monument_id`),
  ADD KEY `fk_envies_monument` (`monument_id`);

--
-- Index pour la table `monuments`
--
ALTER TABLE `monuments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_monuments_categorie` (`categorie_id`),
  ADD KEY `fk_monuments_created_by` (`created_by`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- Index pour la table `visites`
--
ALTER TABLE `visites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_visite` (`utilisateur_id`,`monument_id`),
  ADD KEY `fk_visites_monument` (`monument_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `monuments`
--
ALTER TABLE `monuments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `visites`
--
ALTER TABLE `visites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `liste_envies`
--
ALTER TABLE `liste_envies`
  ADD CONSTRAINT `fk_envies_monument` FOREIGN KEY (`monument_id`) REFERENCES `monuments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_envies_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `monuments`
--
ALTER TABLE `monuments`
  ADD CONSTRAINT `fk_monuments_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_monuments_created_by` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `visites`
--
ALTER TABLE `visites`
  ADD CONSTRAINT `fk_visites_monument` FOREIGN KEY (`monument_id`) REFERENCES `monuments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_visites_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
