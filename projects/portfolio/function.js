const header = document.querySelector(".site-header");
const navLinks = document.querySelectorAll(".nav-links a");
const sections = document.querySelectorAll("main section[id]");

function scrollToId(id) {
  const target = document.getElementById(id);
  if (!target) return;
  const headerHeight = header ? header.offsetHeight : 0;
  const top =
    target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
  window.scrollTo({ top, behavior: "smooth" });
}

navLinks.forEach((link) => {
  link.addEventListener("click", (event) => {
    event.preventDefault();
    const id = link.getAttribute("href").slice(1);
    history.pushState(null, "", "#" + id);
    navLinks.forEach((l) => l.classList.remove("active"));
    link.classList.add("active");
    scrollToId(id);

    // Close mobile menu if open
    const navList = document.getElementById("navLinks");
    const toggle = document.getElementById("navToggle");
    if (navList && toggle) {
      navList.classList.remove("open");
      toggle.classList.remove("open");
      toggle.setAttribute("aria-expanded", "false");
    }
  });
});

window.addEventListener("load", () => {
  if (window.location.hash) {
    scrollToId(window.location.hash.slice(1));
  }
});

const io = new IntersectionObserver(
  (entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) e.target.classList.add("in");
    });
  },
  { threshold: 0.15 },
);
document.querySelectorAll(".reveal").forEach((el) => io.observe(el));

const headerHeight = header ? header.offsetHeight : 0;
const navIo = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        navLinks.forEach((l) => l.classList.remove("active"));
        const link = document.querySelector(
          '.nav-links a[href="#' + entry.target.id + '"]',
        );
        if (link) link.classList.add("active");
      }
    });
  },
  { threshold: 0.5, rootMargin: `-${headerHeight}px 0px -50% 0px` },
);
sections.forEach((s) => navIo.observe(s));

// Hamburger menu toggle
const toggle = document.getElementById("navToggle");
const navList = document.getElementById("navLinks");

if (toggle && navList) {
  toggle.addEventListener("click", () => {
    const isOpen = navList.classList.toggle("open");
    toggle.classList.toggle("open", isOpen);
    toggle.setAttribute("aria-expanded", isOpen);
  });
}
