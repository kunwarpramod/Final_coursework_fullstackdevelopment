<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nepal | Online Voting System</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>


<div style="display: flex; align-items: center; justify-content: center; gap: 15px;">
<div class="header-banner">
  <img src="https://static.vecteezy.com/system/resources/previews/020/896/284/original/nepal-flag-with-mountains-and-sunset-in-the-background-vector.jpg" alt="Nepal Voting Portal">
  <div class="banner-overlay">
    <h1>Your Vote, Your Voice, Our Nation's Future</h1>
  </div>
</div>

</div>


  <h1>Online Voting System</h1>

  <p style="text-align: center; padding: 40px;">
    <?php if (!isset($_SESSION['user_id'])): ?>
      
      <span style="display: block; margin-bottom: 20px; font-size: 1.1rem; color: #64748b;">
        Welcome to 2082 Nepal digital ballot. Please identify yourself to continue.
      </span>

      <a href="login.php" style="min-width: 120px;">Login</a>
      <a href="register.php" style="min-width: 120px; background: #64748b;">Register</a>

    <?php else: ?>

      <span style="display: block; margin-bottom: 20px; font-size: 1.2rem;">
        Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name'] ?? "User"); ?></strong>!
      </span>


      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin/admin_dashboard.php">Go to Admin Dashboard</a>
      <?php else: ?>
        <a href="voter/voter_dashboard.php">Go to Voter Dashboard</a>
      <?php endif; ?>

      <br><br>
      <a href="logout.php" class="glass-btn">Logout</a>

    <?php endif; ?>
  </p>

  <p style="text-align: center; font-size: 0.8rem; color: #94a3b8; border: none; background: transparent; box-shadow: none;">
    &copy; <?php ?> 2082 Nepal Online Voting Portal. All rights reserved.
  </p>

</body>
</html>