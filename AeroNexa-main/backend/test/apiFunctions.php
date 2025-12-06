<?php

function fetchDataFromApi($endpoint)
{
    $ch = curl_init();

    // Set options
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // 1. EXECUTE the request
    $response = curl_exec($ch);

    // 2. CHECK for errors (Optional but recommended)
    if (curl_errno($ch)) {
        echo 'Request Error:' . curl_error($ch);
    }

    // 3. CLOSE the connection
    curl_close($ch);

    // 4. RETURN the data
    return $response;
}
