<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>404 · Ocean HRMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700&display=swap" rel="stylesheet">
  <style>
    body{font-family:Outfit,sans-serif;background:#f3f4f6;display:grid;place-items:center;min-height:100vh;margin:0;color:#211F23}
    .box{background:#fff;padding:40px;border-radius:20px;text-align:center;box-shadow:0 20px 40px rgba(0,0,0,.06)}
    h1{color:#F5A01B;font-size:64px;margin:0}
    a{color:#F5A01B;font-weight:700;text-decoration:none}
  </style>
</head>
<body>
  <div class="box">
    <h1>404</h1>
    <p>Page not found</p>
    <a href="<?= \App\Core\View::url('dashboard') ?>">Back to Dashboard</a>
  </div>
</body>
</html>
