-- First, insert test categories
INSERT INTO test_categories (category_name, description) VALUES
('Hematology', 'Blood cell counts and related tests'),
('Biochemistry', 'Tests for various chemical substances in blood'),
('Hormone', 'Endocrine system and hormone level tests'),
('Cardiac', 'Heart and cardiovascular system tests'),
('Diabetes', 'Blood sugar and diabetes-related tests'),
('Thyroid', 'Thyroid function tests'),
('Liver', 'Liver function tests'),
('Kidney', 'Kidney function tests'),
('Vitamin', 'Vitamin level tests'),
('Allergy', 'Allergy and immunology tests');

-- Then, insert tests with references to categories
INSERT INTO tests (test_name, category_id, method, price, unit, normal_range, sample_type, status) VALUES
-- Hematology Tests
('Complete Blood Count (CBC)', 1, 'Flow Cytometry', 500.00, 'Various', 'Varies by parameter', 'Whole Blood', 1),
('Hemoglobin', 1, 'Spectrophotometry', 150.00, 'g/dL', '13.5-17.5 g/dL (male), 12.0-15.5 g/dL (female)', 'Whole Blood', 1),
('Platelet Count', 1, 'Flow Cytometry', 200.00, 'cells/µL', '150,000-450,000/µL', 'Whole Blood', 1),

-- Biochemistry Tests
('Lipid Profile', 2, 'Spectrophotometry', 800.00, 'mg/dL', 'Varies by parameter', 'Serum', 1),
('Liver Function Test', 2, 'Spectrophotometry', 1000.00, 'Various', 'Varies by parameter', 'Serum', 1),
('Kidney Function Test', 2, 'Spectrophotometry', 900.00, 'Various', 'Varies by parameter', 'Serum', 1),

-- Hormone Tests
('Testosterone', 3, 'ELISA', 1200.00, 'ng/dL', '280-1,100 ng/dL (male)', 'Serum', 1),
('Estradiol', 3, 'ELISA', 1100.00, 'pg/mL', '10-50 pg/mL (male), 30-400 pg/mL (female)', 'Serum', 1),

-- Cardiac Tests
('Troponin', 4, 'ELISA', 1500.00, 'ng/mL', '<0.04 ng/mL', 'Serum', 1),
('CK-MB', 4, 'ELISA', 1200.00, 'U/L', '<25 U/L', 'Serum', 1),

-- Diabetes Tests
('HbA1c', 5, 'HPLC', 800.00, '%', '4.0-5.6%', 'Whole Blood', 1),
('Fasting Blood Sugar', 5, 'GOD-POD', 200.00, 'mg/dL', '70-100 mg/dL', 'Plasma', 1),

-- Thyroid Tests
('TSH', 6, 'ELISA', 600.00, 'mIU/L', '0.4-4.0 mIU/L', 'Serum', 1),
('T3', 6, 'ELISA', 700.00, 'ng/dL', '80-200 ng/dL', 'Serum', 1),
('T4', 6, 'ELISA', 700.00, 'µg/dL', '5.0-12.0 µg/dL', 'Serum', 1),

-- Liver Tests
('SGPT/ALT', 7, 'UV Kinetic', 300.00, 'U/L', '7-56 U/L', 'Serum', 1),
('SGOT/AST', 7, 'UV Kinetic', 300.00, 'U/L', '5-40 U/L', 'Serum', 1),

-- Kidney Tests
('Creatinine', 8, 'Jaffe Method', 250.00, 'mg/dL', '0.7-1.3 mg/dL (male), 0.6-1.1 mg/dL (female)', 'Serum', 1),
('Urea', 8, 'UV Kinetic', 250.00, 'mg/dL', '15-45 mg/dL', 'Serum', 1),

-- Vitamin Tests
('Vitamin D', 9, 'CLIA', 1800.00, 'ng/mL', '20-50 ng/mL', 'Serum', 1),
('Vitamin B12', 9, 'CLIA', 1200.00, 'pg/mL', '200-900 pg/mL', 'Serum', 1),

-- Allergy Tests
('Total IgE', 10, 'ELISA', 1000.00, 'IU/mL', '<100 IU/mL', 'Serum', 1),
('Specific IgE Panel', 10, 'ImmunoCAP', 2500.00, 'kU/L', '<0.35 kU/L', 'Serum', 1);

-- Insert demo content into the test_parameters table
INSERT INTO test_parameters (test_id, parameter_name, default_unit) VALUES
(1, 'Hemoglobin', 'g/dL'),
(1, 'White Blood Cell Count', 'cells/mcL'),
(1, 'Platelet Count', 'cells/mcL'),
(2, 'Blood Glucose (Fasting)', 'mg/dL'),
(2, 'Blood Glucose (Postprandial)', 'mg/dL'),
(3, 'Cholesterol', 'mg/dL'),
(3, 'HDL Cholesterol', 'mg/dL'),
(3, 'LDL Cholesterol', 'mg/dL'),
(3, 'Triglycerides', 'mg/dL');