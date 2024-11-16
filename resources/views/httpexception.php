<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error Page</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background-color: #f8f9fa;
    }

    .error-container {
      text-align: center;
    }

    .status-code {
      font-size: 5em;
      color: #dc3545;
      /* Bootstrap danger color */
    }

    .message {
      font-size: 1.5em;
      color: #6c757d;
      /* Bootstrap secondary color */
    }
  </style>
</head>

<body>
  <div class="error-container">
    <div class="status-code"><?= $status ?></div>
    <div class="message"><?= $message ?></div>
  </div>
</body>

</html>