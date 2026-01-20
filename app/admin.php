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

/* =======================
   PDF Asset: Base64 Background
   ======================= */
$bgPath = 'assets/PDFbackground.jpeg';
$bgBase64 = '';
if (file_exists($bgPath)) {
  $bgData = file_get_contents($bgPath);
  $bgBase64 = 'data:image/jpeg;base64,' . base64_encode($bgData);
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
  <!-- html2pdf.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body>

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>IT Tickets Dashboard</h2>
    <div style="text-align: right;">
      <button onclick="downloadPDF()"
        style="background: #3498db; color: white; padding: 8px 12px; width: auto; font-size: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Download
        Report (PDF)</button>
    </div>
  </div>

  <!-- HIDDEN PDF TEMPLATE -->
  <div id="pdf-template"
    style="display: none; width: 8.5in; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #000; background: white !important;">

    <!-- PDF PAGE 1 -->
    <div class="pdf-page" style="position: relative !important; width: 100%; min-height: 11in; padding: 60px 50px; box-sizing: border-box; page-break-after: always; background: transparent !important;">
      <!-- Background Image for Page 1 -->
      <img src="<?= $bgBase64 ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; object-fit: cover; display: block;">

      <h1 style="text-align: center; color: #1a252f; border-bottom: 3px solid #3498db; padding-bottom: 15px; margin-top: 0; background: transparent !important;">
        IT Help Desk Performance Report</h1>
      <p style="text-align: right; color: #666;">Generated on: <?= date('Y-m-d H:i') ?></p>

      <div style="margin: 20px 0; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: transparent !important;">
        <div style="background: rgba(248, 249, 250, 0.85) !important; padding: 15px; border-radius: 8px; border-left: 4px solid #c0392b;">
          <h4 style="margin: 0; background: transparent !important;">Total Issues</h4>
          <h2 style="margin: 5px 0; background: transparent !important;"><?= $issuesCount ?></h2>
        </div>
        <div style="background: rgba(248, 249, 250, 0.85) !important; padding: 15px; border-radius: 8px; border-left: 4px solid #2ecc71;">
          <h4 style="margin: 0; background: transparent !important;">Total Fixed</h4>
          <h2 style="margin: 5px 0; background: transparent !important;"><?= $fixedCount ?></h2>
        </div>
      </div>

      <div style="margin-top: 30px; background: transparent !important;">
        <h3 style="background: transparent !important;">1. Issues vs Fixed Performance</h3>
        <p style="color: #333; line-height: 1.6; background: transparent !important;">This diagram shows the relationship between reported issues and resolved tickets. 
          Currently, there are <strong><?= $issuesCount ?></strong> active issues and <strong><?= $fixedCount ?></strong> fixed tickets. 
        </p>
        <div id="pdf-chart-1" style="width: 100%; height: 320px; text-align: center; margin-top: 10px;"></div>
      </div>

      <div style="margin-top: 30px; background: transparent !important;">
        <h3 style="background: transparent !important;">3. Tickets by Branch</h3>
        <p style="color: #333; line-height: 1.6; background: transparent !important;">Distribution of tickets across <strong><?= count($branchLabels) ?></strong> different locations. 
          This analysis is crucial for understanding regional IT demand and optimizing resource allocation.
        </p>
        <div id="pdf-chart-3" style="width: 100%; height: 350px; text-align: center; margin-top: 10px;"></div>
      </div>
    </div>

    <!-- PDF PAGE 2 -->
    <div class="pdf-page" style="position: relative !important; width: 100%; min-height: 11in; padding: 60px 50px; box-sizing: border-box; background: transparent !important;">
      <!-- Background Image for Page 2 -->
      <img src="<?= $bgBase64 ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; object-fit: cover; display: block;">

      <div style="margin-top: 0; background: transparent !important;">
        <h3 style="background: transparent !important;">2. Fixed Issues by Type</h3>
        <p style="color: #333; line-height: 1.6; background: transparent !important;">Detailed breakdown of the <strong><?= $fixedCount ?></strong> resolved problems by category. 
          This helps identify that <strong><?= count($problemLabels) ?></strong> different types of issues were successfully addressed.</p>
        <div id="pdf-chart-2" style="width: 100%; height: 320px; text-align: center; margin-top: 10px;"></div>
      </div>

      <div style="margin-top: 40px; background: transparent !important;">
        <h3 style="background: transparent !important;">4. Tickets Over Time (Trend)</h3>
        <p style="color: #333; line-height: 1.6; background: transparent !important;">Visualizes the volume of tickets over the last 14 days. 
          With a daily average of <strong><?= count($trendCounts) > 0 ? round(array_sum($trendCounts) / count($trendCounts), 1) : 0 ?></strong> tickets.
        </p>
        <div id="pdf-chart-4" style="width: 100%; height: 320px; text-align: center; margin-top: 10px;"></div>
      </div>

      <div
        style="margin-top: 80px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 12px; color: #999; text-align: center;">
        End of IT Help Desk Report - Confidential IT Data
      </div>
    </div>
  </div>

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
            labels: { color: '#f5f5f5', font: { weight: 'bold' } }
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

    /* ===== PDF Export Logic ===== */
    async function downloadPDF() {
      const { jsPDF } = window;
      const template = document.getElementById('pdf-template');

      // Update Chart colors to Black for PDF capture
      const chartIds = ['statusChart', 'typeChart', 'branchChart', 'trendChart'];
      const chartInstances = chartIds.map(id => Chart.getChart(id));

      // 1. Switch to Black
      chartInstances.forEach(chart => {
        if (!chart) return;
        if (chart.options.plugins.legend) chart.options.plugins.legend.labels.color = '#000000';
        if (chart.options.scales && chart.options.scales.x) chart.options.scales.x.ticks.color = '#000000';
        if (chart.options.scales && chart.options.scales.y) chart.options.scales.y.ticks.color = '#000000';
        chart.update('none');
      });

      // Show template temporarily for capturing
      template.style.display = 'block';

      // 2. WAIT FOR IMAGES TO LOAD (Backgrounds from Base64 or URL)
      const images = template.getElementsByTagName('img');
      const loadPromises = Array.from(images).map(img => {
        if (img.complete) return Promise.resolve();
        return new Promise(resolve => { img.onload = resolve; img.onerror = resolve; });
      });
      await Promise.all(loadPromises);

      // Capture charts as images and put them in the template
      for (let i = 0; i < chartIds.length; i++) {
        const canvas = document.getElementById(chartIds[i]);
        const imgData = canvas.toDataURL('image/png', 1.0);
        const container = document.getElementById(`pdf-chart-${i + 1}`);
        if (container) container.innerHTML = `<img src="${imgData}" style="max-width: 100%; height: auto;">`;
      }

      const opt = {
        margin: 0,
        filename: 'IT_Help_Disk_Report.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
          scale: 2,
          useCORS: true,
          letterRendering: true,
          logging: true,
          backgroundColor: null
        },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
      };

      // Generate PDF
      try {
        await html2pdf().set(opt).from(template).save();
      } finally {
        // Hide template again
        template.style.display = 'none';

        // 3. Switch back to White for Dashboard
        chartInstances.forEach(chart => {
          if (!chart) return;
          if (chart.options.plugins.legend) chart.options.plugins.legend.labels.color = '#f5f5f5';
          if (chart.options.scales && chart.options.scales.x) chart.options.scales.x.ticks.color = '#f5f5f5';
          if (chart.options.scales && chart.options.scales.y) chart.options.scales.y.ticks.color = '#f5f5f5';
          chart.update('none');
        });

        // Clear images
        for (let i = 1; i <= 4; i++) {
          const container = document.getElementById(`pdf-chart-${i}`);
          if (container) container.innerHTML = '';
        }
      }
    }
  </script>

</body>

</html>