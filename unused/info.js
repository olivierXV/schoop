// Fetch missing grades
const fetchMissingGrades = async () => {
    try {
        const response = await fetch('missing.php');
        if (!response.ok) throw new Error(`Network error: ${response.statusText}`);

        const data = await response.json();
        console.log('Missing Grades:', data);

        const announcementsElement = document.getElementById('announcements');

        if (!data || data.length === 0) {
            announcementsElement.innerHTML = '<p>You have no missing grades.</p>';
            return;
        }

        const subjects = {};
        data.forEach(grade => {
            const subjectName = grade.SubjectName;

            if (!subjects[subjectName]) {
                subjects[subjectName] = { missingWorks: false, noGrades: false };
            }

            // Check for missing works
            if ([grade.ww1, grade.ww2, grade.ww3, grade.ww4,
                 grade.qz1, grade.qz2, grade.qz3, grade.qz4,
                 grade.pt1, grade.pt2, grade.pt3, grade.pt4].some(value => value == null)) {
                subjects[subjectName].missingWorks = true;
            }

            // Check for no grades
            if (grade.quarter_grade == null && grade.qa == null &&
                grade.ww1 == null && grade.ww2 == null && grade.ww3 == null && grade.ww4 == null &&
                grade.qz1 == null && grade.qz2 == null && grade.qz3 == null && grade.qz4 == null &&
                grade.pt1 == null && grade.pt2 == null && grade.pt3 == null && grade.pt4 == null) {
                subjects[subjectName].noGrades = true;
            }
        });

        let announcements = '';
        for (const [subjectName, status] of Object.entries(subjects)) {
            if (status.missingWorks) {
                announcements += `<p>You have missing works in ${subjectName}.</p>`;
            }
            if (status.noGrades) {
                announcements += `<p>You have no grades in ${subjectName}.</p>`;
            }
        }

        // Handle no announcements case
        announcementsElement.innerHTML = announcements || '<p>You have no missing grades.</p>';

        console.log('Subjects Status:', subjects);
        console.log('Generated Announcements:', announcements);
    } catch (error) {
        console.error('Error fetching missing grades:', error);
        document.getElementById('announcements').innerHTML = '<p>Error loading announcements.</p>';
    }
};

// Call the function to fetch and display missing grades
fetchMissingGrades();