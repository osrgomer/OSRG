<?php
/*
Plugin Name: OSRG AI Helper
Description: Adds an AI chat helper widget to every page using the **ChatGPT API**.
Version: 2.1
Author: OSRG
*/
function wp_ai_helper_get_chatgpt_response($message) {
    // Chatgpt API integration with website context
    $api_key = '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'; 
    $endpoint = 'https://api.openai.com/v1/chat/completions'; // Correct ChatGPT chat endpoint

    // Set your website context here, and add a clear instruction for brevity.
    $website_context = 'You are an assistant for the OSRG website. The website is osrg.lol. Its main mission is to serve as a portfolio of the founder\'s coding life. The content primarily consists of coding experiments and tests. The founder and CEO of OSRG is OSRG, which stands for Omer Shalom Rimon Givon. Answer questions using this context, and be sure to keep your answers concise and to the point, preferably in one to two sentences.';

    $data = array(
        'model' => 'gpt-3.5-turbo', // You can change the model, e.g., to 'gpt-4'
        'messages' => array(
            array('role' => 'system', 'content' => $website_context),
            array('role' => 'user', 'content' => $message)
        )
    );
    
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            // OpenAI API key is typically passed in the Authorization header
            'Authorization' => 'Bearer ' . $api_key 
        ),
        'body' => json_encode($data),
        'method' => 'POST',
        'timeout' => 45 // Adjust timeout if needed
    );

    $result = wp_remote_post($endpoint, $args);

    if (is_wp_error($result)) {
        return 'Sorry, I could not connect to the AI.';
    }

    $body = json_decode(wp_remote_retrieve_body($result), true);

    // Check for a valid response structure
    if (isset($body['choices'][0]['message']['content'])) {
        return $body['choices'][0]['message']['content'];
    }

    // Handle potential API errors returned in the body
    if (isset($body['error']['message'])) {
        return 'AI Error: ' . $body['error']['message'];
    }
    
    return 'Sorry, I did not understand that.';
}