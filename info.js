fetch('info.php')
    .then(response => response.json())
    .then(data => {
        console.log(data); // Check the data structure

        if (data.success) {
            // Check if all required fields exist in the response before using them
            const setText = (id, text) => {
                const element = document.getElementById(id);
                if (element) element.textContent = text; // Update <a> text
            };

            // Safely construct the full name and section
            const fullName = `${data.FirstName || ''} ${data.MiddleName || ''} ${data.LastName || ''}`.trim();
            const fullsection = `${data.Strand || ''} ${data.Grade || ''} ${data.Section || ''}`.trim();

            // Update the UI elements with the fetched data
            setText('lrn', data.LRN || 'N/A');
            setText('full-name', fullName);
            setText('full-section', fullsection);
            setText('first-name', data.FirstName || 'N/A');
            setText('middle-name', data.MiddleName || 'N/A');
            setText('last-name', data.LastName || 'N/A');
            setText('grade', "Grade " + (data.Grade || 'N/A'));
            setText('section', data.Section || 'N/A');
            setText('strand', data.Strand || 'N/A');
        }
    })
    .catch(error => {
        console.error('Error fetching user data:', error);
    });
