<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>GSL Portal</title>
</head>
<body>
    <div class="d-lg-flex half">
        <div class="bg order-1 order-md-2" style="background-image: url('/vendor/users/login/login-image.jpg');"></div>
        <div class="contents order-2 order-md-1 col-md-7 align-content-center center">
            <img class="mb-5 login-logo"src="/vendor/users/login/logo.png" alt="" width="250" height="250"/>
            <div class="icon-btn">
                <a class="d-flex login-link center align-items-center wrap-row" href="<?=base_url('msauth')?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-microsoft" viewBox="0 0 20 20"><path d="M7.462 0H0v7.19h7.462V0zM16 0H8.538v7.19H16V0zM7.462 8.211H0V16h7.462V8.211zm8.538 0H8.538V16H16V8.211z"/></svg>   
                <span>Microsoft Login</span>
                </a>
            </div>
        </div>
    </div>

</body>
<style>
/* Bootstrap Styles */

*, ::after, ::before {
    box-sizing: border-box;
}

html {
    display: block;
}
:root {
    /* Breakpoints */
    --bs-breakpoint-xs: 0;
    --bs-breakpoint-sm: 576px;
    --bs-breakpoint-md: 768px;
    --bs-breakpoint-lg: 992px;
    --bs-breakpoint-xl: 1200px;
    --bs-breakpoint-xxl: 1400px;
    
    /* Fonts */
   --bs-body-font-family: 'Arial';
   --bs-body-font-size: 16px;
   --bs-body-font-weight: 400;
   --bs-body-line-height: 1.5em;
   --bs-body-color: white;
   --bs-body-text-align: left;
   
   /* Colors*/
   --bs-body-bg: black;
   --bs-heading-color: white;
}

body {
    font-family: var(--bs-body-font-family);
    font-size: var(--bs-body-font-size);
    font-weight: var(--bs-body-font-weight);
    line-height: var(--bs-body-line-height);
    color: var(--bs-body-color);
    text-align: var(--bs-body-text-align);
    background-color: var(--bs-body-bg);
    -webkit-text-size-adjust: 100%;
    -webkit-tap-highlight-color: transparent;
    margin: 5%;
}

.d-flex {
    display: flex;
}
.align-items-center {
    align-items: center;
}
.align-content-center {
    align-content: center;
}
.center {
    text-align: center;
}

.justify-content-center {
    justify-content: center;
}

.mb-5 {
    margin-bottom: 3rem;
}
.col-7 {
        width: 58.33333333%;
}

.half .bg {
    background-size: cover;
    background-position: center;
}

.half .contents, .half .bg {
    width: 50%;
}

.order-2 {
    order: 2;
}

img[Attributes Style] {
    width: 250px;
    aspect-ratio: auto 250 / 100;
    height: 100px;
}
h1 {
    font-size: calc(1.375rem + 1.5vw);
    @media (min-width: 1200px) {
    h1 {
        font-size: 2.5rem;
    }
}
}
h1, h2, h3{
    margin-top: 0;
    margin-bottom: .5rem;
    font-weight: 500;
    line-height: 1.2;
    color: var(--bs-heading-color, inherit);
}



@media (min-width: 992px) {
    .d-lg-flex {
        display: flex;
    }
}

@media (min-width: 768px) {
    .order-md-2 {
        order: 2;
    }
}

@media (min-width: 768px) {
    .order-md-1 {
        order: 1;
    }
}


@media (min-width: 768px) {
    .col-md-7 {
        flex: 0 0 auto;
        width: 58.33333333%;
    }
}

</style>
<style>

a.login-link  {
    text-decoration: none;
    background-color: green;
    padding: 1em;
    border-radius: 1%;
    color: white;
    width: 170px;
    margin: auto;
}
a.login-link span {
    margin-left: 5px;
}


.half, .half .container > .row {
  min-height: 700px; }

@media (max-width: 992px) {
    .half {
        min-height: 100vh;
    }
  .half .bg {
    height: 30vh; } 
    
.half .contents {
  height: 40vh;
     
}}

.half .contents {
  background: #0f0c0c; 
     
}

.half .contents, .half .bg {
  width: 50%; }
  @media (max-width: 992px) {
    .half .contents, .half .bg {
      width: 100%; } } 


 
</style>

</html>

