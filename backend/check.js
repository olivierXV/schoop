(() => {
    // Determine the correct path based on the location
    const basePath = window.location.pathname.includes('/dashboard/')
        ? '../'
        : '';

    // Check session status using AJAX
    fetch(`${basePath}check_session.php`) // ✅ Corrected string interpolation
    .then(response => response.json())
    .then(data => {
        if (!data.loggedIn) {
            window.location.href = `${basePath}login.html`; // ✅ Corrected string interpolation
        }
    })
    .catch(error => console.error('Error:', error));
})();
