<?php
include 'db_connect.php';
$result = $conn->query("SELECT * FROM appointments ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Appointment Requests</title>
<style>
  body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
  table { width: 100%; border-collapse: collapse; background: white; }
  th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
  th { background-color: #4CAF50; color: white; }
  button { padding: 6px 12px; border: none; color: white; border-radius: 4px; cursor: pointer; margin: 2px; }
  .approve { background-color: #4CAF50; }
  .deny { background-color: #f44336; }
  iframe { border: 1px solid #ccc; width: 100%; height: 600px; margin-top: 30px; border-radius: 8px; }
</style>
</head>
<body>

<h2>🦷 Admin Appointment Requests</h2>

<table>
<tr>
  <th>Name</th>
  <th>Email</th>
  <th>Date</th>
  <th>Time</th>
  <th>Location</th>
  <th>Description</th>
  <th>Status</th>
  <th>Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()) { ?>
<tr>
  <td><?= htmlspecialchars($row['name']) ?></td>
  <td><?= htmlspecialchars($row['email']) ?></td>
  <td><?= htmlspecialchars($row['date']) ?></td>
  <td><?= htmlspecialchars($row['start_time']) ?></td>
  <td><?= htmlspecialchars($row['location']) ?></td>
  <td><?= htmlspecialchars($row['description']) ?></td>
  <td><?= htmlspecialchars($row['status']) ?></td>
  <td>
    <?php if ($row['status'] == 'pending') { ?>
      <form action="approve.php" method="POST" style="display:inline;">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <button type="submit" class="approve">Approve</button>
      </form>
      <form action="deny.php" method="POST" style="display:inline;">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <button type="submit" class="deny">Deny</button>
      </form>
    <?php } else { echo strtoupper($row['status']); } ?>
  </td>
</tr>
<?php } ?>
</table>

<h3>Google Calendar (Approved Appointments)</h3>
<iframe src="https://calendar.google.com/calendar/embed?src=allimagcawas%40gmail.com&ctz=Asia%2FManila"></iframe>

</body>
</html>
