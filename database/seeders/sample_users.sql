-- Seeder sample user and admincashier accounts
INSERT IGNORE INTO `users` (`student_id`, `first_name`, `last_name`, `middle_name`, `email`, `password`, `sex`, `course`, `year_section`, `contact_number`, `address`, `role`, `status`) VALUES
('S0001', 'John', 'Doe', 'A', 'john.doe@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'Male', 'Computer Science', '1-A', '09171234567', '123 Main St', 'user', 'Active');

INSERT IGNORE INTO `admincashier_acc` (`username`, `first_name`, `last_name`, `middle_name`, `email`, `password`, `role`, `status`) VALUES
('admin1', 'Admin', 'Cashier', '', 'admin@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'admincashier', 'active');
