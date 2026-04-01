-- SQL Operations for Hostel Management System

-- ==========================================
-- ADMIN OPERATIONS
-- ==========================================

-- 1. View all students
SELECT id, name, email, phone, room_id FROM Students;

-- 2. Add a new student
INSERT INTO Students (name, email, phone, room_id) 
VALUES ('New Student Name', 'new.student@example.com', '555-0000', NULL);

-- 3. Delete a student by ID
DELETE FROM Students WHERE id = 1;

-- 4. Update student's assigned room and update room occupancy
-- Example: Assigning student 2 to room 1
UPDATE Students SET room_id = 1 WHERE id = 2;
UPDATE Rooms SET occupied = occupied + 1 WHERE id = 1;

-- 5. View all rooms with availability status
SELECT id, room_number, capacity, occupied, 
       (capacity - occupied) AS available_beds
FROM Rooms;

-- 6. Add a new room
INSERT INTO Rooms (room_number, capacity, occupied) 
VALUES ('104', 2, 0);

-- 7. View all student complaints
SELECT c.id AS complaint_id, s.name AS student_name, r.room_number, c.issue, c.status
FROM Complaints c
JOIN Students s ON c.student_id = s.id
LEFT JOIN Rooms r ON s.room_id = r.id;

-- 8. Mark a complaint as resolved
UPDATE Complaints SET status = 'Resolved' WHERE id = 1;

-- ==========================================
-- STUDENT OPERATIONS
-- ==========================================

-- 1. View own profile details (Assuming student id = 1)
SELECT id, name, email, phone, room_id 
FROM Students 
WHERE id = 1;

-- 2. Submit a new complaint (Assuming student id = 1)
INSERT INTO Complaints (student_id, issue, status) 
VALUES (1, 'The AC is not cooling properly.', 'Pending');

-- 3. View own complaint history (Assuming student id = 1)
SELECT id, issue, status 
FROM Complaints 
WHERE student_id = 1;
