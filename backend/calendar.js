    // Determine the correct path based on the location
    const basePath = window.location.pathname.includes('/dashboard/')
        ? '../backend/'
        : 'backend/';

// Load calendar data and display announcements and holidays
async function loadCalendarData() {
    try {
        const response = await fetch(`${basePath}announcements.php`);
        if (!response.ok) throw new Error('Network response was not OK');
        
        const data = await response.json();
        const announcementDates = processDates(data.announcements);
        const holidayDates = processDates(data.holidays);

        // Display announcements and holidays in the #announcements section
        displayAllAnnouncements(data.announcements, data.holidays);

        // Initialize the calendar after fetching data
        initializeCalendar(announcementDates, holidayDates);
    } catch (error) {
        console.error('Error loading calendar data:', error);
        document.getElementById("announcements").innerHTML = '<b>Announcements:</b><br>Unable to load announcements.';
    }
}

// Helper: Convert date ranges into a usable object for the calendar
function processDates(dataArray) {
    const dates = {};

    dataArray.forEach(item => {
        const startDate = new Date(item.start_date);
        const endDate = new Date(item.end_date || item.start_date);
        
        const current = new Date(startDate);
        while (current <= endDate) {
            const month = current.getMonth();
            const day = current.getDate();

            if (!dates[month]) {
                dates[month] = {};
            }

            // Add or append the announcement/holiday to the date
            dates[month][day] = dates[month][day]
                ? `${dates[month][day]} | ${item.text}`
                : item.text;

            current.setDate(current.getDate() + 1); // Move to the next day
        }
    });

    return dates;
}

// Display announcements and holidays in the announcements section
function displayAllAnnouncements(announcements, holidays) {
    const announcementsElement = document.getElementById("announcements");
    if (!announcementsElement) return;

    if (!announcements.length && !holidays.length) {
        announcementsElement.innerHTML = '<b>Announcements:</b><br>No upcoming announcements or holidays.';
        return;
    }

    // Combine and sort by start_date
    const combined = [...announcements, ...holidays].sort((a, b) => new Date(a.start_date) - new Date(b.start_date));

    const announcementHTML = combined.map(item => {
        const startDate = new Date(item.start_date);
        const endDate = item.end_date ? new Date(item.end_date) : startDate;

        const monthName = startDate.toLocaleString("default", { month: "long" });

        // If the start and end date are the same, show only once
        if (startDate.toDateString() === endDate.toDateString()) {
            return `${monthName} ${startDate.getDate()} - ${item.text}`;
        } else {
            return `${monthName} ${startDate.getDate()} to ${endDate.getDate()} - ${item.text}`;
        }
    }).join('<br>');

    announcementsElement.innerHTML = `<b>Announcements:</b><br>${announcementHTML}`;
}

// Initialize the calendar with announcements and holidays
function initializeCalendar(announcementDates, holidayDates) {
    const calendarDays = document.getElementById("calendarDays");
    const monthYear = document.getElementById("monthYear");
    const prevMonthBtn = document.getElementById("prevMonth");
    const nextMonthBtn = document.getElementById("nextMonth");

    let currentDate = new Date();

    function renderCalendar() {
        calendarDays.innerHTML = "";
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();

        monthYear.textContent = currentDate.toLocaleString("default", { month: "long", year: "numeric" });

        // Create empty cells for padding before the first day
        for (let i = 0; i < firstDay; i++) {
            calendarDays.appendChild(document.createElement("div"));
        }

        // Populate calendar days
        for (let day = 1; day <= lastDate; day++) {
            const dayDiv = document.createElement("div");
            dayDiv.textContent = day;

            // Highlight today's date
            if (day === new Date().getDate() && month === new Date().getMonth() && year === new Date().getFullYear()) {
                dayDiv.classList.add("today");
            }

            // Check for announcements
            if (announcementDates[month] && announcementDates[month][day]) {
                dayDiv.classList.add("announcement");
                dayDiv.title = announcementDates[month][day];
            }

            // Check for holidays
            if (holidayDates[month] && holidayDates[month][day]) {
                dayDiv.classList.add("holiday");
                dayDiv.title = holidayDates[month][day];
            }

            calendarDays.appendChild(dayDiv);
        }
    }

    // Navigate to previous month
    prevMonthBtn.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    // Navigate to next month
    nextMonthBtn.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    renderCalendar(); // Initial render
}

// Load data when the document is ready
document.addEventListener('DOMContentLoaded', loadCalendarData);
