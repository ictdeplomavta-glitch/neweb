<?php
// 1. Database Configuration (Usually kept in a separate secure file)
$host = '127.0.0.1';
$db   = 'library_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// 2. Connect to the MySQL Database using PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch as arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Security: Disable emulation
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Fulfilling NFR-SC-03: Handle errors gracefully without exposing DB details
    die("Database connection failed. Please try again later."); 
}

// 3. Get the search term from the user (e.g., from a URL parameter ?search=Harry)
$searchTerm = $_GET['search'] ?? '';

// 4. Prepare and Execute the SQL Query SECURELY
// We use '?' as a placeholder to prevent SQL Injection (Fulfills NFR-SC-03)
$sql = "SELECT title, author, total_copies, available_copies 
        FROM books 
        WHERE title LIKE ? OR author LIKE ?";
        
$stmt = $pdo->prepare($sql);
$wildcardSearch = "%{$searchTerm}%";
$stmt->execute([$wildcardSearch, $wildcardSearch]);

// 5. Fetch the results
$books = $stmt->fetchAll();
?>

<!-- 6. HTML Output (Fulfills UI-01 & UI-02) -->
<!DOCTYPE html>
<html>
<head>
    <title>Library OPAC - Search Results</title>
</head>
<body>
    <h2>Search Results for: "<?php echo htmlspecialchars($searchTerm); ?>"</h2>
    
    <table border="1" cellpadding="10">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Availability Status</th>
        </tr>
        <?php if ($books): ?>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                    <td>
                        <?php 
                        // Fulfilling FR-OP-02: Display real-time availability
                        if ($book['available_copies'] > 0) {
                            echo "<span style='color:green;'>{$book['available_copies']} of {$book['total_copies']} available</span>";
                        } else {
                            echo "<span style='color:red;'>Currently Unavailable (0 of {$book['total_copies']})</span>";
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3">No books found matching your search.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>