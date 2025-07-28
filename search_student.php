<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$search_query = isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '';
$students = [];

if (!empty($search_query)) {
    // Search by name or email
    $sql = "SELECT id, name, email, dob, course, grade, profile_pic FROM students WHERE name LIKE ? OR email LIKE ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }
    $param = "%" . $search_query . "%";
    $stmt->bind_param("ss", $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Student - Student Management System</title>
    <link rel="stylesheet" href="modern-styles.css">
    <style>
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .page-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }
        
        .search-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
            margin: 2rem auto;
            max-width: 800px;
            box-shadow: var(--shadow-medium);
            animation: slideIn 0.8s ease;
        }
        
        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: end;
        }
        
        .search-input-group {
            flex: 1;
        }
        
        .search-input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: white;
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .search-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(240, 147, 251, 0.1);
            transform: translateY(-2px);
        }
        
        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .btn-search {
            padding: 1rem 2rem;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .btn-search::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-secondary);
            transition: left 0.3s ease;
            z-index: -1;
        }
        
        .btn-search:hover::before {
            left: 0;
        }
        
        .btn-search:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-heavy);
        }
        
        .search-results {
            margin-top: 2rem;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .results-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .results-count {
            background: var(--gradient-secondary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: white;
        }
        
        .no-results p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .student-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1.5rem;
            align-items: center;
        }
        
        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }
        
        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--glass-border);
        }
        
        .student-info h3 {
            color: white;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .student-info p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        
        .student-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-view {
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
                gap: 1rem;
            }
            
            .student-card {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .student-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="add_student.php"><i class="fas fa-user-plus"></i> Add Student</a>
        <a href="view_students.php"><i class="fas fa-users"></i> View Students</a>
        <a href="search_student.php"><i class="fas fa-search"></i> Search Student</a>
        <a href="logs.php"><i class="fas fa-list"></i> View Logs</a>
        <a href="index.php?logout=true" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="glass-container">
        <div class="page-header">
            <h1><i class="fas fa-search"></i> Search Students</h1>
            <p>Find specific student information quickly and easily</p>
        </div>
        
        <div class="search-container">
            <form action="search_student.php" method="get" class="search-form">
                <div class="search-input-group">
                    <label for="query">
                        <i class="fas fa-search"></i> Search by Name or Email
                    </label>
                    <input type="search" id="query" name="query" value="<?php echo $search_query; ?>" 
                           placeholder="Enter student name or email address" class="search-input">
                </div>
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
            
            <?php if (!empty($search_query)): ?>
                <div class="search-results">
                    <div class="results-header">
                        <h3 class="results-title">
                            <i class="fas fa-list"></i> Search Results
                        </h3>
                        <span class="results-count">
                            <?php echo count($students); ?> result<?php echo count($students) !== 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    
                    <?php if (empty($students)): ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No Results Found</h3>
                            <p>No students found matching "<?php echo $search_query; ?>"</p>
                            <p>Try searching with different keywords or check the spelling.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <div class="student-card">
                                <div class="student-avatar">
                                    <?php if ($student['profile_pic']): ?>
                                        <img src="<?php echo htmlspecialchars($student['profile_pic']); ?>" 
                                             alt="Profile Picture" class="student-avatar">
                                    <?php else: ?>
                                        <div class="glass-icon" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="student-info">
                                    <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student['email']); ?></p>
                                    <p><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($student['course'] ?: 'N/A'); ?></p>
                                    <p><i class="fas fa-star"></i> <?php echo htmlspecialchars($student['grade'] ?: 'N/A'); ?></p>
                                    <?php if ($student['dob']): ?>
                                        <p><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($student['dob'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="student-actions">
                                    <a href="update_student.php?id=<?php echo $student['id']; ?>" class="btn-view">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
