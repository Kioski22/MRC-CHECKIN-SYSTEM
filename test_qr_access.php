<?php
/**
 * QR Code Access Test
 * This script tests if QR code files in uploads/qrcode directory are accessible
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Code Access Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>QR Code Access Test</h1>
    
    <div class="test-section">
        <h2>1. Directory Structure Test</h2>
        <?php
        $uploadDir = 'uploads/qrcode/';
        if (is_dir($uploadDir)) {
            echo "<p class='success'>✓ uploads/qrcode directory exists</p>";
            
            $files = scandir($uploadDir);
            $qrFiles = array_filter($files, function($file) {
                return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif', 'svg']);
            });
            
            if (count($qrFiles) > 0) {
                echo "<p class='success'>✓ Found " . count($qrFiles) . " QR code files</p>";
                echo "<p>Sample files:</p><ul>";
                $sampleFiles = array_slice($qrFiles, 0, 5);
                foreach ($sampleFiles as $file) {
                    echo "<li>$file</li>";
                }
                if (count($qrFiles) > 5) {
                    echo "<li>... and " . (count($qrFiles) - 5) . " more</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='warning'>⚠ No QR code image files found in directory</p>";
            }
        } else {
            echo "<p class='error'>✗ uploads/qrcode directory does not exist</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>2. Direct File Access Test</h2>
        <?php
        if (isset($qrFiles) && count($qrFiles) > 0) {
            $testFile = reset($qrFiles);
            $testPath = $uploadDir . $testFile;
            echo "<p>Testing access to: <code>$testPath</code></p>";
            
            if (file_exists($testPath)) {
                echo "<p class='success'>✓ File exists on server</p>";
                $fileSize = filesize($testPath);
                echo "<p class='success'>✓ File size: " . number_format($fileSize) . " bytes</p>";
                
                // Create a test URL
                $testUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . 
                          rtrim(dirname($_SERVER['REQUEST_URI']), '/') . '/' . $testPath;
                echo "<p>Direct URL: <a href='$testUrl' target='_blank'>$testUrl</a></p>";
                
                // Show image preview
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                echo "<p><strong>Image Preview:</strong></p>";
                echo "<img src='$testPath' alt='Test QR Code' style='max-width: 200px; max-height: 200px; border: 1px solid #ddd;' ";
                echo "onerror=\"this.style.display='none'; this.nextElementSibling.style.display='block';\">";
                echo "<div style='display:none; color: red;'>✗ Image failed to load (htaccess issue)</div>";
                echo "</div>";
            } else {
                echo "<p class='error'>✗ File does not exist on server</p>";
            }
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>3. .htaccess Test</h2>
        <?php
        $htaccessPath = '.htaccess';
        if (file_exists($htaccessPath)) {
            echo "<p class='success'>✓ .htaccess file exists</p>";
            
            $htaccessContent = file_get_contents($htaccessPath);
            if (strpos($htaccessContent, 'uploads/') !== false) {
                echo "<p class='success'>✓ .htaccess contains uploads directory rules</p>";
            } else {
                echo "<p class='warning'>⚠ .htaccess may not have proper uploads directory exclusion</p>";
            }
            
            if (strpos($htaccessContent, 'png|jpg|jpeg|gif') !== false) {
                echo "<p class='success'>✓ .htaccess contains image file extension rules</p>";
            } else {
                echo "<p class='warning'>⚠ .htaccess may not exclude image files properly</p>";
            }
        } else {
            echo "<p class='error'>✗ .htaccess file not found</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>4. JavaScript Test</h2>
        <button onclick="testQRCodeAccess()">Test QR Code Loading in JavaScript</button>
        <div id="jsTestResult" style="margin-top: 10px;"></div>
        
        <script>
        function testQRCodeAccess() {
            const resultDiv = document.getElementById('jsTestResult');
            resultDiv.innerHTML = '<p>Testing...</p>';
            
            <?php if (isset($testPath)): ?>
            const testImagePath = '<?php echo $testPath; ?>';
            
            const img = new Image();
            img.onload = function() {
                resultDiv.innerHTML = '<p style="color: green;">✓ QR code image loaded successfully via JavaScript</p>';
            };
            img.onerror = function() {
                resultDiv.innerHTML = '<p style="color: red;">✗ QR code image failed to load via JavaScript</p>' +
                                    '<p>This indicates an .htaccess or server configuration issue.</p>';
            };
            img.src = testImagePath;
            <?php else: ?>
            resultDiv.innerHTML = '<p style="color: orange;">⚠ No test file available</p>';
            <?php endif; ?>
        }
        </script>
    </div>
    
    <div class="test-section">
        <h2>5. Print Function Test</h2>
        <p>This simulates how the print function accesses QR codes:</p>
        <?php if (isset($testPath)): ?>
        <button onclick="testPrintFunction()">Test Print Function QR Access</button>
        <div id="printTestResult" style="margin-top: 10px;"></div>
        
        <script>
        function testPrintFunction() {
            const resultDiv = document.getElementById('printTestResult');
            const qrcodeLink = '<?php echo $testPath; ?>';
            
            // Simulate the print function's QR code usage
            const printContent = `
                <div style="border: 1px solid #ccc; padding: 20px; margin: 10px 0;">
                    <h4>Print Preview (QR Code Test)</h4>
                    <div style="width: 100px; height: 100px; border: 1px solid #ddd; display: inline-block;">
                        <img src="${qrcodeLink}" alt="QR Code" style="width: 100%; height: 100%;" 
                             onerror="this.style.display='none'; this.parentNode.innerHTML='<div style=\\"display:flex;align-items:center;justify-content:center;height:100%;font-size:10px;color:#666;\\">QR Failed</div>';">
                    </div>
                    <p>QR Code Path: ${qrcodeLink}</p>
                </div>
            `;
            
            resultDiv.innerHTML = printContent;
        }
        </script>
        <?php else: ?>
        <p style="color: orange;">⚠ No test file available for print function test</p>
        <?php endif; ?>
    </div>
    
    <div class="test-section">
        <h2>Recommendations</h2>
        <ul>
            <li>If images fail to load, check that the .htaccess file properly excludes the uploads directory</li>
            <li>Ensure the uploads/qrcode directory has proper read permissions</li>
            <li>Verify that QR code files are being generated with correct file extensions</li>
            <li>Test print functionality after making .htaccess changes</li>
        </ul>
    </div>
    
    <p><a href="search.php">← Back to Search</a></p>
</body>
</html>