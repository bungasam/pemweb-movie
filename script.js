document.addEventListener("DOMContentLoaded", () => {
    // Search filter
    const search = document.getElementById("search");
    if (search) {
        search.addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            let cards = document.querySelectorAll(".card");
            cards.forEach(card => {
                let title = card.querySelector("h2").innerText.toLowerCase();
                card.style.display = title.includes(filter) ? "block" : "none";
            });
        });
    }

    // Modal logic
    const modal = document.getElementById("reviewModal");
    const span = document.getElementsByClassName("close")[0];
    const reviewButtons = document.querySelectorAll(".btn-review");

    reviewButtons.forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            let filmId = btn.getAttribute("data-id");
            let judul = btn.getAttribute("data-judul");
            document.getElementById("film_id").value = filmId;
            document.getElementById("modalJudul").innerHTML = `📝 Review: ${judul}`;
            modal.style.display = "flex";
        });
    });

    if (span) {
        span.onclick = () => {
            modal.style.display = "none";
        };
    }

    window.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

    // Notifikasi sukses jika ada parameter success
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        alert("✨ Review berhasil disimpan! Terima kasih ✨");
        history.replaceState({}, document.title, window.location.pathname);
    }
});