// 1. Define API URL (Aureliya runs on Port 8002)
const API_URL = 'http://localhost:8002/api/aureliya/properties';

// Global variable para ma-store ang fetched properties
let allProperties = []; 

// 2. Fetch Data Function
async function loadProperties() {
    try {
        const response = await fetch(API_URL);
        
        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }

        allProperties = await response.json(); // Save data to global var
        console.log("Loaded Properties:", allProperties); // Check console for data

        displayProperties(allProperties);

    } catch (error) {
        console.error("Failed to fetch properties:", error);
        // Optional: Show error in UI
        const container = document.getElementById('airbnbGrid');
        if (container) container.innerHTML = `<p style="color:red;">Error loading data. Is the server running?</p>`;
    }
}

// 3. Display Cards Function
function displayProperties(properties) {
    // Targetin yung ID na "airbnbGrid" na nasa HTML mo
    const container = document.getElementById('airbnbGrid');
    
    if (!container) {
        console.error("Error: Element with ID 'airbnbGrid' not found!");
        return;
    }

    container.innerHTML = ''; // Clear hardcoded content

    properties.forEach((property, index) => {
        // Handle photos (Check kung array, string, o wala)
        let imageSrc = 'assets/default.jpg'; // Fallback image
        if (property.photos) {
            // Kung JSON string ang photos, i-parse muna
            try {
                const photos = typeof property.photos === 'string' ? JSON.parse(property.photos) : property.photos;
                if (Array.isArray(photos) && photos.length > 0) {
                    imageSrc = photos[0]; // Kunin ang unang picture
                }
            } catch (e) { console.log("Photo parse error", e); }
        }

        // Create Card HTML
        const card = document.createElement('div');
        card.className = 'airbnb-card';
        card.setAttribute('data-index', index); // Save index for click event
        
        card.innerHTML = `
            <img src="${imageSrc}" alt="${property.title}" onerror="this.src='assets/airbnb1.jpg'">
            <p>${property.title}</p>
            <small>₱${property.price_per_night} / night</small>
        `;

        // Add Click Event per card
        card.addEventListener('click', () => showDetails(index));

        container.appendChild(card);
    });
}

// 4. Show Details Function (Replaces your old showDetails)
function showDetails(index) {
    const panel = document.getElementById("detailsPanel");
    const selected = allProperties[index];

    if (!selected) return;

    // Handle Image for details view
    let imageSrc = 'assets/default.jpg';
    if (selected.photos) {
        try {
            const photos = typeof selected.photos === 'string' ? JSON.parse(selected.photos) : selected.photos;
            if (Array.isArray(photos) && photos.length > 0) imageSrc = photos[0];
        } catch (e) {}
    }

    panel.innerHTML = `
        <img src="${imageSrc}" alt="${selected.title}" class="detail-image" onerror="this.src='assets/airbnb1.jpg'" />
        <div class="detail-content">
            <h2>${selected.title}</h2>
            <p>${selected.description || 'No description available.'}</p>
            <p><strong>Location:</strong> ${selected.city}, ${selected.division || ''}</p>
            <p><strong>Price:</strong> ₱${selected.price_per_night} / night</p>
            <p><strong>Guests:</strong> Up to ${selected.max_guests} people</p>
            <button class="book-btn" onclick="alert('Booking feature coming soon!')">Book Now</button>
        </div>
    `;
}

// Start loading when page is ready
document.addEventListener('DOMContentLoaded', loadProperties);