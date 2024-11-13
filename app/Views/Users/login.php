<?php // Create a login page layout with a full-width background image, the GSL logo and a microsoft login button ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSL Portal Login</title>
    <link rel="stylesheet" href="/themes/modern/login/index.css">
</head>
<body>
    <div class="login-page">
        <div class="login-box">
            <img class="login-logo" src="/themes/modern/login/login-logo-full.png" alt="GSL Logo">
                 <div class="icon-btn">
                <a class="d-flex login-link center align-items-center wrap-row" href="<?=base_url('msauth')?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-microsoft" viewBox="0 0 20 20"><path d="M7.462 0H0v7.19h7.462V0zM16 0H8.538v7.19H16V0zM7.462 8.211H0V16h7.462V8.211zm8.538 0H8.538V16H16V8.211z"/></svg>   
                <span> LOGIN</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>