-- ============================================
-- Annex 7: Damage to Other Assets Table Schema
-- ============================================

CREATE TABLE IF NOT EXISTS annex7_other_assets_damage (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Location Details
    region VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    barangay VARCHAR(100) NOT NULL,

    -- Asset Details
    classification ENUM(
        'Vehicles',
        'Aircraft',
        'Weapons / Ammunition',
        'Medicines',
        'Hospital Equipment',
        'IEC Materials',
        'Furniture',
        'Educational Materials',
        'Electronics / ICT Equipment',
        'Other'
    ) NOT NULL,
    particulars VARCHAR(500) DEFAULT NULL COMMENT 'Name or description of asset',
    unit VARCHAR(50) DEFAULT NULL COMMENT 'Unit of measurement (piece, set, etc.)',
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
    INDEX idx_classification (classification),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at),
    INDEX idx_is_archived (is_archived),
    INDEX idx_location (region, province, city, barangay)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Annex 7: Other Assets Damage Reports';

-- ============================================
-- Sample Data (Optional - for testing)
-- ============================================

-- INSERT INTO annex7_other_assets_damage
-- (region, province, city, barangay, classification, particulars, unit, quantity, cost, remarks, created_by)
-- VALUES
-- ('REGION II', 'CAGAYAN', 'BAGGAO', 'Poblacion', 'Vehicles', 'Ambulance - Toyota Hilux', 'piece', 1, '2,500,000.00', 'Totally damaged by flooding', 1),
-- ('REGION II', 'CAGAYAN', 'BAGGAO', 'San Miguel', 'Electronics / ICT Equipment', 'Desktop Computers', 'piece', 15, '750,000.00', 'Water damage', 1);

-- ============================================
-- Indexes for Reports and Analytics
-- ============================================

-- Composite index for common queries
CREATE INDEX idx_classification_barangay ON annex7_other_assets_damage(classification, barangay);
