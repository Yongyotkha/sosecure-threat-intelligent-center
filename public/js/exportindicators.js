function exportCSV() {
    const selectEl = document.querySelector('select.c-tags');
    const eventName = document.getElementById('pulse-name')?.value || '';

    if (!selectEl) {
        Swal.fire('Error', 'ไม่พบ select ที่มี class .c-tags', 'error');
        return;
    }

    const pulseId = selectEl.getAttribute('data-pulse_id');
    if (!pulseId) {
        Swal.fire('Error', 'ไม่พบค่า pulse_id', 'error');
        return;
    }

    // const confirmDownload = confirm(`Do you want to export Attributes of ${eventName} ?`);
    // if (!confirmDownload) {
    Swal.fire({
        title: 'Do you want to export?',
        text: 'Do you want to export Indicators of ' + eventName + ' ?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Export',
        cancelButtonText: 'Cancel',
        reverseButtons: false
    }).then((result) => {
        if (!result.isConfirmed) {
            Swal.fire({
                icon: 'info',
                title: 'You have canceled the export',
                text: 'You have canceled the export Indicators.',
                timer: 1200,
                showConfirmButton: false
            });
            return;
        }

        Swal.fire({
            title: 'Creating file...',
            text: 'Please wait while we process your request.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        // }

        // document.getElementById('loader').style.display = 'block';

        const queryParams = new URLSearchParams();
        queryParams.append('pulse_id', pulseId);

        const fullUrl = `${exportBaseUrl}?${queryParams.toString()}`;

        fetch(fullUrl, {
            method: 'GET'
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'เกิดข้อผิดพลาด'); });
                }

                const disposition = response.headers.get('Content-Disposition');
                const fileNameMatch = disposition && disposition.match(/filename="?([^"]+)"?/);
                const fileName = fileNameMatch ? fileNameMatch[1] : 'export.csv';

                return response.blob().then(blob => ({ blob, fileName }));
            })
            .then(({ blob, fileName }) => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                Swal.close();
            })
            .catch(error => {
                Swal.fire('Error', error.message, 'error');
            })
            .finally(() => {
                document.getElementById('loader').style.display = 'none';
            });
    });
}
