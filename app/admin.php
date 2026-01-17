<?php
require 'db.php';

/* =======================
   Chart Data: Status
   ======================= */
$statusResult = $conn->query("
    SELECT status, COUNT(*) AS total
    FROM tickets
    GROUP BY status
");

$issuesCount = 0;
$fixedCount = 0;

while ($row = $statusResult->fetch_assoc()) {
  if ($row['status'] === 'issue')
    $issuesCount = (int) $row['total'];
  if ($row['status'] === 'fixed')
    $fixedCount = (int) $row['total'];
}

/* =======================
   Chart Data: Fixed by Type
   ======================= */
$typeResult = $conn->query("
    SELECT problem_type, COUNT(*) AS total
    FROM tickets
    WHERE status = 'fixed'
    GROUP BY problem_type
    ORDER BY total DESC
");

$problemLabels = [];
$problemCounts = [];

while ($row = $typeResult->fetch_assoc()) {
  $problemLabels[] = $row['problem_type'];
  $problemCounts[] = (int) $row['total'];
}

/* =======================
   Chart Data: Tickets by Branch
   ======================= */
$branchResult = $conn->query("
    SELECT location, COUNT(*) AS total
    FROM tickets
    GROUP BY location
    ORDER BY total DESC
");

$branchLabels = [];
$branchCounts = [];

while ($row = $branchResult->fetch_assoc()) {
  $branchLabels[] = $row['location'];
  $branchCounts[] = (int) $row['total'];
}

/* Helper: sanitize phone for WhatsApp */
function wa_phone($phone)
{
  return preg_replace('/\D/', '', $phone);
}

/* =======================
   Chart Data: Trend (Tickets Over Time)
   ======================= */
$trendResult = $conn->query("
    SELECT DATE(created_at) AS day, COUNT(*) AS total
    FROM tickets
    GROUP BY DATE(created_at)
    ORDER BY day ASC
    LIMIT 14
");

$trendLabels = [];
$trendCounts = [];

while ($row = $trendResult->fetch_assoc()) {
  $trendLabels[] = $row['day'];
  $trendCounts[] = (int) $row['total'];
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

  <h2>IT Tickets Dashboard</h2>

  <!-- =======================
     Charts Section
     ======================= -->
  <div class="charts">

    <div class="chart-box">
      <h3>Issues vs Fixed</h3>
      <div class="chart-wrap">
        <canvas id="statusChart"></canvas>
      </div>
      <div class="chart-stats">
        <span>Issues: <strong><?= $issuesCount ?></strong></span>
        <span>Fixed: <strong><?= $fixedCount ?></strong></span>
      </div>
    </div>

    <div class="chart-box">
      <h3>Fixed Issues by Type</h3>
      <div class="chart-wrap">
        <canvas id="typeChart"></canvas>
      </div>
      <?php if (count($problemLabels) === 0): ?>
        <p class="muted">No fixed tickets yet.</p>
      <?php endif; ?>
    </div>

    <div class="chart-box wide">
      <h3>Tickets by Branch</h3>
      <div class="chart-wrap">
        <canvas id="branchChart"></canvas>
      </div>
      <?php if (count($branchLabels) === 0): ?>
        <p class="muted">No tickets yet.</p>
      <?php endif; ?>
    </div>

    <!-- NEW: Tickets Over Time (Trend) -->
    <div class="chart-box wide">
      <h3>🕒 Tickets Over Time (Trend)</h3>
      <div class="chart-wrap">
        <canvas id="trendChart"></canvas>
      </div>
      <?php if (count($trendLabels) === 0): ?>
        <p class="muted">No trend data yet.</p>
      <?php endif; ?>
    </div>

  </div>

  <!-- =======================
     Board Section
     ======================= -->
  <div class="board">

    <!-- ISSUES -->
    <div class="column">
      <h3>Issues</h3>

      <?php
      $issues = $conn->query("SELECT * FROM tickets WHERE status='issue' ORDER BY created_at DESC");
      while ($row = $issues->fetch_assoc()):
        $wa = wa_phone($row['phone']);
        ?>
        <div class="card">
          <strong><?= htmlspecialchars($row['name']) ?></strong><br>

          <span class="muted">
            WhatsApp:
            <a href="https://web.whatsapp.com/send?phone=<?= $wa ?>" target="_blank">
              <?= htmlspecialchars($row['phone']) ?>
            </a>
          </span><br>

          <?= htmlspecialchars($row['location']) ?><br>
          <?= htmlspecialchars($row['problem_type']) ?><br>

          <?php if (!empty($row['other_problem'])): ?>
            <span class="muted"><?= nl2br(htmlspecialchars($row['other_problem'])) ?></span><br>
          <?php endif; ?>

          <br>
          <a href="fix.php?id=<?= (int) $row['id'] ?>" class="btn">Mark as Fixed</a>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- FIXED -->
    <div class="column">
      <h3>Fixed</h3>

      <?php
      $fixed = $conn->query("SELECT * FROM tickets WHERE status='fixed' ORDER BY created_at DESC");
      while ($row = $fixed->fetch_assoc()):
        $wa = wa_phone($row['phone']);
        ?>
        <div class="card fixed">
          <strong><?= htmlspecialchars($row['name']) ?></strong><br>

          <span class="muted">
            WhatsApp:
            <a href="https://web.whatsapp.com/send?phone=<?= $wa ?>" target="_blank">
              <?= htmlspecialchars($row['phone']) ?>
            </a>
          </span><br>

          <?= htmlspecialchars($row['location']) ?><br>
          <?= htmlspecialchars($row['problem_type']) ?>
        </div>
      <?php endwhile; ?>
    </div>

  </div>

  <script>
    /* ===== Chart 1: Issues vs Fixed ===== */
    new Chart(document.getElementById('statusChart'), {
      type: 'doughnut',
      data: {
        labels: ['Issues', 'Fixed'],
        datasets: [{
          data: [<?= $issuesCount ?>, <?= $fixedCount ?>],
          backgroundColor: ['#c0392b', '#2ecc71'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: '#f5f5f5' }
          }
        }
      }
    });

    /* ===== Chart 2: Fixed by Type ===== */
    new Chart(document.getElementById('typeChart'), {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($problemLabels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
          data: <?= json_encode($problemCounts, JSON_UNESCAPED_UNICODE) ?>,
          borderWidth: 0,
          backgroundColor: [
            '#c9a24d', '#3498db', '#9b59b6', '#e67e22',
            '#1abc9c', '#95a5a6', '#e74c3c', '#2ecc71'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: '#f5f5f5' }
          }
        }
      }
    });

    /* ===== Chart 3: Tickets by Branch ===== */
    new Chart(document.getElementById('branchChart'), {
      type: 'bar',
      data: {
        labels: <?= json_encode($branchLabels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
          label: 'Tickets',
          data: <?= json_encode($branchCounts, JSON_UNESCAPED_UNICODE) ?>,
          backgroundColor: '#3498db',
          borderRadius: 6
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            ticks: { stepSize: 5, color: '#f5f5f5', precision: 0 },
            grid: { color: 'rgba(255,255,255,0.1)' }
          },
          y: {
            ticks: { color: '#f5f5f5' },
            grid: { display: false }
          }
        }
      }
    });

    /* ===== Chart 4: Tickets Over Time ===== */
    new Chart(document.getElementById('trendChart'), {
      type: 'line',
      data: {
        labels: <?= json_encode($trendLabels) ?>,
        datasets: [{
          label: 'Tickets',
          data: <?= json_encode($trendCounts) ?>,
          borderColor: '#3498db',
          backgroundColor: 'rgba(52, 152, 219, 0.2)',
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          pointRadius: 4,
          pointBackgroundColor: '#3498db'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            ticks: { color: '#f5f5f5' },
            grid: { color: 'rgba(255,255,255,0.05)' }
          },
          y: {
            beginAtZero: true,
            ticks: { stepSize: 5, color: '#f5f5f5', precision: 0 },
            grid: { color: 'rgba(255,255,255,0.1)' }
          }
        }
      }
    });
  </script>

</body>

</html>