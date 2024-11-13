<?= $this->extend('themes/modern/templates/portal-receiving') ?>
<?= $this->section('content') ?>

<div class="page-content" data-bs-theme="dark">
 
<?php include( 'components/table-all.html' ); ?>
<?php include( 'components/calendar.html' ); ?>

</div><!-- .row -->

<?php include( 'components/po-items.html' ); ?>
<?php include( 'components/create.html' ); ?>

<?php include( 'components/notifications/delete-sku.html' ); ?>

<?php include( 'components/process-units.html' ); ?>
<?php include( 'components/notifications/delete-unit.html' ); ?>
<?php include( 'components/notifications/duplicate-unit.html' ); ?>
<?= $this->endSection(); ?>