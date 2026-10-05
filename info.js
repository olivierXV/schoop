document.addEventListener('DOMContentLoaded', () => {
    // Helper function to safely set text content
    const setText = (id, text) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = text;
        }
    };

    // Fetch user information
    const fetchUserInfo = async () => {
        try {
            const response = await fetch('info.php');
            if (!response.ok) throw new Error(`Network error: ${response.statusText}`);

            const data = await response.json();
            console.log('User Info:', data);

            if (data.success) {
                document.getElementById("announcements").innerHTML = "<b>Announcements:</b><br>March 7 - Research Paper deadline<br>April 9 - Day of Valor<br>April 7-8 - Fourth Quarter Examination"; // Use innerHTML, not textContent
                if (Array.isArray(data.data) && data.data.length > 0) {
                    // Handle teacher/admin data
                    const teacher = data.data[0]; // Use the first record for basic details
    
                    const fullName = `${teacher.FirstName || ''} ${teacher.MiddleName || ''} ${teacher.LastName || ''}`.trim();
                    const subjectList = data.data.map(item => item.SubjectName).join(', ');
                    const sectionList = data.data.map(item => item.SectionName).join(', ');
    
                    // Populate teacher/admin information
                    setText('user-type', 'Teacher/Admin');
                    setText('full-name', fullName || 'N/A');
                    setText('lrn', "Teacher" || 'N/A');
                    setText('full-section', subjectList + "\n" + teacher.SectionName || 'No subjects assigned');
                    setText('teacher-id', teacher.TeacherID || 'N/A');
                    setText('first-name', teacher.FirstName || 'N/A');
                    setText('middle-name', teacher.MiddleName || 'N/A');
                    setText('last-name', teacher.LastName || 'N/A');
                    setText('email', teacher.Email || 'N/A');
                    setText('role', teacher.Role || 'N/A');
                } else if (data.LRN) {
                    // Handle student data
                    const fullName = `${data.FirstName || ''} ${data.MiddleName || ''} ${data.LastName || ''}`.trim();
                    const fullSection = `Grade ${data.GradeLevel || ''} ${data.Section || ''}`.trim();
    
                    setText('user-type', 'Student');
                    setText('full-name', fullName || 'N/A');
                    setText('lrn', data.LRN || 'N/A');
                    setText('first-name', data.FirstName || 'N/A');
                    setText('middle-name', data.MiddleName || 'N/A');
                    setText('last-name', data.LastName || 'N/A');
                    setText('gradelevel', `Grade ${data.GradeLevel || 'N/A'}`);
                    setText('section', data.Section || 'N/A');
                    setText('strand', data.Strand || 'N/A');
    
                } else {
                    console.error('Unknown user type or missing data.');
                }    
            } else {
                console.error('Error:', data.message);
            }
        } catch (error) {
            console.error('Error fetching user data:', error);
        }
    };

    // Fetch missing grades
    const fetchMissingGrades = async () => {
        try {
            const response = await fetch('missing.php');
            if (!response.ok) throw new Error(`Network error: ${response.statusText}`);

            const data = await response.json();
            console.log('Missing Grades:', data);

            const announcementsElement = document.getElementById('announcements');

            // If the announcements section doesn't exist, exit without errors
            if (!announcementsElement) return;

            if (!data || data.length === 0) {
                announcementsElement.innerHTML = '<p>You have no missing grades.</p>';
                return;
            }

            const subjects = {};
            data.forEach(grade => {
                const subjectName = grade.SubjectName;

                if (!subjects[subjectName]) {
                    subjects[subjectName] = { noGrades: false };
                }

                // Check for no grades (all grade fields are null)
                if (grade.quarter_grade == null && grade.qa == null &&
                    grade.ww1 == null && grade.ww2 == null && grade.ww3 == null && grade.ww4 == null &&
                    grade.qz1 == null && grade.qz2 == null && grade.qz3 == null && grade.qz4 == null &&
                    grade.pt1 == null && grade.pt2 == null && grade.pt3 == null && grade.pt4 == null) {
                    subjects[subjectName].noGrades = true;
                }
            });

            let announcements = '';
            for (const [subjectName, status] of Object.entries(subjects)) {
                if (status.noGrades) {
                    announcements += `<p>You have no grades in ${subjectName}.</p>`;
                }
            }

            // Display the announcements or a default message
            announcementsElement.innerHTML = announcements || '<p>You have no missing grades.</p>';

            console.log('Subjects Status:', subjects);
            console.log('Generated Announcements:', announcements);
        } catch (error) {
            console.error('Error fetching missing grades:', error);

            // Safely handle missing announcements element in case it's not present
            const announcementsElement = document.getElementById('announcements');
            if (announcementsElement) {
                announcementsElement.innerHTML = '<p>Error loading announcements.</p>';
            }
        }
    };
    fetchMissingGrades();
    fetchUserInfo();
});
