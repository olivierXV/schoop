document.addEventListener('DOMContentLoaded', function() {
    fetch('dashboardchecker.php')
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the response to check its structure
            
            // Check if the response is successful and contains the necessary data
            if (data && data.success) {
                // Handle teacher
                if (data.userType === 'teacher' && data.role) {
                    document.body.classList.add(data.role || 'default-role');
                    // Add role to the UL class
                    const ul = document.querySelector('ul');
                    if (ul) {
                        ul.classList.add(data.role);
                    }
                }
                
                // Handle student
                if (data.userType === 'student' && data.strand) {
                    document.body.classList.add(data.strand || 'default-strand');
                    // Add strand to the UL class
                    const ul = document.querySelector('ul');
                    if (ul) {
                        ul.classList.add(data.strand);
                    }
                }
                    
            } else {
                console.error('Error: ', data.message || 'No role or strand found');
            }
        })
        .catch(error => console.error('Error fetching data:', error));
});
