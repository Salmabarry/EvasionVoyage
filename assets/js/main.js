document.addEventListener("DOMContentLoaded", function () {
  if (window.lucide) lucide.createIcons();

  // ---- Formulaires de démo : pas de backend, on bloque juste l'envoi ----
  document.querySelectorAll(".js-noop-form").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
    });
  });

  // ---- Navigation : fond au scroll + menu mobile ----
  var header = document.getElementById("site-header");
  if (header) {
    var isHome = header.classList.contains("is-ondark");

    var onScroll = function () {
      var scrolled = window.scrollY > 20;
      header.classList.toggle("is-scrolled", scrolled);
      header.classList.toggle("is-ondark", isHome && !scrolled);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  var toggleBtn = document.getElementById("mobile-menu-toggle");
  var mobileMenu = document.getElementById("mobile-menu");
  var toggleIcon = document.getElementById("mobile-menu-icon");
  if (toggleBtn && mobileMenu) {
    toggleBtn.addEventListener("click", function () {
      var open = mobileMenu.classList.toggle("is-open");
      toggleIcon.setAttribute("data-lucide", open ? "x" : "menu");
      if (window.lucide) lucide.createIcons();
    });
    mobileMenu.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        mobileMenu.classList.remove("is-open");
        toggleIcon.setAttribute("data-lucide", "menu");
        if (window.lucide) lucide.createIcons();
      });
    });
  }

  // ---- Page Destinations : filtre par catégorie ----
  var filterBar = document.getElementById("destination-filters");
  if (filterBar) {
    var cards = document.querySelectorAll("#destination-grid .destination-card");
    filterBar.querySelectorAll(".filter-btn").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var filter = btn.getAttribute("data-filter");

        filterBar.querySelectorAll(".filter-btn").forEach(function (b) {
          b.classList.remove("is-active", "border-primary", "bg-primary", "text-primary-foreground");
          b.classList.add("border-border", "bg-background", "text-foreground");
        });
        btn.classList.add("is-active", "border-primary", "bg-primary", "text-primary-foreground");
        btn.classList.remove("border-border", "bg-background", "text-foreground");

        cards.forEach(function (card) {
          var match = filter === "Toutes" || card.getAttribute("data-category") === filter;
          card.style.display = match ? "" : "none";
        });
      });
    });
  }

  // ---- Page Offres : budget max + type (cosmétique, comme l'original) ----
  var budgetRange = document.getElementById("budget-range");
  var budgetValue = document.getElementById("budget-value");
  var offerList = document.getElementById("offer-list");
  var offerCount = document.getElementById("offer-count");

  if (budgetRange && offerList) {
    var applyBudget = function () {
      var budget = Number(budgetRange.value);
      budgetValue.textContent = budget.toLocaleString("fr-FR") + " FCFA";

      var visible = 0;
      offerList.querySelectorAll(".offer-card").forEach(function (card) {
        var match = Number(card.getAttribute("data-price")) <= budget;
        card.style.display = match ? "" : "none";
        if (match) visible++;
      });
      offerCount.textContent = visible;
    };
    budgetRange.addEventListener("input", applyBudget);
  }

  var typeFilters = document.getElementById("type-filters");
  if (typeFilters) {
    typeFilters.querySelectorAll(".type-btn").forEach(function (btn) {
      btn.addEventListener("click", function () {
        typeFilters.querySelectorAll(".type-btn").forEach(function (b) {
          b.classList.remove("is-active", "bg-accent", "text-accent-foreground");
          b.classList.add("bg-muted", "text-foreground", "hover:bg-secondary");
        });
        btn.classList.add("is-active", "bg-accent", "text-accent-foreground");
        btn.classList.remove("bg-muted", "text-foreground", "hover:bg-secondary");
      });
    });
  }
});
