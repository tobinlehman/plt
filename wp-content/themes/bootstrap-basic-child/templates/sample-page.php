<?php
/* Template Name: Sample page */
// header("Content-Type: application/json;charset=utf-8");

// $all_users = get_user_by('email', 'rbeadel@arkforests.org');

// print_r($all_users->ID);

// $all_users = get_users();
// foreach ( $all_users as $user ) {
// 	echo '<span>' . esc_html( $user->display_name ) . '</span><br>';
// }

echo "Logout url ".wp_logout_url();