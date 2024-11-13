<?= $this->extend('themes/modern/templates/portal-delivery') ?>
<?= $this->section('content') ?>


<div class="page-content" data-bs-theme="dark">
 
<?php include( 'components/table-all.html' ); ?>
<?php include( 'components/calendar.html' ); ?>

</div><!-- .row -->

<?php include( 'components/create.html' ); ?>
<?php include( 'components/edit.html' ); ?>
<?php include( 'components/notifications/delete.html' ); ?>
<?php include( 'components/po-items.html' ); ?>


<?= $this->endSection(); ?>