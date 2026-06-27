<?php
if (isset($_POST['email_or_mobile'])) {
    $authorization->login();
}
$logoUrl = URL . '/images/' . PROJECT . '/logo.png';