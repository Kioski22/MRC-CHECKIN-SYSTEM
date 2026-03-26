<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Manual Print Label</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <script src="js/html5-qrcode.min.js"></script>
</head>

<body id="page-top">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0 font-weight-bold text-primary">Manual Print Label</h5>
            </div>
            <div class="card-body">
                <form id="manualInputForm">
                    <div class="form-group">
                        <label for="manualFirstname">First Name:</label>
                        <input type="text" class="form-control" id="manualFirstname">
                    </div>
                    <div class="form-group">
                        <label for="manualLastname">Last Name:</label>
                        <input type="text" class="form-control" id="manualLastname">
                    </div>
                    <div class="form-group">
                        <label for="manualMiddlename">Middle Name:</label>
                        <input type="text" class="form-control" id="manualMiddlename">
                    </div>
                    <div class="form-group">
                        <label for="manualChapter">Chapter:</label>
                        <input type="text" class="form-control" id="manualChapter">
                    </div>
                    <div class="form-group">
                        <label for="manualQrcodeLink">QR Code Link:</label>
                        <input type="text" class="form-control" id="manualQrcodeLink" onchange="handleQrCodeLinkChange()">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="useDefaultQrCode" onchange="handleQrCodeLinkChange()">
                            <label class="form-check-label" for="useDefaultQrCode">Use default QR code (img/73rdNatCon.jpg) if not available</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary" onclick="printLabel()">Print</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        function handleQrCodeLinkChange() {
            const qrCodeLinkInput = document.getElementById('manualQrcodeLink');
            const useDefaultQrCodeCheckbox = document.getElementById('useDefaultQrCode');
            
            if (useDefaultQrCodeCheckbox.checked && !qrCodeLinkInput.value.trim()) {
                qrCodeLinkInput.value = 'img/73rdNatCon.jpg';
            } else if (!useDefaultQrCodeCheckbox.checked && qrCodeLinkInput.value === 'img/73rdNatCon.jpg') {
                qrCodeLinkInput.value = '';
            }
        }


        function printLabel() {
            const firstname = document.getElementById('manualFirstname').value;
            const lastname = document.getElementById('manualLastname').value;
            const middlename = document.getElementById('manualMiddlename').value;
            const chapter = document.getElementById('manualChapter').value;
            const qrcodeLink = document.getElementById('manualQrcodeLink').value;

            // Use default QR code if checkbox is checked and no link is provided
            if (document.getElementById('useDefaultQrCode').checked && !qrcodeLink.trim()) {
                qrcodeLink = 'img/73rdNatCon.jpg';
            }

            // Construct the name part with middle name included only if it is not "N/A"
            let namePart = `${lastname}, ${firstname}`;
            if (middlename !== 'N/A' && middlename.trim() !== '') {
                namePart += ` ${middlename}`;
            }

            const printContent = `
<div style="width: 70mm; height: 50mm; display: flex; font-size: 16px; text-align: center; line-height: 1; margin: 0; font-weight: bold;">
    <!-- Left Column: Name and Chapter -->
    <div style="width: 33%; margin: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2mm;">
        <!-- Name section with larger font and bold -->
        <p style="margin-bottom: 5px; font-size: calc(24px - (2mm * (100% - 33%))); font-weight: bold; text-align: left; width: 100%; max-width: 100%; overflow-wrap: normal;">
            ${namePart}
        </p>

        <hr style="width: 100%; border: 0; border-top: 1px solid lightgray; margin: 5px 0;">

        <!-- Chapter section with smaller font, lighter color, and italics -->
        <p style="margin-top: 5px; text-align: left; width: 100%; max-width: 100%; font-size: 16px; font-style: italic; color: gray;">
            ${chapter} Chapter
        </p>
    </div>

    <!-- Right Column: QR Code -->
    <div style="width: 67%; display: flex; justify-content: center; align-items: center;">
        <img src="${qrcodeLink}" alt="QR Code" style="width: 40mm; height: 40mm; margin-right: 15px;">
    </div>
</div>
    `;

            // Print logic (e.g., open a new window and print the content)
            const printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write(`
            <html>
            <head><title>Print Label</title></head>
            <body onload="window.print(); window.close();">
                ${printContent}
            </body>
            </html>
        `);
            printWindow.document.close();
        }
    </script>
</body>

</html>
