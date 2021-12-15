-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 15, 2021 lúc 05:32 AM
-- Phiên bản máy phục vụ: 10.4.21-MariaDB
-- Phiên bản PHP: 7.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `backend`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ceo`
--

CREATE TABLE `ceo` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dob` timestamp NULL DEFAULT NULL,
  `phone` varchar(16) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `organization_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `industry`
--

CREATE TABLE `industry` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `industry`
--

INSERT INTO `industry` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'May mặc - Thời trang', 'Sed tempora saepe aut quo molestiae animi laboris esse', '2021-12-09 04:00:29', '2021-12-13 09:23:31', NULL),
(2, 'Nhà hàng - Ẩm thực', 'Aliqua Sint nihil saepe qui amet tempora in quasi distinctio Consequatur autem officia exercitationem ullam', '2021-12-09 04:00:34', '2021-12-13 09:23:52', NULL),
(3, 'Phụ tùng - Cơ khí', 'Assumenda officiis itaque temporibus ipsum officia ea velit', '2021-12-09 04:00:38', '2021-12-13 09:24:38', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `organization`
--

CREATE TABLE `organization` (
  `id` int(11) NOT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `ceo_id` int(11) DEFAULT NULL,
  `licence` varchar(36) NOT NULL,
  `db_name` varchar(24) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '0->trial\r\n1->active\r\n2->lock',
  `lock_status` int(11) NOT NULL DEFAULT 0 COMMENT '0-active\r\n1-lock',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `industry_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `ceo_id` int(11) DEFAULT NULL,
  `product_ids` varchar(500) DEFAULT NULL,
  `industry_id` int(11) DEFAULT NULL,
  `tax_number` varchar(24) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `dob` timestamp NULL DEFAULT NULL,
  `phone` varchar(16) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `route` varchar(255) NOT NULL,
  `description` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '0=>active\r\n1=>lock'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `role`
--

INSERT INTO `role` (`id`, `route`, `description`, `created_at`, `updated_at`, `deleted_at`, `status`) VALUES
(6, '[\"menu-toggle-status\",\"menus.index\",\"menus.create\",\"menus.store\",\"menus.show\",\"menus.edit\",\"menus.update\",\"menus.destroy\"]', 'Toàn quyền quản lý sản phẩm', '2021-12-15 03:04:34', '2021-12-15 03:04:34', NULL, 0),
(7, '[\"menus.index\"]', 'Xem danh sách sản phẩm', '2021-12-15 03:06:22', '2021-12-15 03:06:22', NULL, 0),
(8, '[\"home\"]', 'Xem trang chủ (quyền mặc định)', '2021-12-15 03:33:21', '2021-12-15 03:33:21', NULL, 0),
(9, '[\"checkoutOrders.index\",\"checkoutOrders.create\",\"checkoutOrders.store\",\"checkoutOrders.show\",\"checkoutOrders.edit\",\"checkoutOrders.update\",\"checkoutOrders.destroy\"]', 'Toàn quyền quản lý doanh thu', '2021-12-15 04:12:17', '2021-12-15 04:12:17', NULL, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(4, 'OverLord', 'admin@admin.admin', NULL, '$2y$10$Xm6oaVAXLlDtwGM9ewHGnumkyJAuEb8Yit8k/kORhcGWyRScj1mTS', NULL, '2021-12-01 04:11:15', '2021-12-01 04:11:15');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `ceo`
--
ALTER TABLE `ceo`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `industry`
--
ALTER TABLE `industry`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Chỉ mục cho bảng `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `ceo`
--
ALTER TABLE `ceo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `industry`
--
ALTER TABLE `industry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `organization`
--
ALTER TABLE `organization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
