<?php

function showSuccessMessage($message) {
    return '<div class="alert alert-success">
            <strong>Great!</strong> '.$message.'
            </div>';
}

function showDangerMessage($message) {
    return '<div class="alert alert-danger">
            <strong>Oops!</strong> '.$message.'
            </div>';
}