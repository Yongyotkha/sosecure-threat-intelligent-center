const exportCSV = (type) => {
    const eventName = document.getElementById('event_name').value.trim();
    const type_ = type.value;

    const dateRange = $('#event_date').data('daterangepicker');
    const startDate = dateRange.startDate.format('YYYY-MM-DD');
    const endDate = dateRange.endDate.format('YYYY-MM-DD');

    const publishedBtn = document.querySelector('#groupby-published .btn.active');
    const published_ = publishedBtn ? publishedBtn.value : "";
    const published = published_ == 1 ? 1 : published_ == 2 ? 0 : "";

    // alert(published);

    const checkedInputs = document.querySelectorAll('.check_rss_new_id:checked');
    const selectedPulseIds = Array.from(checkedInputs).map(input => input.value);

    // alert(published);

    const queryParams = new URLSearchParams();
    if (eventName) queryParams.append('event_name', eventName);
    if (published !== "") queryParams.append('published', published);
    if (selectedPulseIds.length > 0) queryParams.append('pulse_id', selectedPulseIds.join(','));
    queryParams.append('start_date', startDate);
    queryParams.append('end_date', endDate);
    queryParams.append('type', type_);
    // queryParams.append('ispublished', published);

    const fullUrl = `${exportBaseUrl}?${queryParams.toString()}`;

    $show_status_pubished = '';
    if (published_ == '1') {
        $show_status_pubished = 'Published';
    } else if (published_ == '2') {
        $show_status_pubished = 'Unpublished';
    } else {
        $show_status_pubished = 'All';
    }

    // alert($show_status_pubished);

    if (type_ == 1) {
        Swal.fire({
            title: 'Do you want to export ?',
            html: 'Do you want to export Events <br> from '
                + '<strong>'
                + moment(startDate, 'YYYY-MM-DD').format('DD-MM-YYYY')
                + ' to '
                + moment(endDate, 'YYYY-MM-DD').format('DD-MM-YYYY')
                + ' ?'
                + '</strong>'
                + '<br>'
                + 'Published Status : <strong>' + $show_status_pubished + '</strong>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Export',
            cancelButtonText: 'Cancel',
            reverseButtons: false,
            width: 400
        }).then((result) => {
            if (!result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'You have canceled the export',
                    text: 'You have canceled the export Event.',
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

            fetch(fullUrl)
                .then(async response => {
                    const contentType = response.headers.get("Content-Type");

                    if (!response.ok) {
                        if (contentType && contentType.includes("application/json")) {
                            const err = await response.json();
                            throw new Error(err.message || "Create file failed");
                        } else {
                            const text = await response.text();
                            throw new Error("Server error:\n" + text.slice(0, 200));
                        }
                    }

                    if (!contentType || !contentType.includes("text/csv")) {
                        const text = await response.text();
                        throw new Error(text.slice(0, 200));
                    }

                    const disposition = response.headers.get('Content-Disposition');
                    const fileNameMatch = disposition && disposition.match(/filename="?([^"]+)"?/);
                    const fileName = fileNameMatch ? fileNameMatch[1] : 'export.csv';

                    const blob = await response.blob();
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
                    let errMsg = error.message;

                    try {
                        // ถ้า error.message เป็น JSON ก็ parse แล้วดึง message
                        const parsed = JSON.parse(error.message);
                        if (parsed.message) {
                            errMsg = parsed.message;
                        }
                    } catch (e) {
                        // ถ้าไม่ใช่ JSON ก็ใช้ข้อความเดิม
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: `"${errMsg}"`, 
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        width: 600,
                        customClass: {
                            title: 'swal2-title-lg' // ใช้ class กำหนดฟอนต์ใหญ่
                        }
                    });
                });

        });
    } else if (type_ == 2) {
        Swal.fire({
            title: 'Do you want to export?',
            html: 'Do you want to export Event and Attributes <br> from '
                + '<strong>'
                + moment(startDate, 'YYYY-MM-DD').format('DD-MM-YYYY')
                + ' to '
                + moment(endDate, 'YYYY-MM-DD').format('DD-MM-YYYY')
                + ' ?'
                + '</strong>'
                + '<br>'
                + 'Published Status : <strong>' + $show_status_pubished + '</strong>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Export',
            cancelButtonText: 'Cancel',
            reverseButtons: false,
            width: 450
        }).then((result) => {
            if (!result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'You have canceled the export',
                    text: 'You have canceled the export Event and Attributes.',
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

            fetch(fullUrl)
                .then(async response => {
                    const contentType = response.headers.get("Content-Type");

                    if (!response.ok) {
                        if (contentType && contentType.includes("application/json")) {
                            const err = await response.json();
                            throw new Error(err.message || "Create file failed");
                        } else {
                            const text = await response.text();
                            throw new Error("Server error:\n" + text.slice(0, 200));
                        }
                    }

                    if (!contentType || !contentType.includes("text/csv")) {
                        const text = await response.text();
                        throw new Error(text.slice(0, 200));
                    }

                    const disposition = response.headers.get('Content-Disposition');
                    const fileNameMatch = disposition && disposition.match(/filename="?([^"]+)"?/);
                    const fileName = fileNameMatch ? fileNameMatch[1] : 'export.csv';

                    const blob = await response.blob();
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
                    let errMsg = error.message;

                    try {
                        // ถ้า error.message เป็น JSON ก็ parse แล้วดึง message
                        const parsed = JSON.parse(error.message);
                        if (parsed.message) {
                            errMsg = parsed.message;
                        }
                    } catch (e) {
                        // ถ้าไม่ใช่ JSON ก็ใช้ข้อความเดิม
                    }
                    Swal.fire({
                        icon: 'warning',
                        title: `"${errMsg}"`,
                        width: 600,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        customClass: {
                            title: 'swal2-title-lg' // ใช้ class กำหนดฟอนต์ใหญ่
                        }
                    });
                });
        });
    }

};

// const importCSV = () => {
//     const importType = document.getElementById('import-type').value;
//     const fileInput = document.getElementById('fileInput');
//     const file = fileInput.files[0];

//     $('#import-modal').modal('hide');



//     if (importType === "0") {
//         Swal.fire('Missing Type', 'Please select the type of import.', 'error');
//         return;
//     }

//     if (!file) {
//         Swal.fire('Missing File', 'Please select a CSV file to import.', 'error');
//         return;
//     }

//     const formData = new FormData();
//     formData.append('file', file);
//     formData.append('import_type', importType);

//     Swal.fire({
//         title: 'Uploading...',
//         text: 'Importing your CSV file. Please wait.',
//         allowOutsideClick: false,
//         allowEscapeKey: false,
//         didOpen: () => {
//             Swal.showLoading();
//         }
//     });
//     // alert("importBaseUrl:", importBaseUrl);
//     console.log('importBaseUrl:', importBaseUrl);



//     fetch(importBaseUrl, {
//         method: 'POST',
//         body: formData,
//         headers: {
//             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
//         }
//     })
//         .then(async response => {
//             Swal.close();

//             const contentType = response.headers.get("Content-Type");
//             const res = contentType.includes("application/json") ? await response.json() : {};

//             if (!response.ok || !res.success) {
//                 const msg = res.message || "Import failed";
//                 throw new Error(msg);
//             }

//             const successText = res.success_list?.length
//                 ? `<b>✔️ Success (${res.success_list.length})</b><br>${res.success_list.join('<br>')}<br><br>` : '';
//             const errorText = res.error_list?.length
//                 ? `<b>❌ Failed (${res.error_list.length})</b><br>${res.error_list.join('<br>')}` : '';

//             Swal.fire({
//                 icon: res.error_list?.length ? 'warning' : 'success',
//                 title: 'Import Result',
//                 html: successText + errorText,
//                 width: 600
//             });
//         })
//         .catch(error => {
//             Swal.close();
//             Swal.fire('Import Failed', error.message, 'error');
//         });
// };

function clearFileInput() {
    const fi = document.getElementById('fileInput');
    if (!fi) return;
    fi.value = '';
    fi.dispatchEvent(new Event('change', {  // เผื่อมีโค้ด/ปลั๊กอินฟัง event นี้อยู่
        bubbles: true
    }));
}

const importCSV = () => {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];

    const selectedType = document.querySelector('input[name="import_type"]:checked').value;
    // alert("Selected import type: " + selectedType);
    console.log(fileInput.files);


    if (selectedType === '') {
        Swal.fire('Missing Type', 'Please select the type of import.', 'error');
        return;
    }

    if (!file) {
        Swal.fire('Missing File', 'Please select a CSV file to import.', 'error');
        return;
    }

    $('#import-modal').modal('hide');

    const formData = new FormData();
    formData.append('file', file);
    formData.append('import_type', selectedType);


    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Importing...',
        showConfirmButton: false,
        showCloseButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const renderLimitedList = (items, label) => {
        const limit = 20;
        const first = items.slice(0, limit).join('<br>');
        const rest = items.slice(limit).join('<br>');

        if (items.length <= limit) return `<b>${label} (${items.length})</b><br>${first}`;

        const moreId = label.toLowerCase().replace(/\W+/g, '') + '-more';
        return `
            <b>${label} (${items.length})</b><br>
            ${first}<br>
            <span id="${moreId}" style="display:none;">${rest}</span>
            <a href="javascript:void(0);" onclick="document.getElementById('${moreId}').style.display='inline'; this.style.display='none';">Show more...</a>
        `;
    };

    fetch(importBaseUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(async response => {
            const contentType = response.headers.get("Content-Type");
            const res = contentType.includes("application/json") ? await response.json() : {};

            // console.log("DEBUG: Status", response.status);
            // console.log("DEBUG: Content-Type", contentType);
            // console.log("DEBUG: Response JSON", res);

            if (!response.ok || !res.success) {
                const msg = res.message || "Import failed";
                throw new Error(msg);
            }
            // alert(res.message);

            const html = renderResultTable(res.success_list, res.error_list, res.total_rows);

            Swal.fire({
                icon: res.error_list?.length ? 'warning' : 'success',
                title: 'Import Result',
                html,
                width: 1000,
                confirmButtonText: 'OK',
                showCloseButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                
            });

        })
        .catch(error => {
            Swal.fire('Import Failed', error.message, 'error');
        })
        .finally(() => {
            clearFileInput();
        });
};

const renderResultTable = (successList , errorList = [], total_rows = []) => {


// alert(message);
    let html = `
      <div style="max-height:300px; overflow-y:auto; text-align:center;">
        <p>
          <span style="color:green;"><i class="fas fa-check-circle"></i> Success (${successList})</span> &nbsp; 
          <span style="color:red;"><i class="fas fa-times-circle"></i> Failed (${errorList.length})</span> &nbsp;
          <span style="color:blue;"><i class="fa fa-bars"></i> Total from file (${total_rows})</span>
        </p>
        
    `;

    if (successList != 0 && errorList.length === 0) {
        html += `
        <br>
        <tr>
            <td colspan="4" style="text-align:center;">
                <h3 style="color:green;">
                    <strong> All data imported successfully</strong>
                </h3>
            </td>
        </tr>
        <br>
        `;
        return html;
    }
    

    



    if (errorList.length > 0) {
        html += `
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:center; padding: 6px;">Status</th>
              <th style="text-align:center; padding: 6px;">ID</th>
              <th style="text-align:center; padding: 6px;">Name</th>
              <th style="text-align:center; padding: 6px;">Detail</th>
            </tr>
          </thead>
          <tbody>
    `;
    }
    errorList.forEach(err => {
        if (typeof err === 'string') {
            html += `
        <tr>
            <td style="text-align:center;"><i class="fas fa-times-circle text-danger"></i></td>
            <td style="text-align:left;">-</td>
            <td style="text-align:left;">-</td>
            <td style="text-align:left;">${err}</td>
        </tr>`;
        } else {
            const shortName = err.name?.length > 50 ? err.name.slice(0, 55) + '…' : err.name;
            html += `
        <tr>
            <td style="text-align:center;"><i class="fas fa-times-circle text-danger"></i></td>
            <td style="text-align:left;">${err.id}</td>
            <td style="text-align:left;" title="${err.name}">${shortName}</td>
            <td style="text-align:left;">${err.reason}</td>
        </tr>`;
        }

    });
    html += `
          </tbody>
        </table>
      </div>
    `;
    return html;
};







