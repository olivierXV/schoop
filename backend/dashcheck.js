// Set basePath globally
const basePath = window.location.pathname.includes('/dashboard/') ? '../backend/' : 'backend/';

document.addEventListener('DOMContentLoaded', function() {
    fetch(`${basePath}dashboardchecker.php`) // Use basePath here
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data); // Debugging

            // Ensure the response is valid and successful
            if (data && data.success) {
                const ul = document.querySelector('ul');

                // For teachers, apply role
                if (data.userType === 'teacher' && data.role) {
                    document.body.classList.add(data.role || 'default-role');
                    if (ul) ul.classList.add(data.role);
                }

                // For students, apply strand
                else if (data.userType === 'student' && data.strand) {
                    document.body.classList.add(data.strand || 'default-strand');
                    if (ul) ul.classList.add(data.strand);
                }

            } else {
                console.error('Error: ', data?.message || 'No role or strand found');
            }
        })
        .catch(error => console.error('Error fetching data:', error));
});
