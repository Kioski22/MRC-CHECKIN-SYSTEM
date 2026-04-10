<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>QR Code Print Test</title>
    <style>
        .test-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .qr-test {
            display: inline-block;
            margin: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        .qr-test img {
            width: 100px;
            height: 100px;
            border: 1px solid #999;
        }
        .print-preview {
            width: 75mm;
            height: 105mm;
            border: 2px solid #000;
            margin: 20px 0;
            position: relative;
            background: #fff;
        }
        .label-content {
            position: absolute;
            width: 100mm;
            height: 70mm;
            background: #fff;
            transform: rotate(90deg);
            left: -12.5mm;
            top: 17.5mm;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.1;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', 'Helvetica', sans-serif;
            border: 1px solid red; /* Debug border */
        }
        .name-section {
            position: absolute;
            top: 4mm;
            left: 4mm;
            width: calc(100% - 8mm);
            margin: 0;
            font-size: 34px;
            font-weight: 700;
            text-align: left;
            padding: 2mm;
            box-sizing: border-box;
            background: rgba(255,255,0,0.1); /* Debug background */
        }
        .chapter-section {
            position: absolute;
            bottom: 4mm;
            left: 4mm;
            width: calc(100% - 38mm);
            margin: 0;
            font-size: 20px;
            font-style: italic;
            color: #333;
            text-align: left;
            padding: 2mm;
            box-sizing: border-box;
            background: rgba(0,255,0,0.1); /* Debug background */
        }
        .qr-section {
            position: absolute;
            bottom: 4mm;
            right: 4mm;
            width: 35mm;
            height: 35mm;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            z-index: 10;
            background: rgba(0,0,255,0.1); /* Debug background */
            border: 1px solid blue; /* Debug border */
        }
        .qr-section img {
            width: 33mm;
            height: 33mm;
            box-sizing: border-box;
        }
        .debug-info {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <h1>QR Code Print Test</h1>
    
    <div class="test-section">
        <h2>1. QR Code File Access Test</h2>
        <div class="debug-info">
            <strong>Testing QR code file access from uploads/qrcode/ directory</strong>
        </div>
        
        <?php
        $uploadDir = 'uploads/qrcode/';
        if (is_dir($uploadDir)) {
            $qrFiles = glob($uploadDir . '*.png');
            if (!empty($qrFiles)) {
                echo "<p>Found " . count($qrFiles) . " QR code files:</p>";
                foreach (array_slice($qrFiles, 0, 3) as $file) { // Show first 3 files
                    $filename = basename($file);
                    $fileUrl = $file;
                    echo "<div class='qr-test'>";
                    echo "<p><strong>File:</strong> $filename</p>";
                    echo "<p><strong>URL:</strong> $fileUrl</p>";
                    echo "<img src='$fileUrl' alt='QR Code' onerror=\"this.style.border='2px solid red'; this.alt='FAILED TO LOAD';\"/>";
                    echo "</div>";
                }
            } else {
                echo "<p style='color: red;'>No QR code files found in uploads/qrcode/</p>";
            }
        } else {
            echo "<p style='color: red;'>uploads/qrcode/ directory does not exist</p>";
        }
        ?>
    </div>

    <div class="test-section">
        <h2>2. URL Rewrite Test</h2>
        <div class="debug-info">
            <strong>Testing different URL patterns</strong>
        </div>
        
        <?php
        // Test different URL patterns
        $testUrls = [
            'uploads/qrcode/test.png',
            './uploads/qrcode/test.png',
            '/checkin/uploads/qrcode/test.png',
            'http://localhost/checkin/uploads/qrcode/test.png'
        ];
        
        foreach ($testUrls as $url) {
            echo "<div class='qr-test'>";
            echo "<p><strong>Testing URL:</strong> $url</p>";
            echo "<img src='$url' alt='QR Test' onerror=\"this.style.border='2px solid red'; this.alt='FAILED: $url';\"/>";
            echo "</div>";
        }
        ?>
    </div>

    <div class="test-section">
        <h2>3. Print Label Preview Test</h2>
        <div class="debug-info">
            <strong>Testing actual print label layout with QR code</strong>
        </div>
        
        <?php
        // Get a sample QR code file for testing
        $sampleQr = '';
        if (is_dir('uploads/qrcode/')) {
            $qrFiles = glob('uploads/qrcode/*.png');
            if (!empty($qrFiles)) {
                $sampleQr = $qrFiles[0];
            }
        }
        
        if (empty($sampleQr)) {
            $sampleQr = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNjc4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gUVIgQ29kZTwvdGV4dD48L3N2Zz4=';
        }
        ?>
        
        <p><strong>Using QR Code:</strong> <?php echo htmlspecialchars($sampleQr); ?></p>
        
        <div class="print-preview">
            <div class="label-content">
                <div class="name-section">
                    DOE, JOHN MIDDLE
                </div>
                
                <div class="chapter-section">
                    Test Chapter
                </div>
                
                <div class="qr-section">
                    <img src="<?php echo htmlspecialchars($sampleQr); ?>" alt="QR Code" 
                         onerror="this.style.display='none'; this.parentNode.innerHTML='<div style=&quot;width:33mm;height:33mm;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;font-size:8px;color:#666;&quot;>QR Code<br>Not Available</div>';">
                </div>
            </div>
        </div>
    </div>

    <div class="test-section">
        <h2>4. JavaScript QR Loading Test</h2>
        <div class="debug-info">
            <strong>Testing QR code loading with JavaScript error handling</strong>
        </div>
        
        <button onclick="testQrLoading()">Test QR Loading</button>
        <div id="qrTestResult"></div>
    </div>

    <script>
        function testQrLoading() {
            const testUrls = [
                'uploads/qrcode/test.png',
                './uploads/qrcode/test.png',
                <?php 
                if (!empty($sampleQr) && strpos($sampleQr, 'data:') !== 0) {
                    echo "'" . addslashes($sampleQr) . "',";
                }
                ?>
                'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNjc4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gUVIgQ29kZTwvdGV4dD48L3N2Zz4='
            ];
            
            const resultDiv = document.getElementById('qrTestResult');
            resultDiv.innerHTML = '<h4>Testing QR Code URLs:</h4>';
            
            testUrls.forEach((url, index) => {
                const testDiv = document.createElement('div');
                testDiv.style.cssText = 'margin: 10px 0; padding: 10px; border: 1px solid #ddd;';
                
                const img = document.createElement('img');
                img.src = url;
                img.style.cssText = 'width: 50px; height: 50px; margin-right: 10px;';
                img.alt = 'Test QR';
                
                img.onload = function() {
                    testDiv.innerHTML = `<span style="color: green;">✓ SUCCESS:</span> ${url}`;
                    testDiv.appendChild(img);
                };
                
                img.onerror = function() {
                    testDiv.innerHTML = `<span style="color: red;">✗ FAILED:</span> ${url}`;
                };
                
                resultDiv.appendChild(testDiv);
            });
        }
        
        // Auto-run the test when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(testQrLoading, 1000);
        });
    </script>
</body>
</html>