<?php
include 'db_connect.php';
$result = $conn->query("SELECT * FROM appointments ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Appointment Requests</title>

<!-- ✅ DataTables & SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">

<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    margin: 40px;
    background: #f9f9f9;
  }
  h2 {
    color: #333;
    margin-bottom: 20px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }
  th {
    background-color: #4CAF50;
    color: white;
  }
  th, td {
    padding: 12px;
    text-align: center;
  }
  button {
    padding: 6px 12px;
    border: none;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    margin: 2px;
  }
  .approve {
    background-color: #4CAF50;
  }
  .deny {
    background-color: #f44336;
  }
</style>
</head>
<body>

<h2>🦷 Admin Appointment Requests</h2>

<table id="appointmentsTable">
  <thead>
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
  </thead>
  <tbody>
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
          <button class="approve" data-id="<?= $row['id'] ?>">Approve</button>
          <button class="deny" data-id="<?= $row['id'] ?>">Deny</button>
        <?php } else { echo strtoupper($row['status']); } ?>
      </td>
    </tr>
  <?php } ?>
  </tbody>
</table>

<!-- ✅ jQuery, DataTables, and SweetAlert2 JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
  // ✅ Initialize DataTable
  $('#appointmentsTable').DataTable({
    responsive: true,
    pageLength: 10,
    order: [[0, 'asc']]
  });

  // ✅ SweetAlert2 for Approve
  $('.approve').click(function() {
    const id = $(this).data('id');
    Swal.fire({
      title: 'Approve Appointment?',
      text: 'Do you want to approve this appointment?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#4CAF50',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, approve it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('approve.php', { id: id }, function() {
          Swal.fire('Approved!', 'The appointment has been approved.', 'success')
            .then(() => location.reload());
        });
      }
    });
  });

  // ✅ SweetAlert2 for Deny (with reason)
  $('.deny').click(async function() {
    const id = $(this).data('id');

    const { value: reasonChoice } = await Swal.fire({
      title: 'Deny Appointment?',
      text: 'Select a reason for denying this appointment:',
      input: 'select',
      inputOptions: {
        'Automated Messages': {
          'conflict': 'Conflict with another appointment schedule',
          'policy': 'Does not comply with clinic policies',
          'info': 'Incomplete or invalid appointment information',
          'late': 'Requested too late or outside clinic hours',
          'other': 'Other (enter manually)'
        }
      },
      inputPlaceholder: 'Select a reason',
      showCancelButton: true,
      confirmButtonText: 'Continue',
      confirmButtonColor: '#f44336'
    });

    if (!reasonChoice) return;

    let reasonText = '';

    if (reasonChoice === 'other') {
      const { value: customReason } = await Swal.fire({
        title: 'Custom Reason',
        input: 'text',
        inputPlaceholder: 'Enter your reason...',
        showCancelButton: true
      });
      if (!customReason) return;
      reasonText = customReason;
    } else {
      reasonText = {
        'conflict': 'Conflict with another appointment schedule.',
        'policy': 'Does not comply with clinic policies.',
        'info': 'Incomplete or invalid appointment information.',
        'late': 'Requested too late or outside clinic hours.'
      }[reasonChoice];
    }

    // ✅ Final confirmation
    Swal.fire({
      title: 'Confirm Denial',
      html: `<p>Reason:</p><strong>${reasonText}</strong>`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#f44336',
      confirmButtonText: 'Yes, deny it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('deny.php', { id: id, reason: reasonText }, function() {
          Swal.fire('Denied!', 'The appointment has been denied.', 'success')
            .then(() => location.reload());
        });
      }
    });
  });
});
</script>

</body>
</html>
