-- Pharmacy Management System Database Schema
-- Create Database
CREATE DATABASE IF NOT EXISTS pharmacy_db;
USE pharmacy_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Staff') DEFAULT 'Staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Suppliers Table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    history_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Medicines Table
CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL,
    supplier_id INT,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- Billing (Invoices) Table
CREATE TABLE IF NOT EXISTS billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    invoice_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- Billing Items Table (Linking medicines to invoices)
CREATE TABLE IF NOT EXISTS billing_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    billing_id INT NOT NULL,
    medicine_id INT,
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (billing_id) REFERENCES billing(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE SET NULL
);

-- Mock Data Insertion

-- Admin User (Password is 'admin123' -> bcrypt hash)
INSERT INTO users (username, password_hash, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin'),
('staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff');

-- Suppliers
INSERT INTO suppliers (name, contact_person, phone, email) VALUES
('MediCorp Ayurveda', 'Jayant Desai', '123-456-7890', 'contact@medicorp.com'),
('Swasthya Supplies', 'Jaya Singh', '987-654-3210', 'sales@healthlife.com'),
('PharmaGenics India', 'Rohit Jain', '555-123-4567', 'info@pharmagenics.com');

-- Customers
INSERT INTO customers (name, phone, email, history_notes) VALUES
('Aarohi Verma', '444-555-6666', 'aarohi@email.com', 'Regular customer, prefers generic brands.'),
('Bhaskar Sharma', '777-888-9999', 'bhaskar@email.com', 'Allergic to penicillin.');

-- Medicines (Including some near-expiry ones to test alerts)
INSERT INTO medicines (name, category, quantity, price, supplier_id, expiry_date) VALUES
('Paracetamol 500mg', 'Analgesic', 500, 5.00, 1, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Amoxicillin 250mg', 'Antibiotic', 200, 12.50, 2, DATE_ADD(CURDATE(), INTERVAL 6 MONTH)),
('Ibuprofen 400mg', 'NSAID', 50, 8.00, 1, DATE_ADD(CURDATE(), INTERVAL 14 DAY)), -- Low stock & expiring soon
('Cetirizine 10mg', 'Antihistamine', 300, 4.00, 3, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Vitamin C 1000mg', 'Supplement', 150, 15.00, 2, DATE_ADD(CURDATE(), INTERVAL 5 DAY)), -- Very soon expiry
('Omeprazole 20mg', 'Antacid', 80, 25.00, 3, DATE_ADD(CURDATE(), INTERVAL -2 DAY)); -- Expired

-- Mock Billings
INSERT INTO billing (customer_id, total_amount, invoice_date) VALUES
(1, 25.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
(2, 45.50, DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(1, 15.00, CURDATE());

-- Mock Billing Items
INSERT INTO billing_items (billing_id, medicine_id, quantity, subtotal) VALUES
(1, 1, 5, 25.00),
(2, 2, 2, 25.00),
(2, 4, 3, 12.00),
(2, 3, 1, 8.50),
(3, 5, 1, 15.00);
