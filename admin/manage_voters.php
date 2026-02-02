<?php
include '../auth_check.php';
require_role('admin');
include '../db.php';
include '../csrf.php';

// Create voter admin only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!verify_csrf_token($csrf)) {
    die('Invalid CSRF token.');
  }

  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $district = trim($_POST['district']);
  $citizenship_no = trim($_POST['citizenship_no']);
  $biometric_id = trim($_POST['biometric_id']);
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

  $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, citizenship_no, district, biometric_id) VALUES (?,?,?,?,?,?,?)");
  $role = 'voter';
  $stmt->bind_param("sssssss", $name, $email, $password, $role, $citizenship_no, $district, $biometric_id);
  $stmt->execute();
}

// Delete voter (require CSRF token)
if (isset($_GET['delete'])) {
  $del_id = (int)$_GET['delete'];
  $csrf = $_GET['csrf'] ?? '';
  if (!verify_csrf_token($csrf)) {
    die('Invalid CSRF token.');
  }
  $stmt = $conn->prepare("DELETE FROM users WHERE user_id=? AND role='voter'");
  $stmt->bind_param("i", $del_id);
  $stmt->execute();
}

// Search handling (by name, email, district, citizenship_no, biometric_id)
$search_q = trim($_GET['q'] ?? '');
if ($search_q !== '') {
  $like = "%" . $search_q . "%";
  $stmt = $conn->prepare("SELECT * FROM users WHERE role='voter' AND (name LIKE ? OR email LIKE ? OR district LIKE ? OR citizenship_no LIKE ? OR biometric_id LIKE ?) ORDER BY user_id DESC");
  $stmt->bind_param("sssss", $like, $like, $like, $like, $like);
  $stmt->execute();
  $voters = $stmt->get_result();
} else {
  $voters = $conn->query("SELECT * FROM users WHERE role='voter' ORDER BY user_id DESC");
}
?>
<!DOCTYPE html> 
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Voters</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
  <h2>Manage Voters</h2>

  <h3>Add Voter</h3>
  <form method="POST">    <?php echo csrf_input_field(); ?>    <input type="text" name="name" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Temp Password" required>
    <input type="text" name="citizenship_no" placeholder="Citizenship No" required>
    <select name="district" required>
      <option value="Kailali">Kailali</option>
      <option value="Kanchanpur">Kanchanpur</option>
      <option value="Dadeldhura">Dadeldhura</option>
      <option value="Baitadi">Baitadi</option>
      <option value="Darchula">Darchula</option>
      <option value="Bajhang">Bajhang</option>
      <option value="Bajura">Bajura</option>
      <option value="Achham">Achham</option>
      <option value="Doti">Doti</option>
    </select>
    <input type="text" name="biometric_id" placeholder="BIOHASHxxxxx">
    <button type="submit" name="create">Add Voter</button>
  </form>

  <h3>Existing Voters</h3>

  <form method="GET" style="margin-bottom:10px;">
    <input type="text" name="q" placeholder="Search by name, email, district, citizenship, biometric..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    <button type="submit">Search</button>
    <?php if (!empty($_GET['q'])): ?><a href="manage_voters.php" style="margin-left:10px;">Clear</a><?php endif; ?>
  </form>

  <table border="1" cellpadding="6">
    <thead>
      <tr><th>ID</th><th>Name</th><th>Email</th><th>District</th><th>Citizenship</th><th>Biometric</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php while ($v = $voters->fetch_assoc()): ?> 
      <tr>
        <td><?php echo $v['user_id']; ?></td>
        <td><?php echo htmlspecialchars($v['name']); ?></td>
        <td><?php echo htmlspecialchars($v['email']); ?></td>
        <td><?php echo htmlspecialchars($v['district']); ?></td>
        <td><?php echo htmlspecialchars($v['citizenship_no']); ?></td>
        <td><?php echo htmlspecialchars($v['biometric_id']); ?></td>
        <td><a href="?delete=<?php echo $v['user_id']; ?>&csrf=<?php echo urlencode(generate_csrf_token()); ?>" onclick="return confirm('Delete voter?')">Delete</a></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <p><a href="admin_dashboard.php">Back</a></p>

  
</body>
</html>
