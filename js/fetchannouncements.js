document.addEventListener('DOMContentLoaded', async function() {
    // Adjust basePath based on folder structure:
    const basePath = window.location.pathname.includes('/dashboard/')
        ? '../backend/'
        : 'backend/';

    try {
        const response = await fetch(`${basePath}announcements.php`);
        if (!response.ok) throw new Error('Network response was not OK');
        const data = await response.json();
        displayAnnouncements(data.announcements, data.holidays);
    } catch (error) {
        console.error('Error fetching announcements:', error);
        const annElem = document.getElementById("announcements");
        if (annElem) {
            annElem.innerHTML = '<b>Announcements:</b> Unable to load announcements.';
        }
    }
});

function displayAnnouncements(announcements, holidays) {
    // Target the <a id="announcements"> element
    const annElem = document.getElementById("announcements");
    if (!annElem) return;

    // Check if there are no announcements or holidays
    if (!announcements.length && !holidays.length) {
        annElem.textContent = "No upcoming announcements or holidays.";
        return;
    }

    // Combine announcements and holidays, then sort by start_date
    const combined = [...announcements, ...holidays].sort((a, b) => new Date(a.start_date) - new Date(b.start_date));

    // Generate HTML output for each announcement
    const annHTML = combined.map(item => {
        const startDate = new Date(item.start_date);
        const endDate = item.end_date ? new Date(item.end_date) : startDate;
        const monthName = startDate.toLocaleString("default", { month: "long" });

        // Format date range: if same day, show one date; otherwise, show range.
        const dateRange = (startDate.toDateString() === endDate.toDateString())
            ? `${monthName} ${startDate.getDate()}`
            : `${monthName} ${startDate.getDate()} to ${endDate.getDate()}`;
        
        return `${dateRange} - ${item.text}`;
    }).join("<br>");

    // Set the innerHTML of the anchor element
    annElem.innerHTML = `<b>Announcements:</b><br>${annHTML}`;
}
