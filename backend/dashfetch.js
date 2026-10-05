// Dynamically determine the basePath based on the current location
const basePath = window.location.pathname.includes('/dashboard/') ? '../backend/' : 'backend/';

document.addEventListener('DOMContentLoaded', () => {
    fetchSchoolYears(); // Load school years first
    fetchSections(); // Load sections for teachers/admins
    updateQuarterOptions(); // Ensure correct quarters on load
    studfetch();

    // Automatically fetch data when any dropdown changes
    ['schoolYear', 'semester', 'quarter', 'grade', 'sectionSelect'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', fetchGrades);
    });

    // Update quarter options dynamically
    document.getElementById('semester')?.addEventListener('change', updateQuarterOptions);
});

// Fetch available school years
async function fetchSchoolYears() {
    const schoolYearSelect = document.getElementById('schoolYear');

    try {
        const response = await fetch(`${basePath}dashfetch.php?fetch=schoolYears`);
        const data = await response.json();

        if (!Array.isArray(data) || data.length === 0) {
            console.error('No school years available.');
            return;
        }

        schoolYearSelect.innerHTML = data.map(year =>
            `<option value="${year.SchoolYear}">${year.SchoolYear}</option>`
        ).join('');

        fetchGrades(); // Load grades after populating school years
    } catch (error) {
        console.error('Error fetching school years:', error);
    }
}

// Update the quarter options based on the semester
function updateQuarterOptions() {
    const semester = document.getElementById('semester')?.value;
    const quarterSelect = document.getElementById('quarter');

    quarterSelect.innerHTML = (semester === '1')
        ? `<option value="Q1">Quarter 1</option><option value="Q2">Quarter 2</option>`
        : `<option value="Q3">Quarter 3</option><option value="Q4">Quarter 4</option>`;

    fetchGrades(); // Update grades when semester changes
}

// Fetch sections
async function fetchSections() {
    const sectionSelect = document.getElementById('sectionSelect');

    try {
        const response = await fetch(`${basePath}dashfetch.php?fetch=sections`);
        const data = await response.json();

        sectionSelect.innerHTML = data.map(section =>
            `<option value="${section.SectionID}">${section.SectionName}</option>`
        ).join('');

        fetchGrades(); // Fetch grades after sections load
    } catch (error) {
        console.error('Error fetching sections:', error);
    }
}

// Fetch and display grades
async function fetchGrades() {
    const schoolYear = document.getElementById('schoolYear')?.value;
    const grade = document.getElementById('grade')?.value;
    const semester = document.getElementById('semester')?.value;
    const quarter = document.getElementById('quarter')?.value;
    const section = document.getElementById('sectionSelect')?.value;

    const table = document.getElementById('gradesTable');
    const tbody = table.querySelector('tbody');
    const thead = table.querySelector('thead');

    try {
        const url = `${basePath}dashfetch.php?schoolYear=${schoolYear}&grade=${grade}&semester=${semester}&quarter=${quarter}&section=${section}`;
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        const data = await response.json();
        tbody.innerHTML = '';

        if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="17">No data available.</td></tr>';
            return;
        }

        const renderCell = (value) => value ?? 'N/A';
        const isTeacher = data[0]?.hasOwnProperty('LastName');

        if (isTeacher) {
            // Set teacher header (editable columns then computed columns)
            thead.innerHTML = `<tr>
                <th>Last Name</th><th>Middle Name</th><th>First Name</th>
                <th>W1</th><th>W2</th><th>W3</th><th>W4</th>
                <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>
                <th>PT1</th><th>PT2</th><th>PT3</th><th>PT4</th>
                <th>QA</th>
                <th>WW Avg</th><th>PT Avg</th><th>Quarter Grade</th>
               </tr>`;
            
            data.forEach(item => {
                let rowHTML = `<tr>
                    <td>${renderCell(item.LastName)}</td>
                    <td>${renderCell(item.MiddleName)}</td>
                    <td>${renderCell(item.FirstName)}</td>`;
                
                // Editable raw fields:
                rowHTML += createEditableCells(item, ['ww1','ww2','ww3','ww4','qz1','qz2','qz3','qz4','pt1','pt2','pt3','pt4','qa']);
                
                // Append computed fields as read-only:
                rowHTML += `<td><center>${renderCell(item.ww_qz_average)}</center></td>
                            <td><center>${renderCell(item.pt_average)}</center></td>
                            <td><center>${renderCell(item.quarter_grade)}</center></td>`;
                
                rowHTML += `</tr>`;
                tbody.innerHTML += rowHTML;
            });
            
            if (window.location.pathname.includes('/dashboard/edit.php')) {
                enableEditing();
            }
        } else {
            // Render student table (read-only)
            thead.innerHTML = `<tr><th>Subject</th><th>Quarter Grade</th></tr>`;
            data.forEach(item => {
                tbody.innerHTML += `<tr>
                    <td>${renderCell(item.SubjectName)}</td>
                    <td>${renderCell(item.quarter_grade)}</td>
                 </tr>`;
            });
        }
        
        } catch (error) {
        console.error('Error fetching grades:', error);
        tbody.innerHTML = '<tr><td colspan="17">Error loading data. Please try again later.</td></tr>';
    }
}

function createEditableCells(item, fields) {
    return fields.map(field => `
        <td 
            class="editable" 
            data-lrn="${item.LRN || ''}"
            data-subject="${item.SubjectID || ''}"
            data-quarter="${item.quarter || ''}"
            data-schoolyear="${item.SchoolYear || ''}"
            data-field="${field}">
            ${item[field] ?? 'N/A'}
        </td>`
    ).join('');
}

// Helper function to get the grade value
function getGrade() {
    return document.getElementById('grade')?.value || '';
}

// Store pending changes
let pendingChanges = [];

// Store changes in pendingChanges array
function storePendingChange(lrn, field, value, subject, quarter, schoolYear) {
    const grade = getGrade(); // Fetch the grade value

    // Check if an entry already exists for this LRN, subject, quarter, and school year
    const existingChange = pendingChanges.find(change =>
        change.lrn === lrn &&
        change.subjectID === subject &&
        change.quarter === quarter &&
        change.schoolYear === schoolYear &&
        change.grade === grade
    );

    if (existingChange) {
        // Update existing entry
        existingChange[field] = value;
    } else {
        // Add a new entry with grade
        const newChange = {
            lrn,
            subjectID: subject,
            quarter,
            schoolYear,
            grade,
            [field]: value
        };
        pendingChanges.push(newChange);
    }

    console.log('Pending Changes:', pendingChanges);
}

// Enable editing on double-click (collects changes)
function enableEditing() {
    document.querySelectorAll('.editable').forEach(cell => {
        cell.addEventListener('dblclick', () => {
            const originalValue = cell.textContent.trim();
            const input = document.createElement('input');
            input.type = 'text';
            input.value = originalValue;

            cell.innerHTML = '';
            cell.appendChild(input);
            input.focus();

            // Save change only on blur or Enter key
            const saveChange = () => {
                const newValue = parseInt(input.value.trim(), 10);
                if (!isNaN(newValue) && newValue !== parseInt(originalValue, 10)) {
                    const lrn = cell.dataset.lrn;
                    const field = cell.dataset.field;
                    const subject = cell.dataset.subject;
                    const quarter = cell.dataset.quarter;
                    const schoolYear = cell.dataset.schoolyear;

                    if (lrn && field && subject && quarter && schoolYear) {
                        storePendingChange(lrn, field, newValue, subject, quarter, schoolYear);
                    } else {
                        console.error('Missing data:', { lrn, field, subject, quarter, schoolYear });
                    }
                }
                cell.textContent = newValue || 'N/A';
            };

            input.addEventListener('blur', saveChange);
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') saveChange();
            });
        });
    });
}

document.getElementById('saveGradesBtn').addEventListener('click', saveAllChanges);

// Save all pending changes when Save button is clicked
async function saveAllChanges() {
    if (pendingChanges.length === 0) {
        alert('No changes to save.');
        return;
    }

    try {
        const response = await fetch(`${basePath}bulk_update.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ changes: pendingChanges })
        });

        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Failed to save grades');

        alert('Grades updated successfully.');
        pendingChanges.length = 0; // Clear pending changes
        fetchGrades(); // Refresh the updated data
    } catch (error) {
        console.error('Error saving grades:', error);
        alert('Error saving grades. Please try again.');
    }
}

// Update a single grade immediately (used for immediate update)
async function updateGrade(lrn, field, value, subject, quarter, schoolYear) {
    try {
        const grade = getGrade(); // Ensure grade is included

        const payload = {
            lrn,
            subjectID: subject,
            quarter,
            schoolYear,
            grade, // Include grade
            [field]: parseInt(value)
        };

        console.log('Sending payload:', payload);

        const response = await fetch(`${basePath}dashupdate.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Failed to update grade');

        console.log('Grade updated successfully:', result);
        fetchGrades(); // Refresh the updated data
    } catch (error) {
        console.error('Error updating grade:', error);
        alert('Error updating grade. Please try again.');
    }
}

let fullName = "";
let fullSection = "";

// Fetch user info and store in global variables
async function studfetch() {
  try {
    const response = await fetch(`${basePath}info.php`);
    if (!response.ok) throw new Error(`Network error: ${response.statusText}`);
    const data = await response.json();
    console.log('User Info:', data);
    if (!data.success) throw new Error('Error fetching user information');
    fullName = `${data.FirstName || ''} ${data.MiddleName || ''} ${data.LastName || ''}`.trim();
    fullSection = `Grade ${data.GradeLevel || ''} ${data.Section || ''}`.trim();
  } catch (error) {
    console.error('Error fetching user data:', error);
  }
}



//code from online, jsPDF and html2canvas
// Helper function: load an image from a URL and return a Promise
function loadImage(url) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.src = url;
      img.onload = () => resolve(img);
      img.onerror = (err) => reject(err);
    });
  }
  
  function exportTableToPDF() {
    // Get the info values from the DOM
    const schoolYearVal = document.getElementById('schoolYear')?.value || '';
    const gradeVal = document.getElementById('grade')?.value || '';
    const semesterVal = document.getElementById('semester')?.value || '';
    const quarterVal = (document.getElementById('quarter')?.value || '').replace('Q', '');
    
    // Create the info block as an array of lines
    const infoLines = [
      fullName,
      `School Year ${schoolYearVal}`,
      fullSection,
      `Computer Programming`,
      `Semester ${semesterVal} Quarter ${quarterVal}`,
      ``
    ];
  
    // Set font size and line height for the info block
    const infoFontSize = 10;
    const lineHeight = infoFontSize + 4; // e.g., 16 points per line
    const infoBlockHeight = infoLines.length * lineHeight;
  
    // Get the table element
    const table = document.getElementById('gradesTable');
    if (!table) {
      alert('Table not found!');
      return;
    }
  
    // Capture the table as an image using html2canvas
    html2canvas(table).then(canvas => {
      const tableImgData = canvas.toDataURL('image/png');
      const { jsPDF } = window.jspdf;
      // Create an A4 PDF in portrait (units in points)
      const pdf = new jsPDF('p', 'pt', 'a4');
      const pdfWidth = pdf.internal.pageSize.getWidth();   // ~595.28 pt
      const pdfHeight = pdf.internal.pageSize.getHeight(); // ~841.89 pt
  
      // Fixed header and footer heights in points
      const headerMaxHeight = 355.68; // 4.94 inches
      const footerMaxHeight = 287.28; // 3.99 inches
  
      // Side margin for the table: 1 inch = 72 points on each side
      const sideMargin = 48;
      const tableAvailableWidth = pdfWidth - 1 * sideMargin;
  
      // Load header and footer images from ../backend/pdf/
      Promise.all([
        loadImage(`${basePath}pdf/header.png`),
        loadImage(`${basePath}pdf/footer.png`)
      ]).then(([headerImg, footerImg]) => {
        // --- Header Image Scaling ---
        // Scale header image to fill full width and preserve aspect ratio; cap height at headerMaxHeight.
        const computedHeaderHeight = pdfWidth * (headerImg.naturalHeight / headerImg.naturalWidth);
        const headerImgHeight = computedHeaderHeight > headerMaxHeight ? headerMaxHeight : computedHeaderHeight;
        const headerImgWidth = pdfWidth; // fill full width
  
        // --- Footer Image Scaling ---
        const computedFooterHeight = pdfWidth * (footerImg.naturalHeight / footerImg.naturalWidth);
        const footerImgHeight = computedFooterHeight > footerMaxHeight ? footerMaxHeight : computedFooterHeight;
        const footerImgWidth = pdfWidth; // fill full width
  
        // --- Calculate available vertical space for info block + table ---
        const availableHeight = pdfHeight - headerImgHeight - footerImgHeight;
        // Deduct info block height to get available height for table image
        const tableAvailableHeight = availableHeight - infoBlockHeight;
  
        // --- Scale Table Image ---
        const conversionFactor = 72 / 96; // 0.75
        const tableImgOriginalWidth = canvas.width * conversionFactor;
        const tableImgOriginalHeight = canvas.height * conversionFactor;
        // Center the table image horizontally within the page, but ensure at least sideMargin on each side
        let tableImgWidth = tableImgOriginalWidth;
        let tableImgHeight = tableImgOriginalHeight;
        if (tableImgWidth > (pdfWidth - sideMargin * 2)) {
          tableImgWidth = pdfWidth - sideMargin * 2;
          tableImgHeight = canvas.height * (tableImgWidth / canvas.width) * conversionFactor;
        }
        const tableX = (pdfWidth - tableImgWidth) / 2;
        // Position the table image below the header + info block
        const tableY = headerImgHeight + infoBlockHeight;
  
        // --- Assemble PDF ---
        // Add header image at top, spanning full width
        pdf.addImage(headerImg, 'PNG', 0, 0, headerImgWidth, headerImgHeight);
  
        // Add info block text just below the header
        pdf.setFont("helvetica", "bold"); // Set font to bold
        pdf.setFontSize(infoFontSize);
        let textY = headerImgHeight + infoFontSize; // starting y for text
        
        infoLines.forEach(line => {
          // Left align the text using the sideMargin as the x-coordinate
          pdf.text(line, sideMargin, textY, { align: "left" });
          textY += lineHeight;
        });
  
        // Add table image, centered horizontally in the available space
        pdf.addImage(tableImgData, 'PNG', tableX, tableY, tableImgWidth, tableImgHeight);
  
        // Add footer image at bottom, spanning full width
        pdf.addImage(footerImg, 'PNG', 0, pdfHeight - footerImgHeight, footerImgWidth, footerImgHeight);
  
        // Save the PDF
        pdf.save('grades.pdf');
      }).catch(error => {
        console.error('Error loading header or footer image:', error);
      });
    }).catch(error => {
      console.error('Error exporting table to PDF:', error);
    });
  }
  