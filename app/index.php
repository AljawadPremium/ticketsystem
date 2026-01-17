<?php
require 'lang.php';
$isRtl = $lang === 'ar';
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $t['title'] ?></title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- App Icons -->
<link rel="icon" type="image/png" href="assets/android.png">

<!-- Android -->
<link rel="icon" sizes="192x192" href="assets/android.png">
<link rel="icon" sizes="512x512" href="assets/Androidhigh-res.png">

<!-- iPhone / iOS -->
<link rel="apple-touch-icon" href="assets/iPhone.png">

<!-- App Meta -->
<meta name="application-name" content="IT Support">
<meta name="apple-mobile-web-app-title" content="IT Support">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#111111">
<meta name="apple-mobile-web-app-status-bar-style" content="black">


</head>

<body class="<?= $isRtl ? 'rtl' : '' ?>">

<div class="ticket-form">

    <div class="lang-switch">
        <a href="?lang=en">EN</a> |
        <a href="?lang=ar">عربي</a>
    </div>

    <div class="logo">
        <img src="assets/logo.png" alt="Logo">
    </div>

    <h2><?= $t['title'] ?></h2>

    <form method="POST" action="submit.php">

        <input type="hidden" name="lang" value="<?= $lang ?>">

        <input type="text" name="name" placeholder="<?= $t['name'] ?>" required>
        <input type="text" name="phone" placeholder="<?= $t['phone'] ?>" required>

        <select name="location" required>
          <option value=""><?= $t['location'] ?></option>
          <?php foreach ($t['locations'] as $value => $label): ?>
              <option value="<?= $value ?>"><?= $label ?></option>
          <?php endforeach; ?>
        </select>
        <p><strong><?= $t['problem'] ?></strong></p>

        <div class="problem-grid">
        <?php foreach ($t['problems'] as $value => $label): ?>
            <label class="problem-tile">
                <input type="radio" name="problem_type" value="<?= $value ?>" required>
                <span><?= $label ?></span>
            </label>
        <?php endforeach; ?>
        </div>

<div id="otherWrap" style="display:none;">
    <textarea name="other_problem" id="other_problem" placeholder="<?= $t['other'] ?>"></textarea>
</div>


        <button type="submit"><?= $t['submit'] ?></button>

    </form>
</div>

<script>
  function toggleOtherField() {
    const selected = document.querySelector('input[name="problem_type"]:checked');
    const wrap = document.getElementById('otherWrap');
    const txt  = document.getElementById('other_problem');

    if (selected && selected.value === 'Other') {
      wrap.style.display = 'block';
      txt.required = true;
      txt.focus();
    } else {
      wrap.style.display = 'none';
      txt.required = false;
      txt.value = '';
    }
  }

  document.querySelectorAll('input[name="problem_type"]').forEach(r => {
    r.addEventListener('change', toggleOtherField);
  });
</script>

</body>
</html>
