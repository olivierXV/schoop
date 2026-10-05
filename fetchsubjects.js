document.addEventListener("DOMContentLoaded", async function () {
    const subjectTable = document.getElementById("subjectTable");

    // Fetch subjects from the PHP script
    try {
        const response = await fetch("fetchsubjects.php");
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();

        if (data.error) {
            subjectTable.innerHTML = `<tr><td colspan="3">${data.error}</td></tr>`;
            console.error("Error from server: ", data.error);
            return;
        }

        // Ensure we have the current semester and subjects
        const { currentSemester, Core, "Applied/Immersion": AppliedImmersion, Specialized } = data;

        // Helper function to create an empty row
        const createEmptyRow = () => '<tr><td colspan="3">&nbsp;</td></tr>';

        // Helper function to create a subject section
        function createSection(title, subjects) {
            if (!subjects || subjects.length === 0) return "";
            let sectionHTML = `<tr><th colspan="3">${title}</th></tr>`;
            subjects.forEach(subject => {
                sectionHTML += `
                    <tr>
                        <td>${subject.SubjectName}</td>
                        <td>${subject.Q1_Grade ?? 'N/A'}</td>
                        <td>${subject.Q2_Grade ?? 'N/A'}</td>
                    </tr>`;
            });
            return sectionHTML;
        }

        // Clear existing content
        subjectTable.innerHTML = "";

        // Populate the table with sections and add empty rows between sections
        const sections = [
            { title: 'Core Subjects', data: Core },
            { title: 'Applied/Immersion Subjects', data: AppliedImmersion },
            { title: 'Specialized Subjects', data: Specialized }
        ];

        sections.forEach((section, index) => {
            subjectTable.innerHTML += createSection(section.title, section.data);
            // Add an empty row **only if it's not the last section**
            if (index < sections.length - 1) {
                subjectTable.innerHTML += createEmptyRow();
            }
        });

    } catch (error) {
        console.error("Fetch error:", error);
        subjectTable.innerHTML = `<tr><td colspan="3">Failed to load subjects.</td></tr>`;
    }
});
