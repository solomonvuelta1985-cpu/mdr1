-- ============================================
-- Annex 6: Infrastructure Damage Table Schema
-- ============================================

CREATE TABLE IF NOT EXISTS annex6_infrastructure_damage (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Location Details
    region VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    barangay VARCHAR(100) NOT NULL,

    -- Infrastructure Details
    type ENUM(
        'Road',
        'Bridge',
        'Flood Control',
        'Government Facilities',
        'Health Facilities',
        'Schools',
        'Cultural Heritage',
        'Utility Service Facilities',
        'Private'
    ) NOT NULL,
    classification ENUM('National', 'Local') DEFAULT NULL,
    name VARCHAR(255) DEFAULT NULL COMMENT 'Name of infrastructure',

    -- Damage Details
    totally_damaged INT DEFAULT 0,
    partially_damaged INT DEFAULT 0,
    total_damaged INT DEFAULT 0,
    unit VARCHAR(50) DEFAULT NULL COMMENT 'Unit of measurement (piece, meter, etc.)',
    quantity DECIMAL(10, 2) DEFAULT 0,
    cost VARCHAR(100) DEFAULT NULL COMMENT 'Estimated cost of damage',

    -- Additional Information
    remarks VARCHAR(500) DEFAULT NULL,

    -- Standard Audit Fields
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_archived BOOLEAN DEFAULT FALSE,

    -- Foreign Keys
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,

    -- Indexes for Performance
    INDEX idx_barangay (barangay),
    INDEX idx_type (type),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at),
    INDEX idx_is_archived (is_archived),
    INDEX idx_location (region, province, city, barangay)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Annex 6: Infrastructure Damage Reports';

-- ============================================
-- Sample Data (Optional - for testing)
-- ============================================

-- INSERT INTO annex6_infrastructure_damage
-- (region, province, city, barangay, type, classification, name,
--  totally_damaged, partially_damaged, total_damaged, unit, quantity, cost, remarks, created_by)
-- VALUES
-- ('REGION II', 'CAGAYAN', 'BAGGAO', 'Poblacion', 'Bridge', 'National', 'Dolores Bridge',
--  1, 0, 1, 'piece', 1, '5,000,000.00', 'Bridge totally damaged due to flooding', 1),
-- ('REGION II', 'CAGAYAN', 'BAGGAO', 'San Miguel', 'Schools', 'Local', NULL,
--  0, 3, 3, 'building', 3, '2,000,000.00', 'Three schools partially damaged', 1);

-- ============================================
-- Indexes for Reports and Analytics
-- ============================================

-- Composite index for common queries
CREATE INDEX idx_type_barangay ON annex6_infrastructure_damage(type, barangay);
CREATE INDEX idx_classification_type ON annex6_infrastructure_damage(classification, type);
