<?php
require 'vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel'])) {
    $filePath = $_FILES['excel']['tmp_name'];
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();

    $allAddresses = [];

    echo "<h2>AI Address Split Results</h2>";
    echo "<style>
        .json-output {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            overflow-x: auto;
            position: relative;
            max-height: 400px;
            overflow-y: auto;
        }
        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #007cba;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .copy-btn:hover {
            background-color: #005a87;
        }
        .copy-success {
            background-color: #28a745 !important;
        }
        .processing-info {
            color: #666;
            font-style: italic;
            margin: 10px 0;
        }
    </style>";

    echo "<div class='processing-info'>Processing addresses...</div>";

    foreach ($sheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        foreach ($cellIterator as $cell) {
            $cellValue = $cell->getValue();

            if (empty($cellValue)) continue;

            $prompt = "You are an address formatting assistant. Return ONLY a valid JSON object (no markdown, no code blocks,no extra field , do not add original address, no extra text) with exactly these fields: street, district, state, country. Address: \"$cellValue\"";
            $response = sendToLokiAI($prompt);

            $cleanedResponse = $response;

            $cleanedResponse = preg_replace('/^```json\s*/', '', $cleanedResponse);
            $cleanedResponse = preg_replace('/\s*```$/', '', $cleanedResponse);
            $cleanedResponse = trim($cleanedResponse);

    
            $parsedResponse = json_decode($cleanedResponse, true);

            if ($parsedResponse && is_array($parsedResponse) && json_last_error() === JSON_ERROR_NONE) {
                
                $parsedResponse['original_address'] = $cellValue;
                $allAddresses[] = $parsedResponse;
            } else {

                $extractedJson = extractJsonFromResponse($response);
                if ($extractedJson) {
                    $extractedJson['original_address'] = $cellValue;
                    $allAddresses[] = $extractedJson;
                } else {

                    $allAddresses[] = [
                        'original_address' => $cellValue,
                        'street' => 'Parse Error',
                        'district' => 'Parse Error', 
                        'state' => 'Parse Error',
                        'country' => 'Parse Error',
                        'raw_response' => $response,
                        'error' => 'Failed to parse JSON response'
                    ];
                }
            }
        }
    }

    $jsonOutput = json_encode($allAddresses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    echo "<h3>Complete Address Split Results</h3>";
    echo "<div class='json-output' id='jsonOutput'>";
    echo "<button class='copy-btn' onclick='copyToClipboard()' id='copyBtn'>Copy JSON</button>";
    echo htmlspecialchars($jsonOutput);
    echo "</div>";

    echo "<script>
        function copyToClipboard() {
            const jsonText = " . json_encode($jsonOutput) . ";

            // Create a temporary textarea element
            const tempTextarea = document.createElement('textarea');
            tempTextarea.value = jsonText;
            document.body.appendChild(tempTextarea);

            // Select and copy the text
            tempTextarea.select();
            tempTextarea.setSelectionRange(0, 99999); // For mobile devices

            try {
                document.execCommand('copy');

                // Update button to show success
                const btn = document.getElementById('copyBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = 'Copied!';
                btn.classList.add('copy-success');

                // Reset button after 2 seconds
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('copy-success');
                }, 2000);

            } catch (err) {
                console.error('Failed to copy: ', err);
                alert('Failed to copy to clipboard');
            }

            // Remove the temporary textarea
            document.body.removeChild(tempTextarea);
        }
    </script>";

} else {
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Excel Address Split</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        input[type="file"] {
            margin: 10px 0;
            padding: 5px;
        }
        button {
            background-color: #007cba;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #005a87;
        }
        .info {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>
<body>
    <h1>Upload Excel File (Address in Cells)</h1>

    <div class="info">
        <strong>How it works:</strong>
        <ul>
            <li>Upload an Excel file containing addresses in cells</li>
            <li>AI will process each address and split it into: street, district, state, country</li>
            <li>Results will be displayed as a single JSON block with a copy button</li>
            <li>Original addresses are preserved for reference</li>
        </ul>
    </div>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="excel" accept=".xlsx,.xls" required>
        <button type="submit">Upload & Process</button>
    </form>
</body>
</html>
<?php } ?>

<?php
function sendToLokiAI($prompt) {
    $url = "";
    $headers = [ 
        "Content-Type: application/json",
        "Authorization: Bearer "
    ];
    $data = [
        "model" => "mistral",
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Add timeout

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
    }

    curl_close($ch);
    $response = json_decode($result, true);

    if (isset($response['choices'][0]['message']['content'])) {
        return $response['choices'][0]['message']['content'];
    } else {
        return json_encode(['error' => 'API Error', 'response' => $response]);
    }
}

function extractJsonFromResponse($response) {

    $patterns = [
        '/```json\s*(.*?)\s*```/s',       
        '/(\{.*\})/s'                
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $response, $matches)) {
            $jsonString = trim($matches[1]);
            $parsed = json_decode($jsonString, true);
            if ($parsed && is_array($parsed) && json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }
    }

    return null;
}
?>
