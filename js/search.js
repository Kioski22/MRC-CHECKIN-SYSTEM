let resultValuesGlobal = [];

function hasApiFailure(apiHealth) {
    return !!(apiHealth && !['ok', 'no_data'].includes(apiHealth.failure_type));
}

function setSearchStatus(message, type = 'info') {
    const searchStatus = document.getElementById('searchStatus');
    if (!searchStatus) {
        return;
    }

    const alertClass = {
        info: 'alert-info',
        success: 'alert-success',
        warning: 'alert-warning',
        danger: 'alert-danger'
    }[type] || 'alert-info';

    searchStatus.innerHTML = `<div class="alert ${alertClass} mb-0" role="alert">${message}</div>`;
}

function clearSearchStatus() {
    const searchStatus = document.getElementById('searchStatus');
    if (searchStatus) {
        searchStatus.innerHTML = '';
    }
}

function clearSearchResults() {
    resultValuesGlobal = [];
    document.getElementById('searchResultsTable').innerHTML = '';
    document.getElementById('searchResultsCard').style.display = 'none';
}

async function handleSearchClick() {
    const searchValue = document.getElementById('searchInput').value;
    if (searchValue) {
        await makeApiRequest(searchValue);
    } else {
        clearSearchResults();
        setSearchStatus('Search box is empty.', 'warning');
    }
}

function insertCheckinData(data) {
    fetch('insertCheckinData', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                firstname: data.firstname,
                lastname: data.lastname,
                middlename: data.middlename,
                email: data.email,
                prclicensenumber: data.prclicencenumber,
                chapter: data.chapter,
                qrcode: data.qrcode || ''
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Check-in data inserted successfully');
            } else {
                console.error('Error inserting check-in data:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

async function makeApiRequest(searchValue) {
    const searchButton = document.getElementById('searchButton');
    try {
        if (searchButton) {
            searchButton.disabled = true;
            searchButton.innerText = 'Searching...';
        }

        clearSearchStatus();
        clearSearchResults();
        setSearchStatus('Searching registration records...', 'info');

        const response = await fetch('ictosearch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `search_term=${encodeURIComponent(searchValue)}`
        });

        if (!response.ok) {
            throw new Error(`Search request failed with status ${response.status}`);
        }

        const data = await response.json();
        handleScanData(data);
    } catch (error) {
        console.error('Error:', error);
        clearSearchResults();
        setSearchStatus(`The hosted app could not complete the search request. This does not yet confirm a Smartpay outage. Details: ${error.message}`, 'danger');
    } finally {
        if (searchButton) {
            searchButton.disabled = false;
            searchButton.innerText = 'Search';
        }
    }
}

async function releaseMSP(email) {
    if (!email) {
        console.error('No email provided for releasing kit.');
        document.getElementById('checkinResultMessage').innerHTML = '<span style="color: red;">Error: No email provided for releasing the kit.</span>';
        $('#checkinResultModal').modal('show');
        return;
    }

    try {
        const response = await fetch('releasemsp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email
            })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('checkinResultMessage').innerText = data.message;
        } else {
            let messageContent = `<i class="fas fa-exclamation-triangle"></i> ${data.message}`;

            if (data.releaseTime) {
                const releaseTime = new Date(data.releaseTime);
                const formattedReleaseTime = new Intl.DateTimeFormat('en-PH', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                }).format(releaseTime);
                messageContent += ` on ${formattedReleaseTime}`;
            }

            document.getElementById('checkinResultMessage').innerHTML = `
            <span style="color: red;">
                ${messageContent}
            </span>`;
        }

        $('#checkinResultModal').modal('show');
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('checkinResultMessage').innerHTML = '<span style="color: red;">Kit release failed: ' + error.message + '</span>';
        $('#checkinResultModal').modal('show');
    }
}

function handleScanData(data) {
    const apiHealth = data.api_health || null;

    if (data.resultValue && data.resultValue.length > 0) {
        resultValuesGlobal = data.resultValue;
        const tableBody = document.getElementById('searchResultsTable');
        tableBody.innerHTML = '';
        resultValuesGlobal.forEach((result, index) => {
            const row = document.createElement('tr');

            const firstnameCell = document.createElement('td');
            firstnameCell.textContent = result.firstname || 'N/A';

            const lastnameCell = document.createElement('td');
            lastnameCell.textContent = result.lastname || 'N/A';

            const emailCell = document.createElement('td');
            emailCell.textContent = result.email || 'N/A';

            const chapterCell = document.createElement('td');
            chapterCell.textContent = result.chapter || 'N/A';

            const registrationTypeCell = document.createElement('td');
            registrationTypeCell.textContent = result.RegistrationType || 'N/A';

            const actionCell = document.createElement('td');
            const viewButton = document.createElement('button');
            viewButton.type = 'button';
            viewButton.className = 'btn btn-primary';
            viewButton.textContent = 'View';
            viewButton.addEventListener('click', () => {
                showModalWithIndex(index);
            });
            actionCell.appendChild(viewButton);

            row.appendChild(firstnameCell);
            row.appendChild(lastnameCell);
            row.appendChild(emailCell);
            row.appendChild(chapterCell);
            row.appendChild(registrationTypeCell);
            row.appendChild(actionCell);
            tableBody.appendChild(row);
        });
        document.getElementById('searchResultsCard').style.display = 'block';
        if (hasApiFailure(apiHealth)) {
            setSearchStatus(`Showing available results. ${apiHealth.message}`, 'warning');
        } else {
            setSearchStatus(`Found ${data.resultValue.length} matching registration record(s).`, 'success');
        }
    } else {
        clearSearchResults();
        if (hasApiFailure(apiHealth)) {
            setSearchStatus(apiHealth.message, 'danger');
        } else {
            setSearchStatus(data.errorMsg || 'No valid data found.', 'warning');
        }
    }
}

function showModalWithIndex(index) {
    const result = resultValuesGlobal[index];

    document.getElementById('modalFirstname').innerText = result.firstname || 'N/A';
    document.getElementById('modalLastname').innerText = result.lastname || 'N/A';
    document.getElementById('modalMiddlename').innerText = result.middlename || 'N/A';
    document.getElementById('modalEmail').innerText = result.email || 'N/A';
    document.getElementById('modalPrclicensenumber').innerText = result.prclicencenumber || 'N/A';
    document.getElementById('modalChapter').innerText = result.chapter || 'N/A';
    document.getElementById('modalQrcodeLink').href = result.qrcode || '#';
    document.getElementById('modalQrcodeLink').innerText = result.qrcode ? 'QR Code' : 'No QR Code Available';

    insertCheckinData(result);
    $('#scanResultModal').modal('show');
}

function editBeforePrint() {
    document.getElementById('editFirstname').value = document.getElementById('modalFirstname').innerText;
    document.getElementById('editLastname').value = document.getElementById('modalLastname').innerText;
    document.getElementById('editMiddlename').value = document.getElementById('modalMiddlename').innerText;
    document.getElementById('editChapter').value = document.getElementById('modalChapter').innerText;

    $('#editPrintModal').modal('show');
}

function saveAndPrint() {
    document.getElementById('modalFirstname').innerText = document.getElementById('editFirstname').value;
    document.getElementById('modalLastname').innerText = document.getElementById('editLastname').value;
    document.getElementById('modalMiddlename').innerText = document.getElementById('editMiddlename').value;
    document.getElementById('modalChapter').innerText = document.getElementById('editChapter').value;

    $('#editPrintModal').modal('hide');
    printLabel();
}

function printLabel() {
    const firstname = document.getElementById('modalFirstname').innerText;
    const lastname = document.getElementById('modalLastname').innerText;
    const middlename = document.getElementById('modalMiddlename').innerText;
    const chapter = document.getElementById('modalChapter').innerText;
    let qrcodeLink = document.getElementById('modalQrcodeLink').href;

    console.log('Original QR Code Link:', qrcodeLink);

    if (qrcodeLink && qrcodeLink !== '#') {
        if (qrcodeLink.includes('psmefiles.s3.ap-southeast-1.amazonaws.com')) {
            console.log('Detected AWS S3 QR code, checking for local alternative...');

            const email = document.getElementById('modalEmail').innerText;
            if (email && email !== 'N/A') {
                qrcodeLink = qrcodeLink.replace(/\|/g, '%7C');
                console.log('Using encoded AWS URL:', qrcodeLink);
            }
        } else if (qrcodeLink.includes('http://') || qrcodeLink.includes('https://')) {
            qrcodeLink = qrcodeLink.replace(/\|/g, '%7C');
            console.log('Using encoded external URL:', qrcodeLink);
        } else {
            const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

            if (qrcodeLink.includes('.png') && !qrcodeLink.startsWith('uploads/qrcode/')) {
                const filename = qrcodeLink.split('/').pop();
                qrcodeLink = 'uploads/qrcode/' + filename;
            }

            qrcodeLink = baseUrl + qrcodeLink;
            console.log('Converted to absolute local URL:', qrcodeLink);
        }
    }

    if (!qrcodeLink || qrcodeLink === '#' || qrcodeLink === '') {
        console.warn('No valid QR code link found, using placeholder');
        qrcodeLink = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNjc4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gUVIgQ29kZTwvdGV4dD48L3N2Zz4=';
    }

    let namePart = `${lastname}, ${firstname}`;
    if (middlename !== 'N/A' && middlename.trim() !== '') {
        namePart += ` ${middlename}`;
    }

    const qrFallbackMarkup = '<div style="width:33mm;height:33mm;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;font-size:8px;color:#666;text-align:center;">QR Code<br>Not Available</div>';

    const printContent = `
    <div style="width: 75mm; height: 105mm; position: relative; margin: 0; padding: 0; box-sizing: border-box;">
        <div style="position: absolute; width: 100mm; height: 70mm; background: #fff; transform: rotate(90deg); left: -12.5mm; top: 17.5mm; font-size: 16px; font-weight: 600; line-height: 1.1; padding: 0; box-sizing: border-box; font-family: 'Arial', 'Helvetica', sans-serif;">
        <p style="position: absolute; top: 4mm; left: 4mm; width: calc(100% - 8mm); margin: 0; font-size: 34px; font-weight: 700; text-align: left; padding: 2mm; box-sizing: border-box; word-wrap: break-word; overflow-wrap: break-word; hyphens: auto;">
            ${namePart}
        </p>

        <p style="position: absolute; bottom: 4mm; left: 4mm; width: calc(100% - 38mm); margin: 0; font-size: 20px; font-style: italic; color: #333; text-align: left; padding: 2mm; box-sizing: border-box; word-wrap: break-word; overflow-wrap: break-word; hyphens: auto;">
            ${chapter} Chapter
        </p>

        <div id="printQrContainer" style="position: absolute; bottom: 4mm; right: 4mm; width: 35mm; height: 35mm; display: flex; justify-content: center; align-items: center; box-sizing: border-box; z-index: 10;">
            <img id="printQrImage" src="${qrcodeLink}" alt="QR Code" style="width: 33mm; height: 33mm; box-sizing: border-box;">
        </div>
        </div>
    </div>`;

    const printWindow = window.open('', '_blank');
    printWindow.document.open();
    printWindow.document.write(`
    <html>
    <head>
    <title>Print Label</title>
    <style>
        @page {
        size: 75mm 105mm;
        margin: 0mm !important;
        }
        html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 75mm !important;
        height: 105mm !important;
        overflow: hidden !important;
        }
        * {
        box-sizing: border-box !important;
        }
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 75mm !important;
                height: 105mm !important;
            }
        }
    </style>
    </head>
    <body>
    ${printContent}
    </body>
    </html>
`);
    printWindow.document.close();

    const finalizePrint = () => {
        if (printWindow.closed) {
            return;
        }

        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };

    const qrContainer = printWindow.document.getElementById('printQrContainer');
    const qrImage = printWindow.document.getElementById('printQrImage');
    let printTriggered = false;

    const triggerPrintOnce = () => {
        if (printTriggered) {
            return;
        }

        printTriggered = true;
        printWindow.setTimeout(finalizePrint, 150);
    };

    const showQrFallback = () => {
        if (!qrContainer) {
            triggerPrintOnce();
            return;
        }

        qrContainer.innerHTML = qrFallbackMarkup;
        triggerPrintOnce();
    };

    if (!qrImage) {
        triggerPrintOnce();
        return;
    }

    qrImage.addEventListener('load', () => {
        console.log('QR Code loaded successfully');
        triggerPrintOnce();
    });

    qrImage.addEventListener('error', () => {
        console.warn('QR Code failed to load for printing');
        showQrFallback();
    });

    if (qrImage.complete) {
        if (typeof qrImage.naturalWidth === 'number' && qrImage.naturalWidth > 0) {
            triggerPrintOnce();
        } else {
            showQrFallback();
        }
        return;
    }

    printWindow.setTimeout(triggerPrintOnce, 1500);
}

function checkIn() {
    const firstname = document.getElementById('modalFirstname').innerText;
    const lastname = document.getElementById('modalLastname').innerText;
    const middlename = document.getElementById('modalMiddlename').innerText;
    const email = document.getElementById('modalEmail').innerText;
    const prclicensenumber = document.getElementById('modalPrclicensenumber').innerText;
    const chapter = document.getElementById('modalChapter').innerText;
    const qrcodeLinkEl = document.getElementById('modalQrcodeLink');
    const qrcode = (qrcodeLinkEl && qrcodeLinkEl.getAttribute('href') === '#')
        ? ''
        : (qrcodeLinkEl ? qrcodeLinkEl.href : '');

    fetch('checkinscript', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                firstname,
                lastname,
                middlename,
                email,
                prclicensenumber,
                chapter,
                qrcode
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('checkinResultMessage').innerText = data.message;
            } else {
                document.getElementById('checkinResultMessage').innerHTML = `
        <span style="color: red;">
            <i class="fas fa-exclamation-triangle"></i> ${data.message}
        </span>`;
            }
            $('#checkinResultModal').modal('show');

            if (data.success) {
                const releaseKitNow = confirm('Do you want to release the kit now?');
                if (releaseKitNow) {
                    releaseKit(email);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('checkinResultMessage').innerText = 'Check-in failed: ' + error.message;
            $('#checkinResultModal').modal('show');
        });
}

async function releaseKit(email) {
    if (!email) {
        console.error('No email provided for releasing kit.');
        document.getElementById('checkinResultMessage').innerHTML = '<span style="color: red;">Error: No email provided for releasing the kit.</span>';
        $('#checkinResultModal').modal('show');
        return;
    }

    try {
        const response = await fetch('releasekit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email
            })
        });

        const data = await response.json();

        if (data.success) {
            const releaseTime = new Date(data.releaseTime);
            new Intl.DateTimeFormat('en-PH', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            }).format(releaseTime);
            document.getElementById('checkinResultMessage').innerText = data.message;
        } else {
            const releaseTime = new Date(data.releaseTime);
            const formattedReleaseTime = new Intl.DateTimeFormat('en-PH', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            }).format(releaseTime);

            document.getElementById('checkinResultMessage').innerHTML = `
        <span style="color: red;">
            <i class="fas fa-exclamation-triangle"></i> ${data.message} on ${formattedReleaseTime}
        </span>`;
        }

        $('#checkinResultModal').modal('show');
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('checkinResultMessage').innerHTML = '<span style="color: red;">Kit release failed: ' + error.message + '</span>';
        $('#checkinResultModal').modal('show');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const searchButton = document.getElementById('searchButton');
    const searchInput = document.getElementById('searchInput');

    if (searchButton && !searchButton.dataset.listenerAttached) {
        searchButton.addEventListener('click', handleSearchClick);
        searchButton.dataset.listenerAttached = 'true';
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', async event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                await handleSearchClick();
            }
        });
    }

    if (window.feather && typeof window.feather.replace === 'function') {
        window.feather.replace();
    }
});