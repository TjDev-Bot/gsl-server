<!doctype html>
<html lang="en-US">
<?= $this->include('App\Views\themes\modern\template-parts\head-crud') ?>
<body>
<?= $this->include('App\Views\themes\modern\template-parts\header') ?>
<?= $this->include('App\Views\themes\modern\template-parts\page-heading') ?>
<?= $this->renderSection('content') ?>

<?= $this->include('App\Views\themes\modern\template-parts\footer.php') ?>
</body>
</html>