    // Check session status using AJAX
    fetch('check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn) {
                window.location.href = "login.html"; // Redirect if not logged in
            }
        })
        .catch(error => console.error('Error:', error));