const BASE_URL = "backend/aureliya/"

// Render cards
function renderProperties(list) {
    const grid = document.getElementById("airbnbGrid");
    grid.innerHTML = "";

    list.forEach((p,i) => {
        const card = document.createElement("div");
        card.classList.add("airbnb-card");
        card.innerHTML = `<img src="${p.imgSrc}" /><p>${p.title}</p>`;
        grid.appendChild(card);

        card.addEventListener("click", () => showDetails(i, list));
    });
}

// Show details panel
function showDetails(index, list) {
    const p = list[index];
    const panel = document.getElementById("detailsPanel");
    panel.innerHTML = `
        <img src="${p.imgSrc}" alt="${p.title}" class="detail-image" />
        <div class="detail-content">
            <h2>${p.title}</h2>
            <p>${p.desc}</p>
            <button class="book-btn">Book Now</button>
        </div>
    `;
}

// Filter properties
document.getElementById("search-btn").addEventListener("click", () => {
    const where = document.getElementById("where").value;
    const when = document.getElementById("when").value;
    const guests = document.getElementById("guests").value.trim();

    fetch(BASE_URL + "properties.php")
        .then(res => res.json())
        .then(data => {

            let filtered = data;

            if (where) {
                filtered = filtered.filter(p => p.country === where);
            }

            if (when && filtered.length) {
                filtered = filtered.filter(p =>
                    !p.date_available || p.date_available >= when
                );
            }

            renderProperties(filtered);
        })
        .catch(err => console.error(err));
});

// Initial render
document.addEventListener('DOMContentLoaded', () => {
    fetch(BASE_URL + "properties.php")
        .then(res => res.json())
        .then(data => renderProperties(data))
        .catch(err => console.error(err));
});
