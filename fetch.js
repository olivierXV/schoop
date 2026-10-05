// Wait until DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    const sectionSelect = document.getElementById('sectionSelect');

    // Retrieve user role from the body class
    const userRole = document.body.classList;

    // Hide the section dropdown if the user is NOT a teacher or admin
    if (!userRole.contains('teacher') && !userRole.contains('admin')) {
        sectionSelect.style.display = 'none';
    } else {
        fetchSections(); // Load sections if the user is a teacher or admin
    }
});