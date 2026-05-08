<?php
// api_jobs.php
// Fetches job postings from the database and sends them to the frontend in JSON format (for AJAX)

header('Content-Type: application/json; charset=utf-8');
require_once 'db_connect.php';

// Check if there is a search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    if ($search) {
        // If searching, filter by title or description (Using Prepared Statements)
        $stmt = $pdo->prepare("
            SELECT j.job_id, j.title, j.description, e.company_name 
            FROM Jobs j
            JOIN Employers e ON j.employer_id = e.employer_id
            WHERE j.title LIKE :search OR j.description LIKE :search
            ORDER BY j.created_at DESC
        ");
        $stmt->execute(['search' => '%' . $search . '%']);
    } else {
        // If no search, fetch all jobs
        $stmt = $pdo->query("
            SELECT j.job_id, j.title, j.description, e.company_name 
            FROM Jobs j
            JOIN Employers e ON j.employer_id = e.employer_id
            ORDER BY j.created_at DESC
        ");
    }

    $jobs = $stmt->fetchAll();

    // Fetch required skills for each job
    foreach ($jobs as &$job) {
        $skill_stmt = $pdo->prepare("
            SELECT s.skill_name 
            FROM Job_Skills js
            JOIN Skills s ON js.skill_id = s.skill_id
            WHERE js.job_id = :job_id
        ");
        $skill_stmt->execute(['job_id' => $job['job_id']]);
        $job['skills'] = $skill_stmt->fetchAll(PDO::FETCH_COLUMN); // Returns only skill_name array
        
        // Sample match score (In a real system, this is calculated based on the logged-in candidate's skills)
        // Providing a random or fixed score for visual purposes for now.
        $job['match_score'] = rand(70, 100); 
    }

    // Output as JSON
    echo json_encode($jobs);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'A database error occurred.']);
}
?>
