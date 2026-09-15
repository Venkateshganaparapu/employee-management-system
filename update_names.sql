USE pharmacy_db;

-- Update Customers
UPDATE customers SET name = 'Aarohi Verma' WHERE name = 'Alice Williams';
UPDATE customers SET name = 'Bhaskar Sharma' WHERE name = 'Bob Builder';
UPDATE customers SET name = 'Esha Chauhan' WHERE name = 'Emily Chen';
UPDATE customers SET name = 'Manish Reddy' WHERE name = 'Michael Rossi';
UPDATE customers SET name = 'Sneha Joshi' WHERE name = 'Sarah Jenkins';
UPDATE customers SET name = 'Deepak Kumar' WHERE name = 'David Kim';
-- Priya Patel is already Indian
UPDATE customers SET name = 'Jayesh Wagle' WHERE name = 'James Wilson';
UPDATE customers SET name = 'Mansi Gupta' WHERE name = 'Maria Garcia';
UPDATE customers SET name = 'Arhaan Ansari' WHERE name = 'Ahmed Al-Farsi';
UPDATE customers SET name = 'Lata Bansal' WHERE name = 'Linda Brown';
UPDATE customers SET name = 'Tarun Haldar' WHERE name = 'Tom Holland';

-- Update Supplier Contacts
UPDATE suppliers SET contact_person = 'Jayant Desai' WHERE contact_person = 'John Doe';
UPDATE suppliers SET contact_person = 'Jaya Singh' WHERE contact_person = 'Jane Smith';
UPDATE suppliers SET contact_person = 'Rohit Jain' WHERE contact_person = 'Robert Johnson';
UPDATE suppliers SET contact_person = 'Swati Chaturvedi' WHERE contact_person = 'Sarah Connor';
UPDATE suppliers SET contact_person = 'Brijesh Wadhwa' WHERE contact_person = 'Bruce Wayne';
UPDATE suppliers SET contact_person = 'Chirag Kulkarni' WHERE contact_person = 'Clark Kent';
UPDATE suppliers SET contact_person = 'Divya Pandey' WHERE contact_person = 'Diana Prince';
UPDATE suppliers SET contact_person = 'Balraj Ahuja' WHERE contact_person = 'Barry Allen';

-- Update Supplier Names (Making them sound more local)
UPDATE suppliers SET name = 'Swasthya Supplies' WHERE name = 'HealthLife Supplies';
UPDATE suppliers SET name = 'PharmaGenics India' WHERE name = 'PharmaGenics';
UPDATE suppliers SET name = 'Arogya Supply Co.' WHERE name = 'Wellness Supply Co.';

-- Update Admin user (optional, just in case)
UPDATE users SET username = 'admin' WHERE username = 'admin';
