<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tea Time - Find Your Perfect Tea</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        #main {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        h1 {
            background: linear-gradient(45deg, #2c5530, #4a7c59);
            color: white;
            text-align: center;
            padding: 30px 20px;
            font-size: 2.2em;
            margin: 0;
        }

        #description {
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
        }

        .top-description {
            font-size: 1.1em;
            line-height: 1.6;
            color: #555;
        }

        #tea-quiz-form {
            padding: 30px;
            display: grid;
            gap: 25px;
        }

        .question {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .label {
            font-weight: 600;
            color: #2c5530;
            font-size: 1.1em;
        }

        select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            background: white;
            transition: border-color 0.3s ease;
        }

        select:focus {
            outline: none;
            border-color: #4a7c59;
        }

        .send {
            background: linear-gradient(45deg, #2c5530, #4a7c59);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.2em;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin-top: 20px;
        }

        .send:hover {
            transform: translateY(-2px);
        }

        #results {
            padding: 30px;
            background: #f0f8f0;
            border-top: 3px solid #4a7c59;
            display: none;
        }

        #results h2 {
            color: #2c5530;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        #recommendation-text {
            font-size: 1.2em;
            font-weight: 600;
            color: #4a7c59;
            margin-bottom: 10px;
        }

        #tea-details {
            color: #666;
            line-height: 1.6;
        }

        #tea-info {
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
        }

        .bottom-description {
            line-height: 1.6;
            color: #555;
            margin-bottom: 20px;
        }

        #Back a {
            color: #4a7c59;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1em;
        }

        #Back a:hover {
            text-decoration: underline;
        }

        @media (min-width: 768px) {
            #tea-quiz-form {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 25px;
            }
            
            .question:last-of-type {
                grid-column: 1 / -1;
            }
            
            .send {
                grid-column: 1 / -1;
                justify-self: center;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>