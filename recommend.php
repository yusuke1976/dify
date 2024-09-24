<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $worry = $_POST['worry'];

    // Replace with your actual Dify workflow URL
    $dify_url = 'https://udify.app/workflow/wgyjDWDfrLNNCAmL';

    // Prepare data for the request
    $data = array('input' => $worry);

    // Set up cURL to send data to Dify
    $ch = curl_init($dify_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Execute the request and get the response
    $response = curl_exec($ch);
    curl_close($ch);

    // Display the response
    echo "<h2>Recommended Book:</h2>";
    echo "<p>$response</p>";
}
?>