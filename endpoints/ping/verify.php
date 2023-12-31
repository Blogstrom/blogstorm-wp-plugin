<?php
require_once(plugin_dir_path(__FILE__) . 'bs_register_ping_verify.php');

// Function to send a Ping Verify request
function blogstorm_ping_verify($request)
{
    // Determine the base URL and auth token based on the environment
    $is_development = getenv('BS_ENV') === 'development';
    $api_base_url = $is_development ? BS_DEV_PING_VERIFY_URL : BS_PROD_PING_VERIFY_URL;

    // Prepare the data for the POST request
    $auth_token = get_option(BS_TOKEN_NAME);
    $base_url = get_site_url(); // Base URL of the WordPress site

    // Send the POST request
    $response = wp_remote_post($api_base_url, array(
        'timeout' => 10,
        'sslverify' => false,
        'body' => json_encode(array(
            'auth_token' => $auth_token,
            'base_url' => $base_url,
        )),
    ));

    if (is_wp_error($response)) {
        return new WP_Error('ping_verify_error', 'Error while sending Ping Verify request');
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code === 200) {
            return array('message' => 'Ping Verify request sent successfully.', 'response' => json_decode($response_body));
        } elseif ($response_code === 401) {
            return new WP_Error('unauthorized', 'You are not authorized to perform this action.', array('status' => 401));
        } elseif ($response_code === 400) {
            return array('message' => 'BAD.', 'response' => array('auth_token' => $auth_token, 'base_url' => $base_url), 'q'=> $response_body);
        } else {
            return new WP_Error('ping_verify_error', 'Error while sending "Ping Verify" request.', array('status' => $response_code));
        }
    }
}
