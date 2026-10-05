document.addEventListener('DOMContentLoaded', () => {
    fetchSchoolYears();  // Load school years first
    fetchSections();     // Load sections for teachers/admins
    updateQuarterOptions(); // Ensure correct quarters on load

    // Automatically fetch data when any dropdown changes
    ['schoolYear', 'semester', 'quarter', 'grade', 'sectionSelect'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', fetchGrades);
    });

    // Update quarter options dynamically
    document.getElementById('semester')?.addEventListener('change', updateQuarterOptions);
});

// Fetch available school years from the server
async function fetchSchoolYears() {
    const schoolYearSelect = document.getElementById('schoolYear');

    try {
        const response = await fetch('dashfetch.php?fetch=schoolYears');
        const data = await response.json();

        if (!Array.isArray(data) || data.length === 0) {
            console.error('No school years available.');
            return;
        }

        schoolYearSelect.innerHTML = data.map(year =>
            `<option value="${year.SchoolYear}">${year.SchoolYear}</option>`
        ).join('');

        fetchGrades(); // Load grades after school years are populated
    } catch (error) {
        console.error('Error fetching school years:', error);
    }
}

// Dynamically update the quarter options based on semester
function updateQuarterOptions() {
    const semester = document.getElementById('semester')?.value;
    const quarterSelect = document.getElementById('quarter');

    quarterSelect.innerHTML = (semester === '1')
        ? `<option value="Q1">Quarter 1</option><option value="Q2">Quarter 2</option>`
        : `<option value="Q3">Quarter 3</option><option value="Q4">Quarter 4</option>`;

    fetchGrades(); // Update grades when semester changes
}

// Fetch sections for teachers/admins
async function fetchSections() {
    const sectionSelect = document.getElementById('sectionSelect');

    try {
        const response = await fetch('dashfetch.php?fetch=sections');
        const data = await response.json();

        sectionSelect.innerHTML = 
            data.map(section =>
                `<option value="${section.SectionID}">${section.SectionName}</option>`
            ).join(''); fetchGrades()
    } catch (error) {
        console.error('Error fetching sections:', error);
    }
}

// Fetch and display grade data with all filters
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
        const url = `dashfetch.php?schoolYear=${schoolYear}&grade=${grade}&semester=${semester}&quarter=${quarter}&section=${section}`;
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

        thead.innerHTML = isTeacher
            ? `<tr>
                <th>Last Name</th><th>Middle Name</th><th>First Name</th>
                <th>WW1</th><th>WW2</th><th>WW3</th><th>WW4</th>
                <th>QZ1</th><th>QZ2</th><th>QZ3</th><th>QZ4</th>
                <th>WW Avg</th><th>PT1</th><th>PT2</th><th>PT3</th><th>PT4</th>
                <th>PT Avg</th><th>QA</th><th>GWA</th>
               </tr>`
            : `<tr><th>Subject</th><th>GWA</th></tr>`;

        data.forEach(item => {
            const row = isTeacher
                ? `<tr>
                        <td>${renderCell(item.LastName)}</td>
                        <td>${renderCell(item.MiddleName)}</td>
                        <td>${renderCell(item.FirstName)}</td>
                        <td style="text-align: center;">${renderCell(item.ww1)}</td>
                        <td style="text-align: center;">${renderCell(item.ww2)}</td>
                        <td style="text-align: center;">${renderCell(item.ww3)}</td>
                        <td style="text-align: center;">${renderCell(item.ww4)}</td>
                        <td style="text-align: center;">${renderCell(item.qz1)}</td>
                        <td style="text-align: center;">${renderCell(item.qz2)}</td>
                        <td style="text-align: center;">${renderCell(item.qz3)}</td>
                        <td style="text-align: center;">${renderCell(item.qz4)}</td>
                        <td style="text-align: center;">${renderCell(item.ww_qz_average)}</td>
                        <td style="text-align: center;">${renderCell(item.pt1)}</td>
                        <td style="text-align: center;">${renderCell(item.pt2)}</td>
                        <td style="text-align: center;">${renderCell(item.pt3)}</td>
                        <td style="text-align: center;">${renderCell(item.pt4)}</td>
                        <td style="text-align: center;">${renderCell(item.pt_average)}</td>
                        <td style="text-align: center;">${renderCell(item.qa)}</td>
                        <td style="text-align: center;">${renderCell(item.quarter_grade)}</td>
                   </tr>`
                : `<tr>
                        <td>${renderCell(item.SubjectName)}</td>
                        <td>${renderCell(item.quarter_grade)}</td>
                   </tr>`;

            tbody.innerHTML += row;
        });
    } catch (error) {
        console.error('Error fetching grades:', error);
        tbody.innerHTML = '<tr><td colspan="17">Error loading data. Please try again later.</td></tr>';
    }
}
