const dops = ["📘", "📗", "📙", "📕", "📓", "📒", "📚", "📖"];
const BOOK_COUNT = 24;

const bg = document.getElementById("bgBooks");

for (let i = 0; i < BOOK_COUNT; i++) {

    const livros = document.createElement("div");

    livros.className = "book";
    livros.textContent = dops[Math.floor(Math.random() * dops.length)];
    livros.style.left = Math.random() * 100 + "vw";
    livros.style.fontSize = 20 + Math.random() * 26 + "px";
    livros.style.animationDuration = 7 + Math.random() * 8 + "s";
    livros.style.animationDelay = -Math.random() * 10 + "s";
    livros.style.opacity = (0.18 + Math.random() * 0.22).toFixed(2);
    bg.appendChild(livros);
}

