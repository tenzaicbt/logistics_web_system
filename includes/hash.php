<?php
// Secure password hashing utility function

function hash_password($plain_password) {
    return password_hash($plain_password, PASSWORD_DEFAULT);
}
