-- SQL script to create food_releases table for tracking daily food distributions
-- Convention dates: October 16-18, 2025 (3 days of food releases)

CREATE TABLE food_releases (
    id INT(11) NOT NULL AUTO_INCREMENT,
    food_id INT(11) NOT NULL,
    release_date DATE NOT NULL,
    release_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    released_by INT(11) NOT NULL,
    notes TEXT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (food_id) REFERENCES food(id) ON DELETE CASCADE,
    FOREIGN KEY (released_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_daily_release (food_id, release_date),
    INDEX idx_release_date (release_date),
    INDEX idx_food_id (food_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create a view for easy querying of food releases with registrant details
CREATE VIEW food_releases_view AS
SELECT 
    fr.id as release_id,
    fr.food_id,
    f.email_address,
    f.first_name,
    f.middle_name,
    f.last_name,
    f.suffix,
    f.mapping,
    fr.release_date,
    fr.release_time,
    fr.released_by,
    u.username as released_by_username,
    fr.notes,
    -- Calculate which day of the convention this is (Day 1, 2, or 3)
    CASE 
        WHEN fr.release_date = '2025-10-16' THEN 'Day 1'
        WHEN fr.release_date = '2025-10-17' THEN 'Day 2'
        WHEN fr.release_date = '2025-10-18' THEN 'Day 3'
        ELSE 'Unknown'
    END as convention_day
FROM food_releases fr
JOIN food f ON fr.food_id = f.id
JOIN users u ON fr.released_by = u.id;

-- Create a view to show registrants and their release status
CREATE VIEW food_release_status AS
SELECT 
    f.id,
    f.email_address,
    f.first_name,
    f.middle_name,
    f.last_name,
    f.suffix,
    f.mapping,
    -- Check releases for each day
    MAX(CASE WHEN fr.release_date = '2025-10-16' THEN fr.release_time END) as day1_released,
    MAX(CASE WHEN fr.release_date = '2025-10-17' THEN fr.release_time END) as day2_released,
    MAX(CASE WHEN fr.release_date = '2025-10-18' THEN fr.release_time END) as day3_released,
    -- Count total releases
    COUNT(fr.id) as total_releases,
    -- Check if can release today
    CASE 
        WHEN CURDATE() = '2025-10-16' AND MAX(CASE WHEN fr.release_date = '2025-10-16' THEN 1 END) IS NULL THEN 1
        WHEN CURDATE() = '2025-10-17' AND MAX(CASE WHEN fr.release_date = '2025-10-17' THEN 1 END) IS NULL THEN 1
        WHEN CURDATE() = '2025-10-18' AND MAX(CASE WHEN fr.release_date = '2025-10-18' THEN 1 END) IS NULL THEN 1
        ELSE 0
    END as can_release_today
FROM food f
LEFT JOIN food_releases fr ON f.id = fr.food_id
GROUP BY f.id, f.email_address, f.first_name, f.middle_name, f.last_name, f.suffix, f.mapping;

-- Insert some comments for documentation
INSERT INTO food_releases (food_id, release_date, released_by, notes) VALUES 
-- These are example entries, remove in production
-- (1, '2025-10-16', 1, 'Day 1 food release - Convention opening day'),
-- (1, '2025-10-17', 1, 'Day 2 food release'),
-- (2, '2025-10-16', 1, 'Day 1 food release')
(0, '1970-01-01', 0, 'Placeholder - remove this entry') 
ON DUPLICATE KEY UPDATE notes = 'Placeholder entry';

-- Remove the placeholder entry
DELETE FROM food_releases WHERE food_id = 0;