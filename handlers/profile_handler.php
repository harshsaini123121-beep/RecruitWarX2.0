<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$auth = new Auth();
$database = new Database();
$db = $database->connect();

if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_profile':
            try {
                // Get user basic info
                $query = "SELECT * FROM users WHERE id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    exit;
                }
                
                // Get work experience
                $query = "SELECT * FROM work_experience WHERE user_id = :user_id ORDER BY start_date DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get education
                $query = "SELECT * FROM education WHERE user_id = :user_id ORDER BY start_date DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get skills
                $query = "SELECT * FROM user_skills WHERE user_id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Calculate profile completion
                $completion = calculateProfileCompletion($user, $experience, $education, $skills);
                
                echo json_encode([
                    'success' => true,
                    'user' => $user,
                    'experience' => $experience,
                    'education' => $education,
                    'skills' => $skills,
                    'completion' => $completion
                ]);
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            try {
                $query = "UPDATE users SET 
                         first_name = :first_name,
                         last_name = :last_name,
                         email = :email,
                         phone = :phone,
                         location = :location,
                         bio = :bio
                         WHERE id = :user_id";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':first_name', $_POST['first_name']);
                $stmt->bindParam(':last_name', $_POST['last_name']);
                $stmt->bindParam(':email', $_POST['email']);
                $stmt->bindParam(':phone', $_POST['phone']);
                $stmt->bindParam(':location', $_POST['location']);
                $stmt->bindParam(':bio', $_POST['bio']);
                $stmt->bindParam(':user_id', $user_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
                }
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        case 'add_experience':
            try {
                $query = "INSERT INTO work_experience (user_id, job_title, company, location, start_date, end_date, description, is_current) 
                         VALUES (:user_id, :job_title, :company, :location, :start_date, :end_date, :description, :is_current)";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':job_title', $_POST['job_title']);
                $stmt->bindParam(':company', $_POST['company']);
                $stmt->bindParam(':location', $_POST['location']);
                $stmt->bindParam(':start_date', $_POST['start_date']);
                $stmt->bindParam(':end_date', $_POST['end_date']);
                $stmt->bindParam(':description', $_POST['description']);
                $stmt->bindParam(':is_current', $_POST['is_current'] ?? 0);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Experience added successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add experience']);
                }
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        case 'add_education':
            try {
                $query = "INSERT INTO education (user_id, degree, institution, location, start_date, end_date, description) 
                         VALUES (:user_id, :degree, :institution, :location, :start_date, :end_date, :description)";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':degree', $_POST['degree']);
                $stmt->bindParam(':institution', $_POST['institution']);
                $stmt->bindParam(':location', $_POST['location']);
                $stmt->bindParam(':start_date', $_POST['start_date']);
                $stmt->bindParam(':end_date', $_POST['end_date']);
                $stmt->bindParam(':description', $_POST['description']);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Education added successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add education']);
                }
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        case 'add_skill':
            try {
                $query = "INSERT INTO user_skills (user_id, skill_name, skill_level, category) 
                         VALUES (:user_id, :skill_name, :skill_level, :category)";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':skill_name', $_POST['skill_name']);
                $stmt->bindParam(':skill_level', $_POST['skill_level']);
                $stmt->bindParam(':category', $_POST['category']);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Skill added successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add skill']);
                }
                
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function calculateProfileCompletion($user, $experience, $education, $skills) {
    $score = 0;
    $total = 100;
    
    // Basic info (40 points)
    if (!empty($user['first_name'])) $score += 5;
    if (!empty($user['last_name'])) $score += 5;
    if (!empty($user['email'])) $score += 5;
    if (!empty($user['phone'])) $score += 5;
    if (!empty($user['location'])) $score += 10;
    if (!empty($user['bio'])) $score += 10;
    
    // Experience (30 points)
    if (count($experience) > 0) $score += 30;
    
    // Education (20 points)
    if (count($education) > 0) $score += 20;
    
    // Skills (10 points)
    if (count($skills) > 0) $score += 10;
    
    return min(100, $score);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>